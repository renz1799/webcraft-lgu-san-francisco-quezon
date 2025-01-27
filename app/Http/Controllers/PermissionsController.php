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
    
    
/**
 * Update the user's permissions.
 */
public function update(Request $request, User $user)
{
    Log::info('Received request to update user role and replace permissions.', [
        'user_id' => $user->id,
        'user_email' => $user->email,
        'request_data' => $request->all(),
    ]);

    try {
        // Step 1: Validate the role
        $validatedRole = $request->validate([
            'role' => 'required|string|exists:roles,name', // Ensure role exists
        ]);

        // Step 2: Update the user's role
        $role = Role::where('name', $validatedRole['role'])->first();
        $user->syncRoles([$role]);
        Log::info('User role updated successfully.', ['user_id' => $user->id, 'role' => $validatedRole['role']]);

        // Step 3: Fetch permissions assigned to the role
        $rolePermissions = $role->permissions()
            ->where('guard_name', 'web')
            ->get(); // Fetch as models, not IDs
        Log::info('Fetched role permissions.', ['role_permissions' => $rolePermissions->pluck('id')]);

        // Step 4: Clear all existing permissions for the user
        $user->syncPermissions([]); // Remove all existing permissions
        Log::info('All existing permissions removed for user.', ['user_id' => $user->id]);

        // Step 5: Resolve permissions explicitly and sync
        $validPermissions = [];
        foreach ($rolePermissions as $permission) {
            try {
                $resolvedPermission = Permission::findById($permission->id, 'web');
                $validPermissions[] = $resolvedPermission;
            } catch (\Spatie\Permission\Exceptions\PermissionDoesNotExist $e) {
                Log::warning('Invalid permission encountered.', ['permission_id' => $permission->id]);
            }
        }

        // Sync valid permissions
        $user->syncPermissions($validPermissions);
        Log::info('Role permissions assigned to user.', [
            'user_id' => $user->id,
            'permissions' => $validPermissions,
        ]);

        // Step 6: Return a success response
        return response()->json(['message' => 'User role and permissions updated successfully.']);
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Validation error occurred while updating user role and permissions.', [
            'errors' => $e->errors(),
            'user_id' => $user->id,
        ]);

        return response()->json(['errors' => $e->errors()], 422);
    } catch (\Exception $e) {
        Log::error('Error occurred while updating user role and permissions.', [
            'error_message' => $e->getMessage(),
            'user_id' => $user->id,
        ]);

        return response()->json(['message' => 'Failed to update user role and permissions.'], 500);
    }
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
