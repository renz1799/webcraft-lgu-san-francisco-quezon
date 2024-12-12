@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Marketcap</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Crypto
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                  Marketcap
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-body">
                                        <div class="flex items-center mb-4">
                                            <div class="flex items-center">
                                                <div class="me-2">
                                                    <span class="avatar avatar-md avatar-rounded bg-light p-2">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <div class="mb-0 font-semibold">
                                                    Bitcoin - BTC
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <div id="bitcoin-chart"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-end">
                                            <div>
                                                <p class="mb-1">BTC / USD</p>
                                                <p class="text-[1.25rem] mb-0 font-semibold leading-none text-primary">$35,876.29</p>
                                            </div>
                                            <div class="ms-auto text-end mt-2">
                                                <p class="mb-0">$0.04</p>
                                                <p class="mb-0 text-[#8c9097] dark:text-white/50"><span class="text-[#8c9097] dark:text-white/50">Vol:</span>(+2.33%)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer !p-0">
                                        <div class="list-group border-0">
                                            <a href="javascript:void(0);" class="py-3 !px-[1.25rem] flex flex-col items-start border-t-0 border-x-0 !border-b dark:border-defaultborder/10 ">
                                                <div class="flex w-full justify-between items-center">
                                                    <p class="tx-14 mb-0 font-weight-semibold text-dark">Price Change <span class="badge bg-primary/10  ms-4 text-primary">Increased</span></p>
                                                    <p class="text-success mb-0 font-weight-normal tx-13">
                                                        <span class="numberfont">+280.30(0.96%)</span> <i class="fa fa-arrow-up"></i> today
                                                    </p>
                                                </div>
                                            </a>
                                            <a href="javascript:void(0);" class="py-3 !px-[1.25rem] flex flex-col items-start border-t-0 border-x-0 ">
                                                <div class="flex w-full justify-between items-center">
                                                    <p class="tx-14 mb-0 font-weight-semibold text-dark">Market Rank<span class="badge bg-secondary/10 text-secondary ms-4">3 Years</span></p>
                                                    <p class="text-dark mb-0 tx-15">
                                                        <span class="numberfont">#1</span>
                                                    </p>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-body">
                                        <div class="flex items-center mb-4">
                                            <div class="flex items-center">
                                                <div class="me-2">
                                                    <span class="avatar avatar-md avatar-rounded bg-light p-2">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Ethereum.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <div class="mb-0 font-semibold">
                                                    Etherium - ETH
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <div id="etherium-chart"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-end">
                                            <div>
                                                <p class="mb-1">ETH / USD</p>
                                                <p class="text-[1.25rem] mb-0 font-semibold leading-none text-primary">$31,244.12</p>
                                            </div>
                                            <div class="ms-auto text-end mt-2">
                                                <p class="mb-0">$2.57</p>
                                                <p class="mb-0 text-[#8c9097] dark:text-white/50"><span class="text-[#8c9097] dark:text-white/50">Vol:</span>(+13.45%)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer !p-0">
                                        <div class="list-group border-0">
                                            <a href="javascript:void(0);" class="py-3 px-[1.25rem] flex flex-col items-start border-t-0 border-x-0 border-b dark:border-defaultborder/10">
                                                <div class="flex w-full justify-between items-center">
                                                    <p class="tx-14 mb-0 font-weight-semibold text-dark">Price Change <span class="badge bg-primary/10 ms-3 text-primary">Increased</span></p>
                                                    <p class="text-success mb-0 font-weight-normal tx-13">
                                                        <span class="numberfont">+2,044.24(1.32%)</span> <i class="fa fa-arrow-up"></i> today
                                                    </p>
                                                </div>
                                            </a>
                                            <a href="javascript:void(0);" class="py-3 px-[1.25rem] flex flex-col items-start border-topacity-0 border-start-0 border-end-0 ">
                                                <div class="flex w-full justify-between items-center">
                                                    <p class="tx-14 mb-0 font-weight-semibold text-dark">Market Rank</p>
                                                    <p class="text-dark mb-0 tx-15">
                                                        <span class="numberfont">#2</span>
                                                    </p>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-body">
                                        <div class="flex items-center mb-4">
                                            <div class="flex items-center">
                                                <div class="me-2">
                                                    <span class="avatar avatar-md avatar-rounded bg-light p-2">
                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Dash.svg')}}" alt="">
                                                    </span>
                                                </div>
                                                <div class="mb-0 font-semibold">
                                                    Dash - DASH
                                                </div>
                                            </div>
                                            <div class="ms-auto">
                                                <div id="dashcoin-chart"></div>
                                            </div>
                                        </div>
                                        <div class="flex items-end">
                                            <div>
                                                <p class="mb-1">DASH / USD</p>
                                                <p class="text-[1.25rem] mb-0 font-semibold leading-none text-primary">$26,345.000</p>
                                            </div>
                                            <div class="ms-auto text-end mt-2">
                                                <p class="mb-0">$12.32</p>
                                                <p class="mb-0 text-[#8c9097] dark:text-white/50"><span class="text-[#8c9097] dark:text-white/50">Vol:</span>(+112.95%)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer !p-0">
                                        <div class="list-group">
                                            <a href="javascript:void(0);" class="py-3 px-[1.25rem] flex flex-col items-start border-t-0 border-x-0 border-b dark:border-defaultborder/10">
                                                <div class="flex w-full justify-between items-center">
                                                    <p class="tx-14 mb-0 font-weight-semibold text-dark">Price Change <span class="badge bg-primary/10 ms-3 text-primary">Increased</span></p>
                                                    <p class="text-success mb-0 font-weight-normal tx-13">
                                                        <span class="numberfont">+40.17 (1.52%)</span> <i class="fa fa-arrow-up"></i> today
                                                    </p>
                                                </div>
                                            </a>
                                            <a href="javascript:void(0);" class="py-3 px-[1.25rem] flex flex-col items-start border-t-0 border-x-0 !border-b-0 ">
                                                <div class="flex w-full justify-between items-center">
                                                    <p class="tx-14 mb-0 font-weight-semibold text-dark">Market Rank</p>
                                                    <p class="text-dark mb-0 tx-15">
                                                        <span class="numberfont">#105</span>
                                                    </p>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-12 col-span-12">
                                <div class="box custom-box overflow-hidden">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            My Top Currencies
                                        </div>
                                        <div>
                                            <button type="button" class="ti-btn btn-wave ti-btn-info !py-1 !px-2 !text-[0.75rem]">View All</button>
                                        </div>
                                    </div>
                                    <div class="box-body !p-0">
                                        <ul class="list-group list-group-flush">
                                            <li class="!py-1 !px-[1.25rem] border-b border-defaultborder dark:border-defaultborder/10">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <div>
                                                            <span class="avatar avatar-sm p-1 bg-light">
                                                                <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <span class="block font-semibold">Bitcoin</span>
                                                            <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">$29,9480</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50">Max Limit</span>
                                                        <span class="font-semibold block">50 BTC</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50">My Volume</span>
                                                        <span class="font-semibold block">31.2450 BTC</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="py-3 px-[1.25rem] border-b border-defaultborder dark:border-defaultborder/10">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <div>
                                                            <span class="avatar avatar-sm p-1 bg-light">
                                                                <img src="{{asset('build/assets/images/crypto-currencies/regular/litecoin.svg')}}" alt="">
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <span class="block font-semibold">Litecon</span>
                                                            <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">$92.98</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50">Max Limit</span>
                                                        <span class="font-semibold block">200 LTC</span>
                                                    </div>
                                                    <div class="">
                                                        <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50">My Volume</span>
                                                        <span class="font-semibold block">38.0023 LTC</span>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="py-3 px-[1.25rem]">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <div>
                                                            <span class="avatar avatar-sm p-1 bg-light">
                                                                <img src="{{asset('build/assets/images/crypto-currencies/regular/Ethereum.svg')}}" alt="">
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <span class="block font-semibold">Etherium</span>
                                                            <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">$1,895.96</span>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50">Max Limit</span>
                                                        <span class="font-semibold block">100 ETH</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50">My Volume</span>
                                                        <span class="font-semibold block">69.2412 BTC</span>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End::row-1 -->

                        <!-- Start::row-2  -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Crypto MarketCap
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <div>
                                                <input class="form-control form-control-sm" type="text" placeholder="Search Here" aria-label=".form-control-sm example">
                                            </div>
                                            <div class="hs-dropdown ti-dropdown">
                                                <a href="javascript:void(0);" class="ti-btn btn-wave ti-btn-primary-full !py-1 !px-2 !text-[0.75rem]" aria-expanded="false">
                                                    Sort By<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                                </a>
                                                <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">Market Cap</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">Price</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">Trading Volume</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">Price Change (24h)</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">Rank</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">A - Z</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">All-Time High (ATH)</a></li>
                                                </ul>
                                            </div>
                                            <div>
                                                <button type="button" class="ti-btn btn-wave ti-btn-secondary-full !py-1 !px-2 !text-[0.75rem]">View All</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-body !p-0">
                                        <div class="table-responsive">
                                            <table class="table whitespace-nowrap min-w-full">
                                                <thead>
                                                    <tr>
                                                        <th scope="col"></th>
                                                        <th scope="col"  class="font-semibold text-start">#</th>
                                                        <th scope="col" class="text-start">Crypto Name</th>
                                                        <th scope="col" class="text-start">MarketCap</th>
                                                        <th scope="col" class="text-start">Price<span class="text-[#8c9097] dark:text-white/50 ms-1">(USD)</span></th>
                                                        <th scope="col" class="text-start">1h Change</th>
                                                        <th scope="col" class="text-start">24h Change</th>
                                                        <th scope="col" class="text-start">Volume (24h)</th>
                                                        <th scope="col" class="text-start">Circulating Supply</th>
                                                        <th scope="col" class="text-start">last 1Week</th>
                                                        <th scope="col" class="text-start">Trade</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border border-defaultborder">
                                                        <td class="text-center">
                                                            <a aria-label="anchor" href="javascript:void(0);"><i class="ri-star-line text-[1rem] text-[#8c9097] dark:text-white/50"></i></a>
                                                        </td>
                                                        <td>1</td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Bitcoin.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold"><a href="javascript:void(0);">Bitcoin (BTC)</a></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">$582.23B</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">
                                                                <a href="javascript:void(0);">$29,948.80</a>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>0.483%</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success font-semibold"><i class="ti ti-arrow-narrow-up text-[.9375rem] font-semibold"></i>0.239%</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)">
                                                                <span class="block font-semibold">$11.79B USD</span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);">
                                                                <span class="font-semibold block mb-2">
                                                                    19.43M of (21M)
                                                                </span>
                                                                <div class="progress-stacked progress-xs w-[75%] mb-4 flex">
                                                                    <div class="progress-bar !bg-success opacity-[0.5] w-[88%]" ></div>
                                                                    <div class="progress-bar !rounded-s-none !rounded-e-full !bg-danger opacity-[0.5] w-[12%]" ></div>
                                                                </div>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div id="btc-chart1"></div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Trade</button>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder">
                                                        <td class="text-center">
                                                            <a aria-label="anchor" href="javascript:void(0);"><i class="ri-star-line text-[1rem] text-[#8c9097] dark:text-white/50"></i></a>
                                                        </td>
                                                        <td>2</td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Ethereum.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold"><a href="javascript:void(0);">Etherium (ETH)</a></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">$226.91B</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">
                                                                <a href="javascript:void(0);">$1,895.96</a>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>0.87%</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>0.29%</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)">
                                                                <span class="block font-semibold">$2.83B USD</span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);">
                                                                <span class="font-semibold block">
                                                                    120M
                                                                </span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div id="eth-chart1"></div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Trade</button>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder">
                                                        <td class="text-center">
                                                            <a aria-label="anchor" href="javascript:void(0);"><i class="ri-star-line text-[1rem] text-[#8c9097] dark:text-white/50"></i></a></td>
                                                        <td>3</td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Golem.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold"><a href="javascript:void(0);">Golem (GLM)</a></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">$202.07M</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">
                                                                <a href="javascript:void(0);">$0.201472</a>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success font-semibold"><i class="ti ti-arrow-narrow-up text-[.9375rem] font-semibold"></i>0.61%</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>34.96%</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)">
                                                                <span class="block font-semibold">$2,112,645 USD</span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);">
                                                                <span class="font-semibold block mb-2">
                                                                    1,000M
                                                                </span>
                                                                <div class="progress-stacked  progress-xs w-[75%] mb-4 flex">
                                                                    <div class="progress-bar !rounded-full !bg-success opacity-[0.5] w-full" ></div>
                                                                </div>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div id="glm-chart"></div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Trade</button>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder">
                                                        <td class="text-center">
                                                            <a aria-label="anchor" href="javascript:void(0);"><i class="ri-star-line text-[1rem] text-[#8c9097] dark:text-white/50"></i></a></td>
                                                        <td>4</td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Dash.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold"><a href="javascript:void(0);">Dash (DASH)</a></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">$365.877M</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">
                                                                <a href="javascript:void(0);">$32.13</a>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success font-semibold"><i class="ti ti-arrow-narrow-up text-[.9375rem] font-semibold"></i>0.59%</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>1.24%</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)">
                                                                <span class="block font-semibold">$3.61M USD</span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);">
                                                                <span class="font-semibold block mb-2">
                                                                    11.37M of (18.92M)
                                                                </span>
                                                                <div class="progress-stacked  progress-xs w-[75%] mb-4 flex">
                                                                    <div class="progress-bar !bg-success opacity-[0.5] w-[56%]" ></div>
                                                                    <div class="progress-bar !rounded-s-none !rounded-e-full !rounded-full !bg-danger opacity-[0.5] w-[44%]" ></div>
                                                                </div>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div id="dash-chart1"></div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Trade</button>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder">
                                                        <td class="text-center">
                                                            <a aria-label="anchor" href="javascript:void(0);"><i class="ri-star-line text-[1rem] text-[#8c9097] dark:text-white/50"></i></a></td>
                                                        <td>5</td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Litecoin.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold"><a href="javascript:void(0);">Litecoin (LTC)</a></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">$6.80B</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">
                                                                <a href="javascript:void(0);">$92.98</a>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-up text-[.9375rem] font-semibold"></i>0.90%</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>2.22%</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)">
                                                                <span class="block font-semibold">$11.46B USD</span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);">
                                                                <span class="font-semibold block mb-2">
                                                                    73.40M
                                                                </span>
                                                                <div class="progress-stacked  progress-xs w-[75%] mb-4 flex">
                                                                    <div class="progress-bar !bg-success !rounded-full opacity-[0.5] w-full" ></div>
                                                                </div>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div id="lite-chart"></div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Trade</button>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder">
                                                        <td class="text-center">
                                                            <a aria-label="anchor" href="javascript:void(0);"><i class="ri-star-line text-[1rem] text-[#8c9097] dark:text-white/50"></i></a></td>
                                                        <td>6</td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Ripple.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold"><a href="javascript:void(0);">Ripple (XRP)</a></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">$42.48B</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">
                                                                <a href="javascript:void(0);">$0.83</a>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success font-semibold"><i class="ti ti-arrow-narrow-up text-[.9375rem] font-semibold"></i>0.01%</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>0.91%</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)">
                                                                <span class="block font-semibold">$2.99B USD</span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);">
                                                                <span class="font-semibold block mb-2">
                                                                    52.54B of (100B)
                                                                </span>
                                                                <div class="progress-stacked  progress-xs w-[75%] mb-4 flex">
                                                                    <div class="progress-bar !bg-success opacity-[0.5] w-[52%]" ></div>
                                                                    <div class="progress-bar !rounded-s-none !rounded-e-full !bg-danger opacity-[0.5] w-[48%]" ></div>
                                                                </div>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div id="ripple-chart"></div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Trade</button>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder">
                                                        <td class="text-center">
                                                            <a aria-label="anchor" href="javascript:void(0);"><i class="ri-star-line text-[1rem] text-[#8c9097] dark:text-white/50"></i></a></td>
                                                        <td>7</td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/EOS.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold"><a href="javascript:void(0);">EOS</a></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">$85.2M</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">
                                                                <a href="javascript:void(0);">$0.765957</a>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-up text-[.9375rem] font-semibold"></i>0.61%</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>20.65%</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)">
                                                                <span class="block font-semibold">$116.91M USD</span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);">
                                                                <span class="font-semibold block mb-2">
                                                                    10.1B of (105B)
                                                                </span>
                                                                <div class="progress-stacked  progress-xs w-[75%] mb-4 flex">
                                                                    <div class="progress-bar !bg-success opacity-[0.5] w-[10%]" ></div>
                                                                    <div class="progress-bar !rounded-s-none !rounded-e-full  !bg-danger opacity-[0.5] w-[90%]" ></div>
                                                                </div>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div id="eos-chart"></div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Trade</button>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder">
                                                        <td class="text-center">
                                                            <a aria-label="anchor" href="javascript:void(0);"><i class="ri-star-line text-[1rem] text-[#8c9097] dark:text-white/50"></i></a></td>
                                                        <td>8</td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Bytecoin.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold"><a href="javascript:void(0);">Bytecoin (BCN)</a></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">$6.2M</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">
                                                                <a href="javascript:void(0);">$0.00039</a>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-up text-[.9375rem] font-semibold"></i>25.24%</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>27.12%</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)">
                                                                <span class="block font-semibold">$6,184 USD</span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);">
                                                                <span class="font-semibold block mb-2">
                                                                    184.02B of (184.07B)
                                                                </span>
                                                                <div class="progress-stacked  progress-xs w-[75%] mb-4 flex">
                                                                    <div class="progress-bar !bg-success opacity-[0.5] w-[99%]" ></div>
                                                                    <div class="progress-bar !rounded-s-none !rounded-e-full  !bg-danger opacity-[0.5] w-[1%]" ></div>
                                                                </div>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div id="bytecoin-chart"></div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Trade</button>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder">
                                                        <td class="text-center">
                                                            <a aria-label="anchor" href="javascript:void(0);"><i class="ri-star-line text-[1rem] text-[#8c9097] dark:text-white/50"></i></a></td>
                                                        <td>9</td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/IOTA.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold"><a href="javascript:void(0);">IOTA</a></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">$510.429M</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">
                                                                <a href="javascript:void(0);">$0.184992</a>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-up text-[.9375rem] font-semibold"></i>1.08%</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>1.41%</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)">
                                                                <span class="block font-semibold">$7.50M USD</span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);">
                                                                <span class="font-semibold block">
                                                                    2.78B
                                                                </span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div id="iota-chart"></div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Trade</button>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder">
                                                        <td class="text-center">
                                                            <a aria-label="anchor" href="javascript:void(0);"><i class="ri-star-line text-[1rem] text-[#8c9097] dark:text-white/50"></i></a></td>
                                                        <td>10</td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-xs avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/square-color/Monero.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold"><a href="javascript:void(0);">Monero</a></div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">$3.062B</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">
                                                                <a href="javascript:void(0);">$165.76</a>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-up text-[.9375rem] font-semibold"></i>3.22%</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger font-semibold"><i class="ti ti-arrow-narrow-down text-[.9375rem] font-semibold"></i>3.48%</span>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0)">
                                                                <span class="block font-semibold">$105.8M USD</span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <a href="javascript:void(0);">
                                                                <span class="font-semibold block">
                                                                    18.15M
                                                                </span>
                                                            </a>
                                                        </td>
                                                        <td>
                                                            <div id="monero-chart"></div>
                                                        </td>
                                                        <td>
                                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Trade</button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="box-footer !border-t-0">
                                        <nav aria-label="Page navigation">
                                            <ul class="ti-pagination  mb-0 justify-end">
                                                <li class="page-item disabled"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">Previous</a></li>
                                                <li class="page-item"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">1</a></li>
                                                <li class="page-item"><a class="page-link active px-3 py-[0.375rem]" href="javascript:void(0);">2</a></li>
                                                <li class="page-item"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">Next</a></li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End::row-2  -->
        
@endsection

@section('scripts')
 
        <!-- Apex Charts JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- Crypto MarketCap JS -->
        @vite('resources/assets/js/crypto-marketcap.js')
        

@endsection