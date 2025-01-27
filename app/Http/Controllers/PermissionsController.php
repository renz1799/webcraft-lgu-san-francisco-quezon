<?php 

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
    
        return response()->json([
            'permissions' => $permissions,
            'userPermissions' => $userPermissions, // Permissions the user already has
        ]);
    }
    
/**
 * Update the user's permissions.
 */
public function update(Request $request, User $user)
{
    // Check if the logged-in user is an admin or has permission to modify users
    if (!auth()->user()->hasRole('admin') && !auth()->user()->can('modify users')) {
        Log::warning('Unauthorized attempt to modify permissions', [
            'user_id' => auth()->id(),
            'attempted_on' => $user->id,
            'roles' => auth()->user()->roles->pluck('name'),
            'permissions' => auth()->user()->getAllPermissions()->pluck('name'),
        ]);
        abort(403, 'You do not have permission to modify user permissions.');
    }

    $this->ensureUserHasRole($user); // Ensure the user has a role

    // Validate the request data (permissions can be an empty array)
    $validated = $request->validate([
        'permissions' => 'nullable|array', // Make the field nullable
    ]);

    // Flatten the permissions array if it exists (e.g., ['view users', 'modify users'])
    $permissions = !empty($validated['permissions']) ? array_map(function ($module, $actions) {
        return array_map(fn($action) => "$action $module", array_keys($actions));
    }, array_keys($validated['permissions']), $validated['permissions']) : [];

    // Flatten and merge the permissions if not empty
    $permissions = !empty($permissions) ? array_merge(...$permissions) : [];

    // Sync permissions with the user (empty array will revoke all permissions)
    $user->syncPermissions($permissions);

    Log::info('Updated user permissions', [
        'user_id' => $user->id,
        'updated_permissions' => $permissions,
    ]);

    return response()->json([
        'message' => 'Permissions updated successfully.',
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
