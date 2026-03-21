<?php

namespace App\Core\Providers;

use App\Core\Http\View\Composers\HeaderComposer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::addLocation(resource_path('core/views'));
        Blade::anonymousComponentPath(resource_path('core/views/components'));

        // Assign the HeaderComposer to the header view
        View::composer('layouts.components.header', HeaderComposer::class);
        View::composer('layouts.partials.header', HeaderComposer::class);
    }
}
