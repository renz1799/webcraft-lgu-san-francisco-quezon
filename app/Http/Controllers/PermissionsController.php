<?php 

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;


class PermissionsController extends Controller
{
    /**
     * Display a listing of the users excluding Administrators.
     */
    public function index()
    {
        // Fetch users excluding Administrators
        $users = User::where('user_type', '!=', 'Administrator')->get();
    
        // Fetch all permissions grouped by their module
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode(' ', $permission->name, 2)[1] ?? 'others'; // Group by page/module
        });
    
        return view('permissions.permissions', compact('users', 'permissions'));
    }
    
    /**
     * Fetch the user's permissions.
     */
    public function getUserPermissions(User $user)
    {
        $this->ensureUserHasRole($user); // Ensure the user has a role
    
        // Fetch all permissions grouped by their page/module
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode(' ', $permission->name, 2)[1] ?? 'others'; // Group by page/module
        });
    
        // Fetch the user's current permissions
        $userPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
    
        // Fetch all roles and the current role of the user
        $roles = Role::pluck('name')->toArray();
        $currentRole = $user->roles->pluck('name')->first();
    
        return response()->json([
            'permissions' => $permissions,
            'userPermissions' => $userPermissions,
            'roles' => $roles,
            'currentRole' => $currentRole, // Add current role
        ]);
    }
    
    public function update(Request $request, $uuid)
    {
        $user = User::where('id', $uuid)->firstOrFail();

        $roleName = $request->input('role'); // Role name
        $permissions = $request->input('permissions', []); // Permissions array (default to empty array)

        \Log::info('Updating user role and permissions', [
            'user_id' => $user->id,
            'requested_role' => $roleName,
            'requested_permissions' => $permissions
        ]);

        $this->updateRoleAndPermissions($user, $roleName, $permissions);

        return response()->json(['message' => 'User role and permissions updated successfully.'], 200);
    }

    /**
     * Update the user's role and permissions.
     *
     * @param \App\Models\User $user
     * @param string|null $newRoleName
     * @param array $permissions
     * @return void
     */
    private function updateRoleAndPermissions(User $user, ?string $newRoleName, array $permissions)
    {
        $currentRole = $user->roles->pluck('name')->first();
    
        if ($currentRole !== $newRoleName) {
            // Assign new role and reset permissions
            $user->syncRoles([]); // Remove current roles
            $newRole = Role::where('name', $newRoleName)->firstOrFail();
            $user->assignRole($newRole);
            $defaultPermissions = $newRole->permissions;
            $user->syncPermissions($defaultPermissions);
    
            \Log::info('Role updated', [
                'user_id' => $user->id,
                'new_role' => $newRoleName,
                'default_permissions' => $defaultPermissions->pluck('name')->toArray(),
            ]);
        } else {
            // Update only custom permissions
            $permissionObjects = Permission::whereIn('name', $permissions)->get();
            $user->syncPermissions($permissionObjects);
    
            \Log::info('Permissions updated', [
                'user_id' => $user->id,
                'custom_permissions' => $permissionObjects->pluck('name')->toArray(),
            ]);
        }
    }
    

    /**
     * Update the user's permissions (custom overrides).
     *
     * @param \App\Models\User $user
     * @param array $permissions
     * @return void
     */
    private function updatePermissions(User $user, array $permissions)
    {
        // Step 1: Extract and flatten permissions correctly
        $formattedPermissions = [];
        foreach ($permissions as $module => $actions) {
            foreach ($actions as $action => $granted) {
                if ($granted) { // Only process checked permissions
                    $formattedPermissions[] = "{$action} {$module}"; // Convert to "view users" format
                }
            }
        }
    
        // Step 2: Fetch permission objects based on formatted names
        $permissionObjects = Permission::whereIn('name', $formattedPermissions)->get();
    
        // Step 3: Sync only the specified permissions
        $user->syncPermissions($permissionObjects);
    
        // Step 4: Log updated permissions
        \Log::info('Permissions updated', [
            'user_id' => $user->id,
            'custom_permissions' => $permissionObjects->pluck('name')->toArray(),
        ]);
    }
    
    /**
     * Ensure the given user has a role assigned.
     */
    private function ensureUserHasRole(User $user)
    {
        Log::info('Checking roles for user before updating permissions', [
            'user_id' => $user->id,
            'current_roles' => $user->roles->pluck('name'),
        ]);

        if ($user->roles->isEmpty()) {
            Log::info('User has no roles. Assigning default "User" role.', [
                'user_id' => $user->id,
            ]);

            // Check if the 'User' role exists, create it if not
            $userRole = Role::firstOrCreate(
                ['name' => 'User', 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );

            if ($userRole->wasRecentlyCreated) {
                Log::info('Created "User" role', [
                    'role_id' => $userRole->id,
                    'role_name' => $userRole->name,
                ]);
            } else {
                Log::info('"User" role already exists', [
                    'role_id' => $userRole->id,
                    'role_name' => $userRole->name,
                ]);
            }

            // Assign the 'User' role to the user
            $user->assignRole($userRole);

            Log::info('Assigned "User" role to user', [
                'user_id' => $user->id,
                'role_name' => $userRole->name,
            ]);
        } else {
            Log::info('User already has roles, no action taken.', [
                'user_id' => $user->id,
                'current_roles' => $user->roles->pluck('name'),
            ]);
        }
    }
    /**
 * Delete a user and log the action.
 */
public function deleteUser(User $user)
{
    try {
        // Ensure the logged-in user is authorized to delete users
        if (!auth()->user()->hasRole('admin') && !auth()->user()->can('delete users')) {
            Log::warning('Unauthorized attempt to delete user', [
                'user_id' => auth()->id(),
                'target_user_id' => $user->id,
                'roles' => auth()->user()->roles->pluck('name'),
                'permissions' => auth()->user()->getAllPermissions()->pluck('name'),
            ]);
            return response()->json(['message' => 'You do not have permission to delete users.'], 403);
        }

        // Log the user's current details before deletion
        Log::info('Deleting user account', [
            'deleted_by' => auth()->id(),
            'user_id' => $user->id,
            'user_details' => $user->toArray(),
        ]);

        // Delete the user
        $user->delete();

        return response()->json(['message' => 'User account deleted successfully.'], 200);
    } catch (\Exception $e) {
        Log::error('Error deleting user', [
            'error' => $e->getMessage(),
            'user_id' => $user->id,
        ]);
        return response()->json(['message' => 'Failed to delete user.'], 500);
    }
}
public function updateStatus(Request $request, $id)
{
    try {
        // Fetch the user by its ID
        $user = User::findOrFail($id);

        // Ensure the logged-in user is authorized
        if (!auth()->user()->hasRole('admin') && !auth()->user()->can('modify users')) {
            return response()->json(['message' => 'You do not have permission to update user status.'], 403);
        }

        // Validate the request
        $validated = $request->validate([
            'is_active' => 'required|boolean',
        ]);

        // Update the user's status
        $user->is_active = $validated['is_active'];
        $user->save();

        return response()->json(['message' => 'User status updated successfully.'], 200);
    } catch (\Exception $e) {
        Log::error('Error updating user status', [
            'error' => $e->getMessage(),
            'user_id' => $id,
        ]);
        return response()->json(['message' => 'Failed to update user status.'], 500);
    }
}


}
