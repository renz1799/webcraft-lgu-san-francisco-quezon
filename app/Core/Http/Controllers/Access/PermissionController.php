<?php

namespace App\Core\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Core\Http\Requests\Permissions\AccessPermissionsDataRequest;
use App\Core\Http\Requests\Permissions\DestroyPermissionRequest;
use App\Core\Http\Requests\Permissions\RestorePermissionRequest;
use App\Core\Http\Requests\Permissions\StorePermissionRequest;
use App\Core\Http\Requests\Permissions\UpdatePermissionRequest;
use App\Core\Models\Permission;
use App\Core\Services\Contracts\Access\PermissionServiceInterface;
use App\Core\Support\AdminRouteResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionServiceInterface $permissions
    ) {
        $this->middleware(['auth', 'permission:permissions.view|permissions.create|permissions.update|permissions.archive|permissions.restore|access.permissions.view|access.permissions.manage'])
            ->only(['index', 'data', 'store', 'update', 'destroy', 'restore']);
    }

    public function index(): View
    {
        return view('access.permissions.index');
    }

    public function data(AccessPermissionsDataRequest $request): JsonResponse
    {
        $payload = $this->permissions->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function store(StorePermissionRequest $request): RedirectResponse|JsonResponse
    {
        $permission = $this->permissions->create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Permission created successfully.',
                'data' => [
                    'id' => (string) $permission->id,
                    'name' => (string) $permission->name,
                    'page' => (string) $permission->page,
                    'guard_name' => (string) $permission->guard_name,
                ],
            ], 201);
        }

        return redirect()
            ->to(app(AdminRouteResolver::class)->route('access.permissions.index'))
            ->with('success', "Permission \"{$permission->name}\" created.");
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse|JsonResponse
    {
        $updated = $this->permissions->update($permission, $request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Permission updated successfully.',
                'data' => [
                    'id' => (string) $updated->id,
                    'name' => (string) $updated->name,
                    'page' => (string) $updated->page,
                    'guard_name' => (string) $updated->guard_name,
                ],
            ], 200);
        }

        return redirect()
            ->to(app(AdminRouteResolver::class)->route('access.permissions.index'))
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(DestroyPermissionRequest $request, Permission $permission): JsonResponse
    {
        $this->permissions->delete($permission);

        return response()->json(['message' => 'Permission archived successfully.'], 200);
    }

    public function restore(RestorePermissionRequest $request, string $permission): JsonResponse
    {
        $ok = $this->permissions->restorePermission($permission);

        if (! $ok) {
            return response()->json(['message' => 'Permission not found or not restorable.'], 404);
        }

        return response()->json(['message' => 'Permission restored successfully.'], 200);
    }
}
