<?php

namespace App\Core\Http\Controllers\Dashboard;

use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardsController extends Controller
{
    public function __construct(
        private readonly ModuleAccessServiceInterface $moduleAccess,
    ) {}

    public function index(): RedirectResponse|View
    {
        $user = auth()->user();

        if ($user) {
            return redirect()->to($this->moduleAccess->postLoginRedirectPathForUser($user));
        }

        return view('pages.dashboards.index');
    }
}
