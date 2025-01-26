<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // For logging
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        Log::info('Fetching all roles and permissions.');

        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        Log::info('Roles fetched successfully.', ['roles_count' => $roles->count()]);
        Log::info('Permissions fetched successfully.', ['permissions_count' => $permissions->count()]);

        return view('permissions.roles', compact('roles', 'permissions'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        Log::info('Accessing the create role form.');

        $permissions = Permission::all();
        Log::info('Permissions fetched successfully for role creation.', ['permissions_count' => $permissions->count()]);

        return view('permissions.roles', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        Log::info('Received request to create a role.', ['request_data' => $request->all()]);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        Log::info('Validation passed for role creation.');

        $role = Role::create(['name' => $validated['name']]);
        if ($request->has('permissions')) {
            $role->syncPermissions($validated['permissions']);
        }

        Log::info('Role created successfully.', ['role_id' => $role->id, 'role_name' => $role->name]);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Show the form for editing a role.
     */
    public function edit(Role $role)
    {
        Log::info('Accessing the edit role form.', ['role_id' => $role->id]);

        $permissions = Permission::all();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        Log::info('Permissions fetched successfully for editing.', ['permissions_count' => $permissions->count()]);

        return view('permissions.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        Log::info('Received request to update a role.', ['role_id' => $role->id, 'request_data' => $request->all()]);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        Log::info('Validation passed for role update.');

        $role->update(['name' => $validated['name']]);
        if ($request->has('permissions')) {
            $role->syncPermissions($validated['permissions']);
        } else {
            $role->syncPermissions([]);
        }

        Log::info('Role updated successfully.', ['role_id' => $role->id, 'role_name' => $role->name]);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        Log::info('Received request to delete a role.', ['role_id' => $role->id]);

        $role->delete();

        Log::info('Role deleted successfully.', ['role_id' => $role->id]);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
