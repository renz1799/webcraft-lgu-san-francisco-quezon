<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function comingsoon()
    {
        return view('pages.authentication.comingsoon');
    }

    public function createpassword_basic()
    {
        return view('pages.authentication.createpassword-basic');
    }

    public function createpassword_cover()
    {
        return view('pages.authentication.createpassword-cover');
    }

    public function lockscreen_basic()
    {
        return view('pages.authentication.lockscreen-basic');
    }

    public function lockscreen_cover()
    {
        return view('pages.authentication.lockscreen-cover');
    }

    public function resetpassword_basic()
    {
        return view('pages.authentication.resetpassword-basic');
    }

    public function resetpassword_cover()
    {
        return view('pages.authentication.resetpassword-cover');
    }

    public function signup_basic()
    {
        return view('pages.authentication.signup-basic');
    }

    public function signup_cover()
    {
        return view('pages.authentication.signup-cover');
    }

    public function signin_basic()
    {
        return view('pages.authentication.signin-basic');
    }

    public function signin_cover()
    {
        return view('pages.authentication.signin-cover');
    }

    public function twostep_verification_basic()
    {
        return view('pages.authentication.twostep-verification-basic');
    }

    public function twostep_verification_cover()
    {
        return view('pages.authentication.twostep-verification-cover');
    }

    public function under_maintenance()
    {
        return view('pages.authentication.under-maintenance');
    }

}
