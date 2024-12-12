<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UielementsController extends Controller
{
    public function alerts()
    {
        return view('pages.uielements.alerts');
    }

    public function badges()
    {
        return view('pages.uielements.badges');
    }

    public function breadcrumbs()
    {
        return view('pages.uielements.breadcrumbs');
    }

    public function blockquotes()
    {
        return view('pages.uielements.blockquotes');
    }

    public function buttons()
    {
        return view('pages.uielements.buttons');
    }

    public function buttongroups()
    {
        return view('pages.uielements.buttongroups');
    }
    public function cards()
    {
        return view('pages.uielements.cards');
    }

    public function dropdowns()
    {
        return view('pages.uielements.dropdowns');
    }

    public function images_figures()
    {
        return view('pages.uielements.images-figures');
    }

    public function indicators()
    {
        return view('pages.uielements.indicators');
    }

    public function listgroups()
    {
        return view('pages.uielements.listgroups');
    }

    public function navs_tabs()
    {
        return view('pages.uielements.navs-tabs');
    }

    public function object_fit()
    {
        return view('pages.uielements.object-fit');
    }

    public function paginations()
    {
        return view('pages.uielements.paginations');
    }

    public function popovers()
    {
        return view('pages.uielements.popovers');
    }
    
    public function progress()
    {
        return view('pages.uielements.progress');
    }

    public function spinners()
    {
        return view('pages.uielements.spinners');
    }

    public function toasts()
    {
        return view('pages.uielements.toasts');
    }

    public function tooltips()
    {
        return view('pages.uielements.tooltips');
    }
    
}
