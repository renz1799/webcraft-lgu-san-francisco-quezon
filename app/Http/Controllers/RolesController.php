<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\AccessRolesDataRequest;
use App\Http\Requests\Roles\DeleteRoleRequest;
use App\Http\Requests\Roles\RestoreRoleRequest;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Models\Role;
use App\Services\Contracts\RoleServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RolesController extends Controller
{
    public function __construct(private readonly RoleServiceInterface $roles)
    {
        $this->middleware(['auth', 'role_or_permission:Administrator']);
    }

    public function index(): View
    {
        return view('access.roles.index', $this->roles->indexData());
    }

    public function data(AccessRolesDataRequest $request): JsonResponse
    {
        $payload = $this->roles->datatable($request->validated());

        return response()->json([
            'data' => $payload['data'] ?? [],
            'last_page' => (int) ($payload['last_page'] ?? 1),
            'total' => (int) ($payload['total'] ?? 0),
        ]);
    }

    public function restore(RestoreRoleRequest $request, string $role): JsonResponse
    {
        $ok = $this->roles->restoreRole($role);

        if (! $ok) {
            return response()->json(['message' => 'Role not found or not restorable.'], 404);
        }

        return response()->json(['message' => 'Role restored successfully.'], 200);
    }

    public function create(): View
    {
        return view('access.roles.index', $this->roles->indexData());
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roles->create($request->validated());

        return redirect()->route('access.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        return view('access.roles.index', $this->roles->indexData());
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roles->update($role, $request->validated());

        return redirect()->route('access.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(DeleteRoleRequest $request, Role $role): RedirectResponse|JsonResponse
    {
        $this->roles->delete($role);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Role deleted successfully.'], 200);
        }

        return redirect()->route('access.roles.index')->with('success', 'Role deleted successfully.');
    }
}
