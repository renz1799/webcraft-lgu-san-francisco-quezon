<?php

namespace App\Modules\GSO\Http\Controllers;

use App\Core\Models\User;
use App\Http\Controllers\Controller;
use App\Modules\GSO\Services\Contracts\GsoDashboardServiceInterface;
use Illuminate\Contracts\View\View;

class GsoDashboardController extends Controller
{
    public function __construct(
        private readonly GsoDashboardServiceInterface $dashboard,
    ) {
    }

    public function __invoke(): View
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);

        return view('gso::dashboard.index', $this->dashboard->build($user));
    }
}
