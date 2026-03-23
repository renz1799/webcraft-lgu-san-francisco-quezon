<?php

namespace App\Core\Http\Controllers;

use App\Core\Http\Requests\Drive\ConnectDriveRequest;
use App\Core\Http\Requests\Drive\DisconnectDriveRequest;
use App\Core\Http\Requests\Drive\UploadDriveFileRequest;
use App\Core\Models\GoogleToken;
use App\Core\Models\Module;
use App\Core\Models\User;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveConnectionServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GoogleDriveController extends Controller
{
    public function __construct(
        private readonly GoogleDriveConnectionServiceInterface $connection,
        private readonly GoogleDriveFileServiceInterface $files,
        private readonly ModuleDepartmentResolverInterface $moduleDepartments,
    ) {}

    public function index(): View
    {
        return view('drive.index', [
            'contexts' => $this->integrationContexts(),
        ]);
    }

    public function connect(ConnectDriveRequest $request): RedirectResponse
    {
        $module = $this->resolveManagedModule((string) $request->validated('module_id'));
        $department = $this->resolveManagedDepartment($module);

        if ($department === null) {
            return redirect()
                ->route('drive.index')
                ->with('error', "Google Drive cannot be connected for {$module->name} because no default department scope is configured.");
        }

        session([
            'drive.pending_scope' => [
                'module_id' => (string) $module->id,
                'department_id' => (string) $department->id,
                'module_name' => (string) $module->name,
                'department_name' => (string) $department->name,
            ],
        ]);

        return redirect()->away(
            $this->connection->getAuthUrlFor((string) $module->id, (string) $department->id)
        );
    }

    public function callback(Request $request): RedirectResponse
    {
        $code = (string) $request->query('code');

        $pendingScope = session('drive.pending_scope');

        if (is_array($pendingScope)
            && ! empty($pendingScope['module_id'])
            && ! empty($pendingScope['department_id'])) {
            $this->connection->handleCallbackFor(
                (string) $pendingScope['module_id'],
                (string) $pendingScope['department_id'],
                $code,
                (string) $request->user()->id,
            );
            session()->forget('drive.pending_scope');

            $moduleName = trim((string) ($pendingScope['module_name'] ?? 'selected context'));
            $departmentName = trim((string) ($pendingScope['department_name'] ?? 'default department'));

            return redirect()
                ->route('drive.index')
                ->with('status', "Google Drive connected for {$moduleName} ({$departmentName}).");
        }

        $this->connection->handleCallback($code, (string) $request->user()->id);

        return redirect()
            ->route('drive.index')
            ->with('status', 'Google Drive connected.');
    }

    public function disconnect(DisconnectDriveRequest $request): RedirectResponse
    {
        $module = $this->resolveManagedModule((string) $request->validated('module_id'));
        $department = $this->resolveManagedDepartment($module);

        if ($department === null) {
            return redirect()
                ->route('drive.index')
                ->with('error', "Google Drive cannot be disconnected for {$module->name} because no default department scope is configured.");
        }

        $this->connection->disconnectFor((string) $module->id, (string) $department->id);

        return redirect()
            ->route('drive.index')
            ->with('status', "Google Drive disconnected for {$module->name}.");
    }

    public function upload(UploadDriveFileRequest $request): RedirectResponse
    {
        $meta = $this->files->upload(
            $request->file('file'),
            null,
            (bool) $request->boolean('make_public'),
        );

        return back()->with('uploaded', $meta);
    }

    public function preview(Request $request, string $fileId): Response
    {
        $file = $this->files->download($fileId);

        return response($file['bytes'], 200, [
            'Content-Type' => $file['mime_type'],
            'Content-Disposition' => 'inline; filename="' . $file['name'] . '"',
            'Cache-Control' => 'private, max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function integrationContexts(): Collection
    {
        $sharedCapabilityCodes = collect((array) config('modules.shared_capability_codes', []))
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter()
            ->values();

        $modules = Module::query()
            ->with('defaultDepartment')
            ->where('is_active', true)
            ->get()
            ->reject(fn (Module $module) => $sharedCapabilityCodes->contains(strtoupper((string) $module->code)))
            ->sortBy([
                fn (Module $module) => $module->isPlatformContext() ? 0 : ($module->isBusinessContext() ? 1 : 2),
                fn (Module $module) => strtoupper((string) $module->name),
            ])
            ->values();

        $contexts = $modules->map(function (Module $module): array {
            $department = $this->moduleDepartments->allowedDepartmentsForModule((string) $module->id)->first();
            $departmentId = $department?->id
                ? (string) $department->id
                : $this->moduleDepartments->defaultDepartmentIdForModule((string) $module->id);

            return [
                'module' => $module,
                'department_id' => $departmentId,
                'department' => $department,
            ];
        });

        $tokenLookup = $this->loadTokenLookup($contexts);

        return $contexts->map(function (array $context) use ($tokenLookup): array {
            /** @var Module $module */
            $module = $context['module'];
            $departmentId = trim((string) ($context['department_id'] ?? ''));
            $token = $departmentId !== ''
                ? $tokenLookup->get($this->tokenLookupKey((string) $module->id, $departmentId))
                : null;

            return [
                'module_id' => (string) $module->id,
                'module_code' => (string) $module->code,
                'module_name' => (string) $module->name,
                'module_type' => $module->typeLabel(),
                'department_id' => $departmentId !== '' ? $departmentId : null,
                'department_name' => $context['department']?->name,
                'department_code' => $context['department']?->code,
                'is_connectable' => $departmentId !== '',
                'connected' => (bool) ($token && $token->refresh_token),
                'connected_by_name' => $this->displayUserName($token?->connectedBy),
                'connected_at_text' => $token?->updated_at?->format('M d, Y h:i A'),
            ];
        })->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $contexts
     * @return Collection<string, GoogleToken>
     */
    private function loadTokenLookup(Collection $contexts): Collection
    {
        $moduleIds = $contexts
            ->pluck('module.id')
            ->filter()
            ->map(fn ($value) => (string) $value)
            ->values();
        $departmentIds = $contexts
            ->pluck('department_id')
            ->filter()
            ->map(fn ($value) => (string) $value)
            ->values();

        if ($moduleIds->isEmpty() || $departmentIds->isEmpty()) {
            return collect();
        }

        return GoogleToken::query()
            ->with([
                'connectedBy:id,username,email',
                'connectedBy.profile:id,user_id,first_name,middle_name,last_name,name_extension',
            ])
            ->whereIn('module_id', $moduleIds->all())
            ->whereIn('department_id', $departmentIds->all())
            ->where('provider', 'google_drive')
            ->get()
            ->keyBy(fn (GoogleToken $token) => $this->tokenLookupKey((string) $token->module_id, (string) $token->department_id));
    }

    private function resolveManagedModule(string $moduleId): Module
    {
        $module = Module::query()
            ->whereKey($moduleId)
            ->where('is_active', true)
            ->firstOrFail();

        $sharedCapabilityCodes = collect((array) config('modules.shared_capability_codes', []))
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter()
            ->values();

        abort_if(
            $sharedCapabilityCodes->contains(strtoupper((string) $module->code)),
            422,
            'Shared capabilities do not manage Google Drive connections through module scope.'
        );

        return $module;
    }

    private function resolveManagedDepartment(Module $module): ?object
    {
        return $this->moduleDepartments->allowedDepartmentsForModule((string) $module->id)->first();
    }

    private function tokenLookupKey(string $moduleId, string $departmentId): string
    {
        return "{$moduleId}:{$departmentId}";
    }

    private function displayUserName(?User $user): ?string
    {
        if ($user === null) {
            return null;
        }

        $profileName = trim((string) ($user->profile?->full_name ?? ''));

        if ($profileName !== '') {
            return $profileName;
        }

        $username = trim((string) ($user->username ?? ''));

        if ($username !== '') {
            return $username;
        }

        $email = trim((string) ($user->email ?? ''));

        return $email !== '' ? $email : null;
    }
}
