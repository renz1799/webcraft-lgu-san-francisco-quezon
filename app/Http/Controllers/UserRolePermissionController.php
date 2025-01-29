<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Log;

class UserRolePermissionController extends Controller
{
    public function edit($userId)
    {
        $user = User::findOrFail($userId);
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('page');
    
        // Retrieve user's current role
        $userRole = $user->roles()->first(); // Fetch the first assigned role
    
        // Retrieve user's assigned permissions correctly
        $userPermissions = $user->permissions->groupBy('page')->map(function ($actions) {
            return $actions->mapToGroups(function ($permission) {
                $words = explode(' ', $permission->name);
                $action = strtolower(array_shift($words)); // Extract 'view', 'modify', 'delete'
                $cleanName = implode(' ', $words); // Extract actual permission name
    
                return [$cleanName => [$action]]; // Store multiple actions
            })->map(function ($actions) {
                return $actions->flatten()->unique()->values()->all(); // Ensure unique actions
            });
        });
    
        // Log the retrieved data
        Log::info('User Role:', ['role' => $userRole ? $userRole->name : 'No Role Assigned']);
        Log::info('User Assigned Permissions:', $userPermissions->toArray());
    
        return view('permissions.set-user-role-permissions', compact('user', 'roles', 'permissions', 'userPermissions', 'userRole'));
    }
    

// Update user role and permissions
public function update(Request $request, $userId)
{
    $user = User::findOrFail($userId);
    \Log::info("Processing permission update for User ID: {$userId}");
    \Log::info("User found: " . json_encode($user));

    // Validate incoming JSON data
    $validatedData = $request->validate([
        'permissions' => 'required|array'
    ]);

    \Log::info("Raw Request Data: " . json_encode($request->all()));
    \Log::info("Validated permissions data: " . json_encode($validatedData));

    // Update Permissions
    $permissions = $request->input('permissions', []);
    $formattedPermissions = [];

    foreach ($permissions as $page => $actions) {
        \Log::info("Processing permissions for Page: {$page}");

        foreach ($actions as $permission => $selectedActions) {
            foreach ($selectedActions as $action) {
                // Search with 'action + permission name' pattern
                $searchName = ucfirst($action) . ' ' . $permission;
                \Log::info("Searching for permission: {$searchName} on Page: {$page}");

                $foundPermission = Permission::where('page', $page)
                    ->where('name', $searchName)
                    ->first();

                if ($foundPermission) {
                    $formattedPermissions[] = $foundPermission->id;
                    \Log::info("Permission found: " . json_encode($foundPermission));
                } else {
                    \Log::warning("Permission not found for: {$searchName} on Page: {$page}");
                }
            }
        }
    }

    \Log::info("Final permission IDs to sync: " . json_encode($formattedPermissions));
    $user->permissions()->sync(array_filter($formattedPermissions));
    \Log::info("Permissions successfully updated for User ID: {$userId}");

    return response()->json(['message' => 'Permissions updated successfully.']);
}

public function changeRole(Request $request, $userId)
{
    $user = User::findOrFail($userId);
    $newRoleName = $request->input('role');

    \Log::info("Role change initiated for User ID: {$userId} to Role: {$newRoleName}");

    // Fetch the role by name and ensure it exists
    $role = Role::where('name', $newRoleName)->first();

    if (!$role) {
        \Log::error("Role not found: {$newRoleName}");
        return response()->json(['error' => 'Role not found'], 404);
    }

    // Remove all existing roles from user
    $user->roles()->detach();
    \Log::info("Existing roles removed for user.");

    // Assign new role using correct ID
    $user->roles()->attach($role->id);
    \Log::info("User role updated to: {$role->name} (ID: {$role->id})");

    // Remove all permissions
    $user->permissions()->detach();
    \Log::info("Existing permissions removed.");

    // Assign default permissions from the role
    $defaultPermissions = $role->permissions()->pluck('id')->toArray();
    $user->permissions()->sync($defaultPermissions);

    \Log::info("Default role permissions applied: " . json_encode($defaultPermissions));

    return response()->json(['message' => 'Role updated successfully and permissions reset.']);
}

    
}
