<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdvanceduiController extends Controller
{
    public function accordions_collapse()
    {
        return view('pages.advancedui.accordions-collapse');
    }                                

    public function custom_scrollbar()
    {
        return view('pages.advancedui.custom-scrollbar');
    }

    public function draggable_cards()
    {
        return view('pages.advancedui.draggable-cards');
    }

    public function modals_closes()
    {
        return view('pages.advancedui.modals-closes');
    }

    public function navbars()
    {
        return view('pages.advancedui.navbars');
    }
    
    public function offcanvas()
    {
        return view('pages.advancedui.offcanvas');
    }

    public function ratings()
    {
        return view('pages.advancedui.ratings');
    }

    public function scrollspy()
    {
        return view('pages.advancedui.scrollspy');
    }

    public function stepper()
    {
        return view('pages.advancedui.stepper');
    }
    
    public function swiperjs()
    {
        return view('pages.advancedui.swiperjs');
    }

}
