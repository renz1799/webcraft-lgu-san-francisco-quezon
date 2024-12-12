@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')
 
                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Wallet</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Crypto
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                  Wallet
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
                                                BTC WALLET
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="leading-none">
                                                        <span class="avatar avatar-rounded">
                                                            <img src="{{asset('build/assets/images/crypto-currencies/square-color/Bitcoin.svg')}}" alt="">
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">Available BTC</span>
                                                        <span class="font-semibold">0.05437 BTC</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="font-semibold">$1646.94 USD</span>
                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">In USD</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box-footer">
                                            <div class="btn-list text-center">
                                                <button type="button" class="ti-btn ti-btn-primary min-w-[9.375rem] me-1 btn-wave">Deposit</button>
                                                <button type="button" class="ti-btn ti-btn-success min-w-[9.375rem] btn-wave">Withdraw</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box custom-box">
                                        <div class="box-header">
                                            <div class="box-title">
                                                ETH WALLET
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="leading-none">
                                                        <span class="avatar avatar-rounded">
                                                            <img src="{{asset('build/assets/images/crypto-currencies/square-color/Ethereum.svg')}}" alt="">
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">Available ETH</span>
                                                        <span class="font-semibold">2.3892 ETH</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="font-semibold">$4581.24 USD</span>
                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">In USD</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box-footer">
                                            <div class="btn-list text-center">
                                                <button type="button" class="ti-btn ti-btn-primary min-w-[9.375rem] me-1 btn-wave">Deposit</button>
                                                <button type="button" class="ti-btn ti-btn-success min-w-[9.375rem] btn-wave">Withdraw</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box custom-box">
                                        <div class="box-header">
                                            <div class="box-title">
                                                XRP WALLET
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="leading-none">
                                                        <span class="avatar avatar-rounded">
                                                            <img src="{{asset('build/assets/images/crypto-currencies/square-color/Ripple.svg')}}" alt="">
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">Available XRP</span>
                                                        <span class="font-semibold">234.943 XRP</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="font-semibold">$192.29 USD</span>
                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">In USD</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box-footer">
                                            <div class="btn-list text-center">
                                                <button type="button" class="ti-btn ti-btn-primary min-w-[9.375rem] me-1 btn-wave">Deposit</button>
                                                <button type="button" class="ti-btn ti-btn-success min-w-[9.375rem] btn-wave">Withdraw</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box custom-box">
                                        <div class="box-header">
                                            <div class="box-title">
                                                LTC WALLET
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="leading-none">
                                                        <span class="avatar avatar-rounded">
                                                            <img src="{{asset('build/assets/images/crypto-currencies/square-color/Litecoin.svg')}}" alt="">
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">Available LTC</span>
                                                        <span class="font-semibold">37.254 LTC</span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <span class="font-semibold">$3519.01 USD</span>
                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.75rem] font-normal">In USD</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box-footer">
                                            <div class="btn-list text-center">
                                                <button type="button" class="ti-btn ti-btn-primary min-w-[9.375rem] me-1 btn-wave">Deposit</button>
                                                <button type="button" class="ti-btn ti-btn-success min-w-[9.375rem] btn-wave">Withdraw</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="xl:col-span-8 col-span-12">
                                  <div class="grid grid-cols-12 gap-6">
                                        <div class="xl:col-span-12 col-span-12">
                                            <div class="box custom-box">
                                                <div class="box-header justify-between">
                                                    <div class="box-title">
                                                        BTC Wallet Address
                                                    </div>
                                                    <div>
                                                        <button type="button" class="ti-btn ti-btn-outline-primary btn-wave">Connect</button>
                                                    </div>
                                                </div>
                                                <div class="box-body">
                                                    <div class="flex items-center flex-wrap justify-between gap-4 mb-3">
                                                      <div class="flex-grow">
                                                          <label for="btc-wallet-address1" class="form-label">Wallet Address</label>
                                                          <div class="input-group">
                                                                <input type="text" class="form-control !border-s" id="btc-wallet-address1" value="afb0dc8bc84625587b85415c86ef43ed8df" placeholder="Placeholder">
                                                                <button type="button" class="ti-btn btn-wave ti-btn-primary-full !mb-0">Copy</button>
                                                            </div>
                                                      </div>
                                                      <div>
                                                            <span class="avatar avatar-xxl border dark:border-defaultborder/10">
                                                                <img src="{{asset('build/assets/images/media/media-89.png')}}" class="p-1 qr-image" alt="">
                                                            </span>
                                                      </div>
                                                    </div>
                                                    <div class="grid grid-cols-12 gap-4">
                                                        <div class="xl:col-span-4 col-span-12">
                                                            <div class="rounded-md p-4 bg-light">
                                                                <div class="flex items-center gap-4">
                                                                    <div class="leading-none">
                                                                        <span class="avatar bg-success/10 !text-success">
                                                                            <i class="ti ti-arrow-narrow-down text-[1.25rem]"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50">Received</span>
                                                                        <span class="block font-semibold">6.2834 <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50 font-normal">BTC</span></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="xl:col-span-4 col-span-12">
                                                            <div class="rounded-md p-4 bg-light">
                                                                <div class="flex items-center gap-3">
                                                                    <div class="leading-none">
                                                                        <span class="avatar bg-danger/10 !text-danger">
                                                                            <i class="ti ti-arrow-narrow-up text-[1.25rem]"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50">Sent</span>
                                                                        <span class="block font-semibold">2.7382 <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50 font-normal">BTC</span></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="xl:col-span-4 col-span-12">
                                                            <div class="rounded-md p-4 bg-light">
                                                                <div class="flex items-center gap-3">
                                                                    <div class="leading-none">
                                                                        <span class="avatar bg-secondary/10 !text-secondary">
                                                                            <i class="ti ti-wallet text-[1.25rem]"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50">Wallet Balance</span>
                                                                        <span class="block font-semibold">12.5232 <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50 font-normal">BTC</span></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="box custom-box">
                                                <div class="box-header justify-between">
                                                    <div class="box-title">
                                                        ETH Wallet Address
                                                    </div>
                                                    <div>
                                                        <button type="button" class="ti-btn ti-btn-outline-primary btn-wave">Connect</button>
                                                    </div>
                                                </div>
                                                <div class="box-body">
                                                    <div class="flex items-center flex-wrap justify-between gap-4 mb-4">
                                                      <div class="flex-grow">
                                                          <label for="btc-wallet-address2" class="form-label">Wallet Address</label>
                                                          <div class="input-group">
                                                                <input type="text" class="form-control !border-s" id="btc-wallet-address2" value="afb0dc8bc84625587b85415c86ef43ed8df" placeholder="Placeholder">
                                                                <button type="button" class="ti-btn btn-wave ti-btn-primary-full !mb-0">Copy</button>
                                                            </div>
                                                      </div>
                                                      <div>
                                                            <span class="avatar avatar-xxl border dark:border-defaultborder/10">
                                                                <img src="{{asset('build/assets/images/media/media-89.png')}}" class="p-1 qr-image" alt="">
                                                            </span>
                                                      </div>
                                                    </div>
                                                    <div class="grid grid-cols-12 gap-4">
                                                        <div class="xl:col-span-4 col-span-12">
                                                            <div class="rounded-md p-4 bg-light">
                                                                <div class="flex items-center gap-4">
                                                                    <div class="leading-none">
                                                                        <span class="avatar bg-success/10 ">
                                                                            <i class="ti ti-arrow-narrow-down text-[1.25rem] !text-success"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50">Received</span>
                                                                        <span class="block font-semibold">6.2834 <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50 font-normal">ETH</span></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="xl:col-span-4 col-span-12">
                                                            <div class="rounded-md p-4 bg-light">
                                                                <div class="flex items-center gap-4">
                                                                    <div class="leading-none">
                                                                        <span class="avatar !bg-danger/10 ">
                                                                            <i class="ti ti-arrow-narrow-up text-[1.25rem] !text-danger"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50">Sent</span>
                                                                        <span class="block font-semibold">2.7382 <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50 font-normal">ETH</span></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="xl:col-span-4 col-span-12">
                                                            <div class="rounded-md p-4 bg-light">
                                                                <div class="flex items-center gap-4">
                                                                    <div class="leading-none">
                                                                        <span class="avatar !bg-secondary/10 ">
                                                                            <i class="ti ti-wallet text-[1.25rem] !text-secondary"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50">Wallet Balance</span>
                                                                        <span class="block font-semibold">12.5232 <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50 font-normal">ETH</span></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="box custom-box">
                                                <div class="box-header justify-between">
                                                    <div class="box-title">
                                                        LTC Wallet Address
                                                    </div>
                                                    <div>
                                                        <button type="button" class="ti-btn ti-btn-outline-primary btn-wave">Connect</button>
                                                    </div>
                                                </div>
                                                <div class="box-body">
                                                    <div class="flex items-center flex-wrap justify-between gap-4 mb-4">
                                                      <div class="flex-grow">
                                                          <label for="btc-wallet-address3" class="form-label">Wallet Address</label>
                                                          <div class="input-group">
                                                                <input type="text" class="form-control !border-s" id="btc-wallet-address3" value="afb0dc8bc84625587b85415c86ef43ed8df" placeholder="Placeholder">
                                                                <button type="button" class="ti-btn btn-wave ti-btn-primary-full !mb-0">Copy</button>
                                                            </div>
                                                      </div>
                                                      <div>
                                                            <span class="avatar avatar-xxl border dark:border-defaultborder/10">
                                                                <img src="{{asset('build/assets/images/media/media-89.png')}}" class="p-1 qr-image" alt="">
                                                            </span>
                                                      </div>
                                                    </div>
                                                    <div class="grid grid-cols-12 gap-4">
                                                        <div class="xl:col-span-4 col-span-12">
                                                            <div class="rounded-md p-4 bg-light">
                                                                <div class="flex items-center gap-4">
                                                                    <div class="leading-none">
                                                                        <span class="avatar bg-success/10 !text-success">
                                                                            <i class="ti ti-arrow-narrow-down text-[1.25rem]"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50">Received</span>
                                                                        <span class="block font-semibold">6.2834 <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50 font-normal">LTC</span></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="xl:col-span-4 col-span-12">
                                                            <div class="rounded-md p-4 bg-light">
                                                                <div class="flex items-center gap-4">
                                                                    <div class="leading-none">
                                                                        <span class="avatar bg-danger/10 !text-danger">
                                                                            <i class="ti ti-arrow-narrow-up text-[1.25rem]"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50">Sent</span>
                                                                        <span class="block font-semibold">2.7382 <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50 font-normal">LTC</span></span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="xl:col-span-4 col-span-12">
                                                            <div class="rounded-md p-4 bg-light">
                                                                <div class="flex items-center gap-4">
                                                                    <div class="leading-none">
                                                                        <span class="avatar bg-secondary/10 !text-secondary">
                                                                            <i class="ti ti-wallet text-[1.25rem]"></i>
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block text-[#8c9097] dark:text-white/50">Wallet Balance</span>
                                                                        <span class="block font-semibold">12.5232 <span class="text-[0.75rem] text-[#8c9097] dark:text-white/50 font-normal">LTC</span></span>
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
                            <!--End::row-1 -->
                            
                        </div>

@endsection

@section('scripts')


@endsection
