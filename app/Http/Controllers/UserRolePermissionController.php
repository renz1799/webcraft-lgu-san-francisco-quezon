<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Log;

class UserRolePermissionController extends Controller
{
    // Show form for setting user role and permissions
    public function edit($userId)
    {
        $user = User::findOrFail($userId);
        $roles = Role::all();
        $permissions = Permission::all()->groupBy('page');

        // Log the retrieved permissions
        Log::info('Permissions retrieved from DB:', $permissions->toArray());

        // Retrieve user's assigned permissions
            $userPermissions = $user->permissions->groupBy('page')->map(function ($actions) {
                return $actions->mapWithKeys(function ($permission) {
                    $words = explode(' ', $permission->name);
                    $action = strtolower(array_shift($words)); // Extracts 'view', 'modify', 'delete'
                    $cleanName = implode(' ', $words); // Extracts actual permission name

                    return [$cleanName => [$action]];
                });
            });

// Log the corrected user permissions structure
Log::info('Corrected User Assigned Permissions:', $userPermissions->toArray());


        // Log the user's assigned permissions
        Log::info('User Assigned Permissions:', $userPermissions->toArray());

        return view('permissions.set-user-role-permissions', compact('user', 'roles', 'permissions', 'userPermissions'));
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

    
}
