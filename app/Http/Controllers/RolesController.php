<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Services\Contracts\RoleServiceInterface;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Http\Requests\Roles\DeleteRoleRequest;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RolesController extends Controller
{
    public function __construct(private readonly RoleServiceInterface $roles)
    {
        $this->middleware(['auth','role_or_permission:Administrator']);
    }

    public function index(): View
    {
        $data = $this->roles->indexData();
        return view('permissions.roles', $data);
    }

    // If you keep a separate "create" view, otherwise you can remove
    public function create(): View
    {
        $data = $this->roles->indexData(); // reuse: permissions list
        return view('permissions.roles-create', $data);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roles->create($request->validated());
        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        // Keep the edit view skinny; just pass what it needs
        $data = $this->roles->indexData();
        $data['role']            = $role->load('permissions');
        $data['rolePermissions'] = $role->permissions()->pluck('id')->toArray();

        return view('permissions.roles-edit', $data);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roles->update($role, $request->validated());
        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(DeleteRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roles->delete($role);
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
