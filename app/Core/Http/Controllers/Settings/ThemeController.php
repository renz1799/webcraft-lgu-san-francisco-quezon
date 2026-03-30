<?php

namespace App\Core\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Core\Http\Requests\Theme\UpdateThemeColorsRequest;
use App\Core\Http\Requests\Theme\UpdateThemeStyleRequest;
use App\Core\Services\UI\ThemeService;
use RuntimeException;

class ThemeController extends Controller
{
    public function __construct(private ThemeService $theme)
    {
        $this->middleware('auth');
        $this->middleware('permission:theme.update_colors')->only('updateColors');
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
