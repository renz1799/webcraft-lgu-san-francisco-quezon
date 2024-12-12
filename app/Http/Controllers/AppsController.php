<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppsController extends Controller
{
    public function full_calendar()
    {
        return view('pages.apps.full-calendar');
    }

    public function gallery()
    {
        return view('pages.apps.gallery');
    }

    public function sweetalerts()
    {
        return view('pages.apps.sweetalerts');
    }

    public function projects_list()
    {
        return view('pages.apps.projects-list');
    }

    public function projects_overview()
    {
        return view('pages.apps.projects-overview');
    }

    public function projects_create()
    {
        return view('pages.apps.projects-create');
    }

    public function job_details()
    {
        return view('pages.apps.job-details');
    }

    public function job_company_search()
    {
        return view('pages.apps.job-company-search');
    }

    public function job_search()
    {
        return view('pages.apps.job-search');
    }
    public function job_post()
    {
        return view('pages.apps.job-post');
    }

    public function job_list()
    {
        return view('pages.apps.job-list');
    }

    public function job_candidate_search()
    {
        return view('pages.apps.job-candidate-search');
    }

    public function job_candidate_details()
    {
        return view('pages.apps.job-candidate-details');
    }

    public function nft_marketplace()
    {
        return view('pages.apps.nft-marketplace');
    }
    
    public function nft_details()
    {
        return view('pages.apps.nft-details');
    }

    public function nft_create()
    {
        return view('pages.apps.nft-create');
    }

    public function nft_wallet_integration()
    {
        return view('pages.apps.nft-wallet-integration');
    }

    public function nft_live_auction()
    {
        return view('pages.apps.nft-live-auction');
    }

    public function crm_contacts()
    {
        return view('pages.apps.crm-contacts');
    }

    public function crm_companies()
    {
        return view('pages.apps.crm-companies');
    }

    public function crm_deals()
    {
        return view('pages.apps.crm-deals');
    }

    public function crm_leads()
    {
        return view('pages.apps.crm-leads');
    }

    public function crypto_transactions()
    {
        return view('pages.apps.crypto-transactions');
    }

    public function crypto_currency_exchange()
    {
        return view('pages.apps.crypto-currency-exchange');
    }

    public function crypto_buy_sell()
    {
        return view('pages.apps.crypto-buy-sell');
    }

    public function crypto_marketcap()
    {
        return view('pages.apps.crypto-marketcap');
    }

    public function crypto_wallet()
    {
        return view('pages.apps.crypto-wallet');
    }

}
