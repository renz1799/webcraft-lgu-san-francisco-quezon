<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permissions\AccessPermissionsDataRequest;
use App\Http\Requests\Permissions\DestroyPermissionRequest;
use App\Http\Requests\Permissions\RestorePermissionRequest;
use App\Http\Requests\Permissions\StorePermissionRequest;
use App\Http\Requests\Permissions\UpdatePermissionRequest;
use App\Models\Permission;
use App\Services\Contracts\PermissionServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionServiceInterface $permissions
    ) {
        $this->middleware(['auth', 'role_or_permission:Administrator'])
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
            ->route('access.permissions.index')
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
            ->route('access.permissions.index')
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
