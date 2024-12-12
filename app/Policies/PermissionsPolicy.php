<?php

namespace App\Policies;

use App\Models\User;

class PermissionsPolicy
{
    public function view(User $user, User $targetUser)
    {
        Log::info('Checking view permissions', [
            'authenticated_user' => $user->id,
            'target_user' => $targetUser->id,
            'has_permission' => $user->hasPermissionTo('view-permissions'),
            'is_admin' => $user->hasRole('Administrator'),
        ]);
    
        // Allow admins to bypass
        if ($user->hasRole('Administrator')) {
            return true;
        }
    
        // Default: Check "view-permissions"
        return $user->hasPermissionTo('view-permissions');
    }
    
    
    public function modify(User $user, User $targetUser)
    {
        // Allow only if the current user has permission to modify permissions
        return $user->hasPermissionTo('modify-permissions');
    }
    
}
