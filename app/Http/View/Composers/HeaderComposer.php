<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HeaderComposer
{
    public function compose(View $view)
    {
        $user = Auth::user(); // Fetch the authenticated user
        if ($user) {
            $user->load('profile'); // Load the profile relationship

            // Cache-busted URL for profile photo
            if ($user->profile && $user->profile->profile_photo_path) {
                $photoPath = public_path('storage/' . $user->profile->profile_photo_path);
                if (file_exists($photoPath)) {
                    $photoUrl = asset('storage/' . $user->profile->profile_photo_path) . '?v=' . filemtime($photoPath);
                } else {
                    $photoUrl = asset('default-avatar.png'); // Fallback if the file doesn't exist
                }
            } else {
                $photoUrl = asset('default-avatar.png'); // Fallback if no profile photo is set
            }

            // Add the cache-busted photo URL to the user object
            $user->cache_busted_photo_url = $photoUrl;
        }

        $view->with('user', $user); // Pass user data to the view
    }
}
