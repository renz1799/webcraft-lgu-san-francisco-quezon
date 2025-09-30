<?php
// app/Http/Controllers/ThemeController.php
namespace App\Http\Controllers;

use App\Services\ThemeService;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function __construct(private ThemeService $theme) {}

    /** per-user style (any authenticated user) */
    public function updateStyle(Request $request)
    {
        $this->middleware('auth');

        $data = $request->validate([
            'mode'      => 'in:light,dark',
            'dir'       => 'in:ltr,rtl',
            'nav'       => 'in:vertical,horizontal',
            'menuHover' => 'boolean',
        ]);

        $result = $this->theme->saveUserStyle($request->user()->id, $data);

        return response()->json(['ok' => true, 'style' => $result]);
    }

    /** admin-only colors, apply to everyone */
    public function updateColors(Request $request)
    {
        $this->middleware(['auth','role:admin']);

        $data = $request->validate([
            'primary' => 'regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i',
            'success' => 'regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i',
            'warning' => 'regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i',
            'danger'  => 'regex:/^#([0-9a-f]{3}|[0-9a-f]{6})$/i',
        ]);

        $colors = $this->theme->saveGlobalColors($data);

        return response()->json(['ok' => true, 'colors' => $colors]);
    }
}
