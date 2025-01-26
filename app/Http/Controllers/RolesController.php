<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // For logging
use App\Models\Role;
use App\Models\Permission;

class RolesController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index()
    {
        Log::info('Fetching all roles and permissions.');

        $roles = Role::with('permissions')->get(); // Using the custom Role model
        $permissions = Permission::all(); // Using the custom Permission model

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

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            Log::info('Validation passed for role creation.', ['validated_data' => $validated]);

            $roleId = \Str::uuid();
            $role = Role::create([
                'id' => $roleId,
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);

            Log::info('Role created successfully in database.', [
                'role_id' => $role->id,
                'role_name' => $role->name,
            ]);

            if ($request->has('permissions')) {
                Log::info('Syncing permissions for the role.', ['permissions' => $validated['permissions']]);
                $role->syncPermissions($validated['permissions']);
                Log::info('Permissions synced successfully.', ['role_id' => $role->id]);
            } else {
                Log::info('No permissions provided to sync.');
            }

            Log::info('Role creation process completed successfully.', ['role_id' => $role->id]);

            return redirect()
                ->route('roles.index')
                ->with('success', 'Role created successfully.');
        } catch (\Exception $e) {
            Log::error('Error occurred while creating a role.', [
                'error_message' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Failed to create role. Please try again later.');
        }
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
        Log::info('Received request to update a role.', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'request_data' => $request->all(),
        ]);
    
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ]);
    
            Log::info('Validation passed for role update.', ['validated_data' => $validated]);
    
            $role->update(['name' => $validated['name']]);
            Log::info('Role name updated successfully.', ['role_id' => $role->id]);
    
            // Force guard name to match when syncing permissions
            $permissions = Permission::whereIn('id', $validated['permissions'])
                ->where('guard_name', 'web') // Ensure correct guard
                ->get();
    
            Log::info('Filtered permissions for syncing', [
                'role_id' => $role->id,
                'permissions' => $permissions->pluck('id')->toArray(),
            ]);
    
            $role->syncPermissions($permissions);
    
            Log::info('Permissions synced successfully.', ['role_id' => $role->id]);
    
            return redirect()
                ->route('roles.index')
                ->with('success', 'Role updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error occurred while updating a role.', [
                'role_id' => $role->id,
                'error_message' => $e->getMessage(),
            ]);
    
            return redirect()
                ->back()
                ->with('error', 'Failed to update role. Please try again.');
        }
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
