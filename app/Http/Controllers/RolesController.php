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
            // Step 1: Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name',
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ]);
    
            Log::info('Validation passed for role creation.', ['validated_data' => $validated]);
    
            // Step 2: Fetch permissions and validate guards
            $allPermissions = Permission::whereIn('id', $validated['permissions'] ?? [])
                ->get(['id', 'name', 'guard_name']);
    
            Log::info('Fetched permissions for validation.', [
                'permissions_count' => $allPermissions->count(),
                'permissions_details' => $allPermissions->toArray(),
            ]);
    
            $validPermissions = $allPermissions->where('guard_name', 'web')->pluck('id')->toArray();
    
            // Step 3: Create the role
            $role = Role::create([
                'id' => \Str::uuid(),
                'name' => $validated['name'],
                'guard_name' => 'web',
            ]);
    
            Log::info('Role created successfully in database.', [
                'role_id' => $role->id,
                'role_name' => $role->name,
            ]);
    
            // Step 4: Clear permission cache using the service container
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            Log::info('Permission cache cleared.');
    
            // Step 5: Verify permissions before syncing
            foreach ($validPermissions as $permissionId) {
                if (!Permission::findById($permissionId, 'web')) {
                    Log::error('Permission not found by Spatie.', [
                        'permission_id' => $permissionId,
                        'guard_name' => 'web',
                    ]);
                    throw new \Exception("Permission with ID {$permissionId} and guard 'web' is not recognized.");
                }
            }
    
            Log::info('Verifying permissions before syncing.', [
                'valid_permissions' => $validPermissions,
            ]);
    
            // Step 6: Sync permissions
            try {
                $role->syncPermissions($validPermissions);
                Log::info('Permissions synced successfully.', ['role_id' => $role->id]);
            } catch (\Exception $e) {
                Log::error('Error occurred while syncing permissions.', [
                    'error_message' => $e->getMessage(),
                    'role_id' => $role->id,
                    'valid_permissions' => $validPermissions,
                ]);
    
                throw $e; // Re-throw the exception to trigger the catch block below
            }
    
            // Step 7: Redirect with success message
            return redirect()
                ->route('roles.index')
                ->with('success', 'Role created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error occurred while creating a role.', [
                'errors' => $e->errors(),
                'request_data' => $request->all(),
            ]);
    
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
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
            // Step 1: Validate the request
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
                'permissions' => 'array',
                'permissions.*' => 'exists:permissions,id',
            ]);
    
            Log::info('Validation passed for role update.', ['validated_data' => $validated]);
    
            // Step 2: Update the role name
            $role->update(['name' => $validated['name']]);
            Log::info('Role name updated successfully.', ['role_id' => $role->id]);
    
            // Step 3: Fetch permissions and validate guards
            $allPermissions = Permission::whereIn('id', $validated['permissions'] ?? [])
                ->get(['id', 'name', 'guard_name']);
    
            Log::info('Fetched permissions for validation.', [
                'permissions_count' => $allPermissions->count(),
                'permissions_details' => $allPermissions->toArray(),
            ]);
    
            $validPermissions = $allPermissions->where('guard_name', 'web')->pluck('id')->toArray();
    
            // Step 4: Clear permission cache using the service container
            app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
            Log::info('Permission cache cleared.');
    
            // Step 5: Verify permissions before syncing
            foreach ($validPermissions as $permissionId) {
                if (!Permission::findById($permissionId, 'web')) {
                    Log::error('Permission not found by Spatie.', [
                        'permission_id' => $permissionId,
                        'guard_name' => 'web',
                    ]);
                    throw new \Exception("Permission with ID {$permissionId} and guard 'web' is not recognized.");
                }
            }
    
            Log::info('Verifying permissions before syncing.', [
                'valid_permissions' => $validPermissions,
            ]);
    
            // Step 6: Sync permissions
            try {
                $role->syncPermissions($validPermissions);
                Log::info('Permissions synced successfully.', ['role_id' => $role->id]);
            } catch (\Exception $e) {
                Log::error('Error occurred while syncing permissions.', [
                    'error_message' => $e->getMessage(),
                    'role_id' => $role->id,
                    'valid_permissions' => $validPermissions,
                ]);
    
                throw $e; // Re-throw the exception to trigger the catch block below
            }
    
            // Step 7: Redirect with success message
            return redirect()
                ->route('roles.index')
                ->with('success', 'Role updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error occurred while updating a role.', [
                'errors' => $e->errors(),
                'role_id' => $role->id,
            ]);
    
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error occurred while updating a role.', [
                'error_message' => $e->getMessage(),
                'role_id' => $role->id,
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
