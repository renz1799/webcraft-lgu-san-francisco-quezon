<?php

namespace App\Modules\GSO\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class GsoDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('gso::dashboard.index');
    }
}
