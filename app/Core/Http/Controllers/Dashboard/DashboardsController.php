<?php

namespace App\Core\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;

class DashboardsController extends Controller
{
    public function index()
    {
        return view('pages.dashboards.index');
    }
}
