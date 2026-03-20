<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Theme\UpdateThemeColorsRequest;
use App\Http\Requests\Theme\UpdateThemeStyleRequest;
use App\Services\UI\ThemeService;
use RuntimeException;

class ThemeController extends Controller
{
    public function __construct(private ThemeService $theme)
    {
        $this->middleware('auth');
        $this->middleware('role_or_permission:Administrator|admin')->only('updateColors');
    }

    /** Per-user style (unchanged) */
    public function updateStyle(UpdateThemeStyleRequest $request)
    {
        $userId = (string) $request->user()->id;
        $next = $this->theme->saveUserStyle($userId, $request->validated());

        return response()->json($next);
    }

    /** Administrator-only colors, scoped to the active module */
    public function updateColors(UpdateThemeColorsRequest $request)
    {
        try {
            $colors = $this->theme->saveModuleColors($request->validated());
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json($colors);
    }
}
