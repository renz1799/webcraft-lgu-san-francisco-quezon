<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\DeleteRoleRequest;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Models\Role;
use App\Services\Contracts\RoleServiceInterface;
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
        $data = $this->roles->indexData();

        return view('access.roles.index', $data);
    }

    // Kept for resource compatibility; renders the same screen-based form/modals.
    public function create(): View
    {
        $data = $this->roles->indexData();

        return view('access.roles.index', $data);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->roles->create($request->validated());

        return redirect()->route('access.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $data = $this->roles->indexData();
        $data['role'] = $role->load('permissions');
        $data['rolePermissions'] = $role->permissions()->pluck('id')->toArray();

        return view('access.roles.index', $data);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roles->update($role, $request->validated());

        return redirect()->route('access.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(DeleteRoleRequest $request, Role $role): RedirectResponse
    {
        $this->roles->delete($role);

        return redirect()->route('access.roles.index')->with('success', 'Role deleted successfully.');
    }
}
