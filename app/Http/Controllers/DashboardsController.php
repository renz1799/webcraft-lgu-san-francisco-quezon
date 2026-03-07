<?php

namespace App\Http\Controllers;

class DashboardsController extends Controller
{
    public function index()
    {
        return view('pages.dashboards.index');
    }
}
