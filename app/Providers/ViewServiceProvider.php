<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Http\View\Composers\HeaderComposer;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Assign the HeaderComposer to the header view
        View::composer('layouts.components.header', HeaderComposer::class);
    }
}
