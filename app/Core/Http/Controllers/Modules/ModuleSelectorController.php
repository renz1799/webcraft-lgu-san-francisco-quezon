<?php

namespace App\Core\Http\Controllers\Modules;

use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModuleSelectorController extends Controller
{
    public function __construct(
        private readonly ModuleAccessServiceInterface $moduleAccess,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $modules = $this->moduleAccess->accessibleModulesForUser($request->user());

        if ($modules->count() === 1) {
            $module = $modules->first();
            $this->moduleAccess->rememberActiveModule($module);

            return redirect()->to($this->moduleAccess->homePathForModule($module));
        }

        return view('modules.index', [
            'modules' => $modules,
        ]);
    }

    public function open(Request $request, string $moduleCode): RedirectResponse
    {
        $module = $this->moduleAccess->findActiveModuleByCode($moduleCode);

        abort_if(! $module, 404);
        abort_unless(
            $this->moduleAccess->hasActiveModuleAccess($request->user(), (string) $module->id),
            403
        );

        $this->moduleAccess->rememberActiveModule($module);

        return redirect()->to($this->moduleAccess->homePathForModule($module));
    }
}
