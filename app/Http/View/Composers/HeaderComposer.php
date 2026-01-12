<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class HeaderComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        if ($user) {
            $user->loadMissing('profile');

            $default = asset('build/assets/images/default-profile.png');
            $photoUrl = $default;

            $relPath = $user->profile?->profile_photo_path; // e.g. "profiles/abc.jpg"
            if ($relPath) {
                $abs = public_path('storage/' . ltrim($relPath, '/'));

                if (is_file($abs)) {
                    $photoUrl = asset('storage/' . ltrim($relPath, '/')) . '?v=' . filemtime($abs);
                }
            }

            // attach computed url
            $user->cache_busted_photo_url = $photoUrl;
        }

        $view->with('user', $user);
    }
}
