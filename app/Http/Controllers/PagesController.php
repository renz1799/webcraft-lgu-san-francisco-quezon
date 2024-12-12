<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PagesController extends Controller
{
    public function aboutus()
    {
        return view('pages.pages.aboutus');
    }

    public function blog()
    {
        return view('pages.pages.blog');
    }

    public function blog_details()
    {
        return view('pages.pages.blog-details');
    }

    public function blog_create()
    {
        return view('pages.pages.blog-create');
    }

    public function chat()
    {
        return view('pages.pages.chat');
    }

    public function contacts()
    {
        return view('pages.pages.contacts');
    }

    public function contactus()
    {
        return view('pages.pages.contactus');
    }

    public function add_products()
    {
        return view('pages.pages.add-products');
    }

    public function cart()
    {
        return view('pages.pages.cart');
    }

    public function checkout()
    {
        return view('pages.pages.checkout');
    }

    public function edit_products()
    {
        return view('pages.pages.edit-products');
    }

    public function order_details()
    {
        return view('pages.pages.order-details');
    }

    public function orders()
    {
        return view('pages.pages.orders');
    }

    public function products()
    {
        return view('pages.pages.products');
    }

    public function products_details()
    {
        return view('pages.pages.products-details');
    }

    public function products_list()
    {
        return view('pages.pages.products-list');
    }

    public function wishlist()
    {
        return view('pages.pages.wishlist');
    }

    public function mail()
    {
        return view('pages.pages.mail');
    }

    public function mail_settings()
    {
        return view('pages.pages.mail-settings');
    }

    public function empty_page()
    {
        return view('pages.pages.empty-page');
    }

    public function faqs()
    {
        return view('pages.pages.faqs');
    }

    public function filemanager()
    {
        return view('pages.pages.filemanager');
    }

    public function invoice_create()
    {
        return view('pages.pages.invoice-create');
    }

    public function invoice_details()
    {
        return view('pages.pages.invoice-details');
    }

    public function invoice_list()
    {
        return view('pages.pages.invoice-list');
    }

    public function landing()
    {
        return view('pages.pages.landing');
    }

    public function landing_jobs()
    {
        return view('pages.pages.landing-jobs');
    }

    public function notifications()
    {
        return view('pages.pages.notifications');
    }

    public function pricing()
    {
        return view('pages.pages.pricing');
    }

    public function profile()
    {
        return view('pages.pages.profile');
    }

    public function reviews()
    {
        return view('pages.pages.reviews');
    }

    public function team()
    {
        return view('pages.pages.team');
    }

    public function terms()
    {
        return view('pages.pages.terms');
    }

    public function timeline()
    {
        return view('pages.pages.timeline');
    }

    public function todo()
    {
        return view('pages.pages.todo');
    }

}
