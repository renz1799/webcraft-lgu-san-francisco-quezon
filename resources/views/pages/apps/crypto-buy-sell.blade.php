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
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Buy &amp; Sell</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Crypto
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                  Buy &amp; Sell
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <div class="container">

                            <!-- Start::row-1 -->
                            <div class="grid grid-cols-12 gap-6">
                                <div class="xl:col-span-4 col-span-12">
                                    <div class="box custom-box">
                                        <div class="box-header">
                                            <div class="box-title">
                                                Buy Crypto
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div>
                                                <div class="input-group mb-4 sm:flex block flex-nowrap crypto-buy-sell">
                                                    <input type="text" class="form-control !border-s crypto-buy-sell-input border-e-0" aria-label="crypto buy select" placeholder="Select Currency">
                                                    <select class="form-control" data-trigger id="choices-single-default">
                                                        <option value="">BTC</option>
                                                        <option value="Choice 1">ETH</option>
                                                        <option value="Choice 2">XRP</option>
                                                        <option value="Choice 3">DASH</option>
                                                        <option value="Choice 4">NEO</option>
                                                        <option value="Choice 5">LTC</option>
                                                        <option value="Choice 6">BSD</option>
                                                    </select>
                                                </div>
                                                <div class="input-group mb-4 sm:flex block flex-nowrap crypto-buy-sell">
                                                    <input type="text" class="form-control !border-s crypto-buy-sell-input border-e-0" aria-label="crypto buy select" placeholder="36,335.00">
                                                    <select class="form-control" data-trigger id="choices-single-default1">
                                                        <option value="">USD</option>
                                                        <option value="Choice 1">AED</option>
                                                        <option value="Choice 2">AUD</option>
                                                        <option value="Choice 3">ARS</option>
                                                        <option value="Choice 4">AZN</option>
                                                        <option value="Choice 5">BGN</option>
                                                        <option value="Choice 6">BRL</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <div class="text-[.875rem] py-2"><span class="font-semibold text-dark">Price:</span><span class="text-[#8c9097] dark:text-white/50 ms-2 text-[.875rem] inline-block">6.003435</span><span class="text-dark font-semibold ltr:float-right rtl:float-left">BTC</span></div>
                                                    <div class="text-[.875rem] py-2"><span class="font-semibold text-dark">Amount:</span><span class="text-[#8c9097] dark:text-white/50 ms-2 text-[.875rem] inline-block">2,34,4543.00</span><span class="text-dark font-semibold ltr:float-right rtl:float-left">LTC</span></div>
                                                    <div class="font-semibold text-[.875rem] py-2">Total: <span class="text-[.875rem] inline-block">22.00 BTC</span></div>
                                                    <div class="text-[0.75rem] text-success">Additional Charges: 0.32%(0.0001231 BTC)</div>
                                                    <label class="font-semibold text-[0.75rem] mt-4 mb-2">SELECT PAYMENT METHOD :</label>
                                                    <div class="grid grid-cols-12 gap-2">
                                                        <div class="xl:col-span-6 col-span-12">
                                                            <div class="!p-2 border dark:border-defaultborder/10 rounded-md">
                                                                <div class="form-check !ps-0 !mb-0 !min-h-[auto]">
                                                                    <input class="form-check-input !align-middle" type="radio" name="flexRadioDefault" id="flexRadioDefault1" checked>
                                                                    <label class="form-check-label text-[0.75rem]" for="flexRadioDefault1">
                                                                        Credit / Debit Cards
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="xl:col-span-6 col-span-12">
                                                            <div class="!p-2 border dark:border-defaultborder/10 rounded-md">
                                                                <div class="form-check !ps-0 !mb-0 !min-h-[auto]">
                                                                    <input class="form-check-input !align-middle" type="radio" name="flexRadioDefault" id="flexRadioDefault2">
                                                                    <label class="form-check-label text-[0.75rem]" for="flexRadioDefault2">
                                                                        Paypal
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="xl:col-span-12 col-span-12">
                                                            <div class="!p-2 border dark:border-defaultborder/10 rounded-md">
                                                                <div class="form-check !ps-0 !mb-0 !min-h-[auto]">
                                                                    <input class="form-check-input !align-middle" type="radio" name="flexRadioDefault" id="flexRadioDefault3">
                                                                    <label class="form-check-label text-[0.75rem]" for="flexRadioDefault3">
                                                                        Wallet
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="grid mt-6 pt-1">
                                                    <button type="button" class="ti-btn ti-btn-primary-full btn-wave ti-btn-lg dark:border-defaultborder/10">BUY</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="xl:col-span-4 col-span-12">
                                    <div class="box custom-box">
                                        <div class="box-header">
                                            <div class="box-title">
                                                Sell Crypto
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="tab-pane !border-0 !p-0" id="sell-crypto1" role="tabpanel"
                                                aria-labelledby="sell-crypto1">
                                                <div class="input-group mb-4 sm:flex block flex-nowrap crypto-buy-sell">
                                                    <input type="text" class="form-control !border-s crypto-buy-sell-input border-e-0" aria-label="crypto buy select" placeholder="Select Currency">
                                                    <select class="form-control !rounded-s-none" data-trigger id="choices-single-default2">
                                                        <option value="">BTC</option>
                                                        <option value="Choice 1">ETH</option>
                                                        <option value="Choice 2">XRP</option>
                                                        <option value="Choice 3">DASH</option>
                                                        <option value="Choice 4">NEO</option>
                                                        <option value="Choice 5">LTC</option>
                                                        <option value="Choice 6">BSD</option>
                                                    </select>
                                                </div>
                                                <div class="input-group mb-4 sm:flex block flex-nowrap crypto-buy-sell">
                                                    <input type="text" class="form-control !border-s crypto-buy-sell-input border-e-0" aria-label="crypto buy select" placeholder="36,335.00">
                                                    <select class="form-control" data-trigger id="choices-single-default3">
                                                        <option value="">USD</option>
                                                        <option value="Choice 1">AED</option>
                                                        <option value="Choice 2">AUD</option>
                                                        <option value="Choice 3">ARS</option>
                                                        <option value="Choice 4">AZN</option>
                                                        <option value="Choice 5">BGN</option>
                                                        <option value="Choice 6">BRL</option>
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <span class="form-label">Crypto Value :</span>
                                                    <div class="relative">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                                        <div class="p-2 border dark:border-defaultborder/10 rounded-md flex items-center justify-between gap-4 mt-1">
                                                            <div class="gap-2 flex items-center">
                                                                <div class="leading-none">
                                                                    <span class="avatar bg-light p-2">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold">Bitcoin - BTC</div>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="font-semibold block">0.374638535 BTC</span>
                                                                <span class="text-[#8c9097] dark:text-white/50">$5,364.65</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-4">
                                                    <span class="form-label">Deposit To :</span>
                                                    <div class="relative">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                                        <div class="p-2 border dark:border-defaultborder/10 rounded-md flex items-center gap-2 mt-1">
                                                            <div class="leading-none">
                                                                <span class="avatar bg-light p-2">
                                                                    <i class="ri-bank-line text-info text-xl"></i>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <span class="font-semibold block">Banking</span>
                                                                <span class="text-[#8c9097] dark:text-white/50">Checking ...</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-[.875rem] py-2"><span class="font-semibold text-dark">Price:</span><span class="text-[#8c9097] dark:text-white/50 ms-2 text-[.875rem]">6.003435</span><span class="text-dark font-semibold ltr:float-right rtl:float-left">BTC</span></div>
                                                    <div class="text-[.875rem] py-2"><span class="font-semibold text-dark">Amount:</span><span class="text-[#8c9097] dark:text-white/50 ms-2 text-[.875rem]">2,34,4543.00</span><span class="text-dark font-semibold ltr:float-right rtl:float-left">LTC</span></div>
                                                </div>
                                                <div class="grid mt-6">
                                                    <button type="button" class="ti-btn ti-btn-danger-full btn-wave ti-btn-lg dark:border-defaultborder/10">SELL</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="xl:col-span-4 col-span-12">
                                    <div class="box custom-box">
                                        <div class="box-header">
                                            <div class="box-title">
                                                Quick Secure Transfer
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="tab-pane border-0 !p-0" id="sell-crypto" role="tabpanel"
                                                aria-labelledby="sell-crypto">
                                                <div class="mb-4">
                                                    <span class="form-label">Crypto Value :</span>
                                                    <div class="relative">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                                        <div class="p-2 border dark:border-defaultborder/10 rounded flex items-center justify-between gap-4 mt-1 rounded-md">
                                                            <div class="gap-2 flex items-center">
                                                                <div class="leading-none">
                                                                    <span class="avatar bg-light p-2">
                                                                        <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div class="font-semibold">Bitcoin - BTC</div>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="font-semibold block">0.374638535 BTC</span>
                                                                <span class="text-[#8c9097] dark:text-white/50">$5,364.65</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-4">
                                                    <span class="form-label">Deposit To :</span>
                                                    <div class="relative">
                                                        <a aria-label="anchor" href="javascript:void(0);" class="stretched-link"></a>
                                                        <div class="p-2 border dark:border-defaultborder/10 rounded flex items-center gap-2 mt-1 rounded-md">
                                                            <div class="leading-none">
                                                                <span class="avatar bg-light p-2">
                                                                    <i class="ri-bank-line text-info text-xl"></i>
                                                                </span>
                                                            </div>
                                                            <div>
                                                                <span class="font-semibold block">Banking</span>
                                                                <span class="text-[#8c9097] dark:text-white/50">Checking ...</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-4">
                                                    <span class="block font-semibold">Amount :</span>
                                                    <div class="flex items-center justify-between text-[#8c9097] dark:text-white/50">
                                                        <div>Weekly Bank Limit</div>
                                                        <div>$10,000 remaining</div>
                                                    </div>
                                                </div>
                                                <div class="input-group mb-4 sm:flex block flex-nowrap crypto-buy-sell">
                                                    <input type="text" class="form-control crypto-buy-sell-input border-e-0 !border-s !rounded-s-md" aria-label="crypto buy select" placeholder="36,335.00">
                                                    <select class="form-control" data-trigger id="choices-single-default4">
                                                        <option value="">USD</option>
                                                        <option value="Choice 1">AED</option>
                                                        <option value="Choice 2">AUD</option>
                                                        <option value="Choice 3">ARS</option>
                                                        <option value="Choice 4">AZN</option>
                                                        <option value="Choice 5">BGN</option>
                                                        <option value="Choice 6">BRL</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <div class="text-[.875rem] py-2"><span class="font-semibold text-dark">Price:</span><span class="text-[#8c9097] dark:text-white/50 ms-2 text-[.875rem]">6.003435</span><span class="text-dark font-semibold ltr:float-right rtl:float-left">BTC</span></div>
                                                    <div class="text-[.875rem] py-2"><span class="font-semibold text-dark">Amount:</span><span class="text-[#8c9097] dark:text-white/50 ms-2 text-[.875rem]">2,34,4543.00</span><span class="text-dark font-semibold ltr:float-right rtl:float-left">LTC</span></div>
                                                </div>
                                                <div class="grid mt-6">
                                                    <button type="button" class="ti-btn ti-btn-success-full btn-wave ti-btn-lg dark:border-defaultborder/10">Transfer</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--End::row-1 -->

                            <!-- Start:: row-2 -->
                            <div class="grid grid-cols-12 gap-6">
                                <div class="xl:col-span-12 col-span-12">
                                    <div class="box custom-box">
                                        <div class="box-header justify-between">
                                            <div class="box-title">
                                                Buy &amp; Sell Statistics
                                            </div>
                                            <div class="btn-group inline-flex flex-wrap" role="group" aria-label="Basic example">
                                                <button type="button" class="ti-btn ti-btn-primary-full !rounded-e-none btn-wave">1D</button>
                                                <button type="button" class="ti-btn ti-btn-primary !rounded-none  btn-wave">1W</button>
                                                <button type="button" class="ti-btn ti-btn-primary !rounded-none  btn-wave">1M</button>
                                                <button type="button" class="ti-btn ti-btn-primary !rounded-none  btn-wave">3M</button>
                                                <button type="button" class="ti-btn ti-btn-primary !rounded-none  btn-wave">6M</button>
                                                <button type="button" class="ti-btn ti-btn-primary !rounded-s-none  btn-wave">1Y</button>
                                            </div>
                                        </div>
                                        <div class="box-body !p-0">
                                          <div class="grid grid-cols-12 gap-6">
                                                <div class="xl:col-span-8 col-span-12 pe-0 border-e border-dashed dark:border-defaultborder/10">
                                                    <div class="flex flex-wrap items-center border-b border-dashed dark:border-defaultborder/10 mb-4 p-4 gap-4 ps-[3rem]">
                                                        <div>
                                                            <span class="block text-[0.75rem]">Total Buy</span>
                                                            <span class="block font-semibold text-[.9375rem] text-success">$324.25 USD</span>
                                                        </div>
                                                        <div>
                                                            <span class="block text-[0.75rem]">Total Sell</span>
                                                            <span class="block font-semibold text-[.9375rem] text-danger">$4,235.25 USD</span>
                                                        </div>
                                                        <div>
                                                            <span class="block text-[0.75rem]">Available Balance</span>
                                                            <span class="block font-semibold text-[.9375rem] text-primary">$22,803.92 USD</span>
                                                        </div>
                                                        <div>
                                                            <span class="block text-[0.75rem]">Total Crypto Asset Value</span>
                                                            <span class="block font-semibold text-[.9375rem] text-warning">$4,56,683.00 USD</span>
                                                        </div>
                                                        <div></div>
                                                    </div>
                                                    <div id="buy_sell-statistics" class="px-3"></div>
                                                </div>
                                                <div class="xl:col-span-4 col-span-12 xl:ps-0">
                                                    <div class="p-4">
                                                        <div class="box custom-box !bg-light shadow-none">
                                                            <div class="box-body">
                                                                <div class="flex items-center justify-between">
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] mb-2">BTC/USD</span>
                                                                        <span class="font-semibold h6 mb-0">27.53612</span>
                                                                        <span class="text-danger block text-[0.75rem] mt-1">-0.06%</span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="avatar avatar-xl avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/square-color/Bitcoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="box custom-box !bg-light shadow-none">
                                                            <div class="box-body">
                                                                <div class="flex items-center justify-between">
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] mb-2">ETH/USD</span>
                                                                        <span class="font-semibold h6 mb-0">20.6782</span>
                                                                        <span class="text-success block text-[0.75rem] mt-1">+2.86%</span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="avatar avatar-xl avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/square-color/Ethereum.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="box custom-box !bg-light shadow-none mb-0">
                                                            <div class="box-body">
                                                                <div class="flex items-center justify-between">
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] mb-2">LTC/USD</span>
                                                                        <span class="font-semibold h6 mb-0">54.2912</span>
                                                                        <span class="text-success block text-[0.75rem] mt-1">+15.93%</span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="avatar avatar-xl avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/crypto-currencies/square-color/Litecoin.svg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: row-2 -->
                            
                        </div>

@endsection

@section('scripts')
                        
        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

        <!-- Apex Charts JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- Crypto Buy & Sell JS -->
        @vite('resources/assets/js/crypto-buy_sell.js')
        

@endsection