@extends('layouts.master')

@section('styles')
 
        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">

        <!-- Tom Select Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/tom-select/css/tom-select.default.min.css')}}">

@endsection

@section('content')
 
                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Currency Exchange</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                Crypto
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                               Currency Exchange
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row-1 -->
                    <div class="grid grid-cols-12 gap-x-6">
                        <div class="xl:col-span-12 col-span-12">
                            <div class="box custom-box currency-exchange-box">
                                <div class="box-body !p-[3rem] flex items-center justify-center">
                                    <div class="container">
                                        <h3 class="text-white text-center">Buy and Sell Coins without additional fees</h3>
                                        <span class="block text-[1rem] text-white text-center opacity-[0.8] !mb-4 ">
                                            Buy now and get +50% extra bonus Minimum pre-sale amount 100 Crypto Coin. We accept BTC crypto-currency..
                                        </span>
                                        <div class="p-4 mb-4 rounded currency-exchange-area">
                                            <div class="grid grid-cols-12 sm:gap-4 gap-2">
                                                <div class="xxl:col-span-3 col-span-12">
                                                    <input type="text" class="form-control" value="0.0453" placeholder="Enter Amount">
                                                </div>
                                                <div class="xxl:col-span-2 col-span-12">
                                                    <div>
                                                        <select class="form-control" data-trigger name="Vacancies">
                                                            <option value="Choice 1">Bitcoin</option>
                                                            <option value="Choice 2">Etherium</option>
                                                            <option value="Choice 3">Litecoin</option>
                                                            <option value="Choice 4">Ripple</option>
                                                            <option value="Choice 5">Cardano</option>
                                                            <option value="Choice 6">Neo</option>
                                                            <option value="Choice 7">Stellar</option>
                                                            <option value="Choice 8">EOS</option>
                                                            <option value="Choice 9">NEM</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="xxl:col-span-2 col-span-12 flex items-center justify-center">
                                                    <div class="!text-[2rem] text-white text-center op-8 leading-none">
                                                        =
                                                    </div>
                                                </div>
                                                <div class="xxl:col-span-3 col-span-12">
                                                    <input type="text" class="form-control" value="1350.93" placeholder="Exchange Amount">
                                                </div>
                                                <div class="xxl:col-span-2 col-span-12">
                                                    <select class="form-control" data-trigger>
                                                        <option value="Choice 1">USD</option>
                                                        <option value="Choice 2">Pound</option>
                                                        <option value="Choice 3">Rupee</option>
                                                        <option value="Choice 4">Euro</option>
                                                        <option value="Choice 5">Won</option>
                                                        <option value="Choice 6">Dinar</option>
                                                        <option value="Choice 7">Rial</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="button" class="ti-btn ti-btn-success-full btn-wave">Exchange Now</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->

                    <!-- Start:: row-2 -->
                    <div class="grid grid-cols-12 gap-x-6 justify-center">
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box custom-box overflow-hidden">
                                <div class="box-body mb-4">
                                    <div class="flex items-start justify-between mb-4 flex-wrap">
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-rounded avatar-xs">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Bitcoin.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <h6 class="font-semibold mb-0">Bitcoin - BTC</h6>
                                            </div>
                                            <span class="text-[1.5625rem] flex items-center">24.3% <span class="text-[0.75rem] text-warning opacity-[0.7] ms-2">+0.59<i class="ti ti-arrow-big-up-line ms-1 inline-flex"></i></span><span class="badge bg-success-transparent text-[.625rem] ms-2">24H</span></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="block text-[.875rem] mb-1 text-primary">0.00434</span>
                                            <span class="block text-success font-semibold">$30.29</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="btc-currency-chart" class="mt-1 w-full"></div>
                            </div>
                        </div>
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box custom-box overflow-hidden">
                                <div class="box-body mb-4">
                                    <div class="flex items-start justify-between mb-4 flex-wrap">
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-rounded avatar-xs">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Ethereum.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <h6 class="font-semibold mb-0">Etherium - ETH</h6>
                                            </div>
                                            <span class="text-[1.5625rem] flex items-center">17.67% <span class="text-[0.75rem] text-warning opacity-[0.7] ms-2">+1.30<i class="ti ti-arrow-big-up-line ms-1 inline-flex"></i></span></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="block text-[.875rem] mb-1 text-secondary">1.2923</span>
                                            <span class="block text-success font-semibold">$2,283.73</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="eth-currency-chart" class="mt-1 w-full"></div>
                            </div>
                        </div>
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box custom-box overflow-hidden">
                                <div class="box-body mb-4">
                                    <div class="flex items-start justify-between mb-4 flex-wrap">
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-rounded avatar-xs">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Dash.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <h6 class="font-semibold mb-0">Dash - DASH</h6>
                                            </div>
                                            <span class="text-[1.5625rem] flex items-center">17.67% <span class="text-[0.75rem] text-warning opacity-[0.7] ms-2">+1.30<i class="ti ti-arrow-big-up-line ms-1 inline-flex"></i></span></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="block text-[.875rem] mb-1 text-success">1.2923</span>
                                            <span class="block text-success font-semibold">$2,283.73</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="dash-currency-chart" class="mt-1 w-full"></div>
                            </div>
                        </div>
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box custom-box overflow-hidden">
                                <div class="box-body mb-4">
                                    <div class="flex items-start justify-between mb-4 flex-wrap">
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-rounded avatar-xs">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Litecoin.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <h6 class="font-semibold mb-0">Litecoin - LTC</h6>
                                            </div>
                                            <span class="text-[1.5625rem] flex items-center">17.67% <span class="text-[0.75rem] text-warning opacity-[0.7] ms-2">+1.30<i class="ti ti-arrow-big-up-line ms-1 inline-flex"></i></span></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="block text-[.875rem] mb-1 text-warning">1.2923</span>
                                            <span class="block text-success font-semibold">$2,283.73</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="ltc-currency-chart" class="mt-1 w-full"></div>
                            </div>
                        </div>
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box custom-box overflow-hidden">
                                <div class="box-body mb-4">
                                    <div class="flex items-start justify-between mb-4 flex-wrap">
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-rounded avatar-xs">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Ripple.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <h6 class="font-semibold mb-0">Ripple - XRS</h6>
                                            </div>
                                            <span class="text-[1.5625rem] flex items-center">17.67% <span class="text-[0.75rem] text-warning opacity-[0.7] ms-2">+1.30<i class="ti ti-arrow-big-up-line ms-1 inline-flex"></i></span></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="block text-[.875rem] mb-1 text-pinkmain">1.2923</span>
                                            <span class="block text-success font-semibold">$2,283.73</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="xrs-currency-chart" class="mt-1 w-full"></div>
                            </div>
                        </div>
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box custom-box overflow-hidden">
                                <div class="box-body mb-4">
                                    <div class="flex items-start justify-between mb-4 flex-wrap">
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-rounded avatar-xs">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Golem.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <h6 class="font-semibold mb-0">Golem - GLM</h6>
                                            </div>
                                            <span class="text-[1.5625rem] flex items-center">17.67% <span class="text-[0.75rem] text-warning opacity-[0.7] ms-2">+1.30<i class="ti ti-arrow-big-up-line ms-1 inline-flex"></i></span></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="block text-[.875rem] mb-1 text-purplemain">1.2923</span>
                                            <span class="block text-success font-semibold">$2,283.73</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="glm-currency-chart" class="mt-1 w-full"></div>
                            </div>
                        </div>
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box custom-box overflow-hidden">
                                <div class="box-body mb-4">
                                    <div class="flex items-start justify-between mb-4 flex-wrap">
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-rounded avatar-xs">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Monero.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <h6 class="font-semibold mb-0">Monero</h6>
                                            </div>
                                            <span class="text-[1.5625rem] flex items-center">17.67% <span class="text-[0.75rem] text-warning opacity-[0.7] ms-2">+1.30<i class="ti ti-arrow-big-up-line ms-1 inline-flex"></i></span></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="block text-[.875rem] mb-1 text-danger">1.2923</span>
                                            <span class="block text-success font-semibold">$2,283.73</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="monero-currency-chart" class="mt-1 w-full"></div>
                            </div>
                        </div>
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box custom-box overflow-hidden">
                                <div class="box-body mb-4">
                                    <div class="flex items-start justify-between mb-4 flex-wrap">
                                        <div>
                                            <div class="flex items-center gap-2 mb-4">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-rounded avatar-xs">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/EOS.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <h6 class="font-semibold mb-0">EOS</h6>
                                            </div>
                                            <span class="text-[1.5625rem] flex items-center">17.67% <span class="text-[0.75rem] text-warning opacity-[0.7] ms-2">+1.30<i class="ti ti-arrow-big-up-line ms-1 inline-flex"></i></span></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="block text-[.875rem] mb-1 text-info">1.2923</span>
                                            <span class="block text-success font-semibold">$2,283.73</span>
                                        </div>
                                    </div>
                                </div>
                                <div id="eos-currency-chart" class="mt-1 w-full"></div>
                            </div>
                        </div>
                        <div class="xl:col-span-12 col-span-12">
                            <div class="text-center my-6">
                                <button class="ti-btn btn-wave ti-btn-primary !border !border-primary" type="button" disabled>
                                    <span class="ti-spinner  !w-[1rem] !h-[1rem] align-middle" role="status"
                                        aria-hidden="true"></span>
                                    Loading...
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- End:: row-2 -->

@endsection

@section('scripts')

    
        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

        <!-- Apex Charts JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- Crypto Currency Exchange JS -->
        @vite('resources/assets/js/crypto-currency-exchange.js')


@endsection