<?php

namespace App\Http\Controllers;

use App\Services\ThemeService;
use App\Http\Requests\Theme\UpdateThemeStyleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ThemeController extends Controller
{
    public function __construct(private ThemeService $theme)
    {
        // Everyone must be logged in to hit these endpoints
        $this->middleware('auth');

        // Colors are admin-only
        $this->middleware('role:admin')->only('updateColors');
    }

    /** Per-user style (any authenticated user) */
    public function updateStyle(UpdateThemeStyleRequest $request)
    {
        $userId = (string) $request->user()->id;

        // $request already normalized (menuHover -> menuStyle, trimming, casing, etc.)
        $next = $this->theme->saveUserStyle($userId, $request->validated());

        // Return the resolved style (useful for debugging/clients)
        return response()->json($next);
    }

    /** Admin-only colors, apply to everyone */
    public function updateColors(Request $request)
    {
        $data = $request->validate([
            'primary' => ['sometimes','regex:/^#(?:[0-9a-f]{3}){1,2}$/i'],
            'success' => ['sometimes','regex:/^#(?:[0-9a-f]{3}){1,2}$/i'],
            'warning' => ['sometimes','regex:/^#(?:[0-9a-f]{3}){1,2}$/i'],
            'danger'  => ['sometimes','regex:/^#(?:[0-9a-f]{3}){1,2}$/i'],
        ]);

        $colors = $this->theme->saveGlobalColors($data);

        return response()->json($colors);
    }
}
