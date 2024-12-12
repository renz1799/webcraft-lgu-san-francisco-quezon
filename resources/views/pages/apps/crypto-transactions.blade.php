@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')
 
                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Transactions</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Crypto
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                  Transactions
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-9 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Transaction History
                                        </div>
                                        <div class="flex flex-wrap gap-2">
                                            <div>
                                                <input class="form-control form-control-sm" type="text" placeholder="Search Here" aria-label=".form-control-sm example">
                                            </div>
                                            <div class="ti-dropdown hs-dropdown">
                                                <a href="javascript:void(0);" class="ti-btn btn-wave ti-btn-primary-full !py-1 !px-2 !text-[0.75rem]" aria-expanded="false">
                                                    Sort By<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                                </a>
                                                <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">New</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">This Week</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">This Month</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">This Year</a></li>
                                                </ul>
                                            </div>
                                            <div>
                                                <button type="button" class="ti-btn btn-wave ti-btn-secondary-full !py-1 !px-2 !text-[0.75rem]">View All</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="table-responsive">
                                            <table class="table whitespace-nowrap table-bordered min-w-full">
                                                <thead>
                                                    <tr>
                                                        <th scope="col"></th>
                                                        <th scope="col" class="text-start">Sender</th>
                                                        <th scope="col" class="text-start">Transaction Hash</th>
                                                        <th scope="col" class="text-start">Coin</th>
                                                        <th scope="col" class="text-start">Date</th>
                                                        <th scope="col" class="text-start">Amount</th>
                                                        <th scope="col" class="text-start">Receiver</th>
                                                        <th scope="col" class="text-start">Status</th>
                                                        <th scope="col" class="text-start">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border border-defaultborder transaction">
                                                        <td>
                                                            <span class="avatar avatar-sm avatar-rounded bg-light">
                                                                <i class="ti ti-arrow-narrow-up text-success font-semibold text-[1rem]"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Json Taylor</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>#1242232401</span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Bitcoin.svg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Bitcoin</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>24,Jul 2023 - 4:23PM</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success">+0.041</span>
                                                        </td>
                                                        <td>
                                                            <span>Texas Steel</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success/10 text-success">Success</span>
                                                        </td>
                                                        <td>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                  <span><i class="ri-download-2-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Download
                                                                  </span>
                                                              </button>
                                                          </div>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm transaction-delete-btn">
                                                                  <span><i class="ri-delete-bin-5-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Delete
                                                                  </span>
                                                              </button>
                                                          </div>
                                                      </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder transaction">
                                                        <td>
                                                            <span class="avatar avatar-sm avatar-rounded bg-light">
                                                                <i class="ti ti-arrow-narrow-down text-danger font-semibold text-[1rem]"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Samantha Taylor</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>#1242232402</span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Dash.svg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Dashcoin</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>20,Jul 2023 - 12:47PM</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger">-0.284</span>
                                                        </td>
                                                        <td>
                                                            <span>Stuart Little</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-warning/10 text-warning">Pending</span>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <span><i class="ri-download-2-line"></i></span>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                        Download
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm transaction-delete-btn">
                                                                    <span><i class="ri-delete-bin-5-line"></i></span>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                        Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder transaction">
                                                        <td>
                                                            <span class="avatar avatar-sm avatar-rounded bg-light">
                                                                <i class="ti ti-arrow-narrow-up text-success font-semibold text-[1rem]"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Brian Jhonson</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>#1242232403</span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Euro.svg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Euro</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>14,Aug 2023 - 10:25AM</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success">+0.943</span>
                                                        </td>
                                                        <td>
                                                            <span>Melissa Smith</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success/10 text-success">Success</span>
                                                        </td>
                                                        <td>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                  <span><i class="ri-download-2-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Download
                                                                  </span>
                                                              </button>
                                                          </div>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm transaction-delete-btn">
                                                                  <span><i class="ri-delete-bin-5-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Delete
                                                                  </span>
                                                              </button>
                                                          </div>
                                                      </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder transaction">
                                                        <td>
                                                            <span class="avatar avatar-sm avatar-rounded bg-light">
                                                                <i class="ti ti-arrow-narrow-up text-success font-semibold text-[1rem]"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Liam Anderson</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>#1242232404</span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Ethereum.svg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Etherium</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>10,Jul 2023 - 4:14PM</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success">+0.582</span>
                                                        </td>
                                                        <td>
                                                            <span>Alexander Clark</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-danger/10 text-danger">Failed</span>
                                                        </td>
                                                        <td>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                  <span><i class="ri-download-2-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Download
                                                                  </span>
                                                              </button>
                                                          </div>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm transaction-delete-btn">
                                                                  <span><i class="ri-delete-bin-5-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Delete
                                                                  </span>
                                                              </button>
                                                          </div>
                                                      </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder transaction">
                                                        <td>
                                                            <span class="avatar avatar-sm avatar-rounded bg-light">
                                                                <i class="ti ti-arrow-narrow-up text-success font-semibold text-[1rem]"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Isabella Brown</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>#1242232405</span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Golem.svg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Golem</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>19,Aug 2023 - 11:35AM</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success">+0.290</span>
                                                        </td>
                                                        <td>
                                                            <span>Elijah Davis</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success/10 text-success">Success</span>
                                                        </td>
                                                        <td>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                  <span><i class="ri-download-2-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Download
                                                                  </span>
                                                              </button>
                                                          </div>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm transaction-delete-btn">
                                                                  <span><i class="ri-delete-bin-5-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Delete
                                                                  </span>
                                                              </button>
                                                          </div>
                                                      </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder transaction">
                                                        <td>
                                                            <span class="avatar avatar-sm avatar-rounded bg-light">
                                                                <i class="ti ti-arrow-narrow-down text-danger font-semibold text-[1rem]"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Sophia Lee</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>#1242232406</span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/litecoin.svg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Litecoin</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>12,Jun 2023 - 2:45PM</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger">-0.147</span>
                                                        </td>
                                                        <td>
                                                            <span>Harper Taylor</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success/10 text-success">Success</span>
                                                        </td>
                                                        <td>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                  <span><i class="ri-download-2-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Download
                                                                  </span>
                                                              </button>
                                                          </div>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm transaction-delete-btn">
                                                                  <span><i class="ri-delete-bin-5-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Delete
                                                                  </span>
                                                              </button>
                                                          </div>
                                                      </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder transaction">
                                                        <td>
                                                            <span class="avatar avatar-sm avatar-rounded bg-light">
                                                                <i class="ti ti-arrow-narrow-up text-success font-semibold text-[1rem]"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Evelyn Clark</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>#1242232407</span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Ripple.svg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Ripple</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>27,Jul 2023 - 10:18AM</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success">+1.05</span>
                                                        </td>
                                                        <td>
                                                            <span>William Brown</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success/10 text-success">Success</span>
                                                        </td>
                                                        <td>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                  <span><i class="ri-download-2-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Download
                                                                  </span>
                                                              </button>
                                                          </div>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm transaction-delete-btn">
                                                                  <span><i class="ri-delete-bin-5-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Delete
                                                                  </span>
                                                              </button>
                                                          </div>
                                                      </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder transaction">
                                                        <td>
                                                            <span class="avatar avatar-sm avatar-rounded bg-light">
                                                                <i class="ti ti-arrow-narrow-up text-success font-semibold text-[1rem]"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Liam Anderson</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>#1242232408</span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/monero.svg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Monero</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>16,Aug 2023 - 9:25PM</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success">+0.625</span>
                                                        </td>
                                                        <td>
                                                            <span>Amelia Davis</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info/10 text-info">Inprogress</span>
                                                        </td>
                                                        <td>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                  <span><i class="ri-download-2-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Download
                                                                  </span>
                                                              </button>
                                                          </div>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm transaction-delete-btn">
                                                                  <span><i class="ri-delete-bin-5-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Delete
                                                                  </span>
                                                              </button>
                                                          </div>
                                                      </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder transaction">
                                                        <td>
                                                            <span class="avatar avatar-sm avatar-rounded bg-light">
                                                                <i class="ti ti-arrow-narrow-down text-danger font-semibold text-[1rem]"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Harper Taylor</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>#1242232409</span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Zcash.svg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Zcash</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>24,Jul 2023 - 12:47PM</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-danger">-0.293</span>
                                                        </td>
                                                        <td>
                                                            <span>Benjamin Martinez</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-info/10 text-info">Inprogress</span>
                                                        </td>
                                                        <td>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                  <span><i class="ri-download-2-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Download
                                                                  </span>
                                                              </button>
                                                          </div>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm transaction-delete-btn">
                                                                  <span><i class="ri-delete-bin-5-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Delete
                                                                  </span>
                                                              </button>
                                                          </div>
                                                      </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder transaction">
                                                        <td>
                                                            <span class="avatar avatar-sm avatar-rounded bg-light">
                                                                <i class="ti ti-arrow-narrow-up text-success font-semibold text-[1rem]"></i>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/16.jpg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Lucas Taylor</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>#1242232410</span>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/crypto-currencies/regular/Decred.svg')}}" alt="">
                                                                </span>
                                                                <div class="font-semibold">Decred</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span>24,Jul 2023 - 12:47PM</span>
                                                        </td>
                                                        <td>
                                                            <span class="text-success">+0.893</span>
                                                        </td>
                                                        <td>
                                                            <span>Mia Wilson</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success/10 text-success">Success</span>
                                                        </td>
                                                        <td>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                  <span><i class="ri-download-2-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Download
                                                                  </span>
                                                              </button>
                                                          </div>
                                                          <div class="hs-tooltip ti-main-tooltip">
                                                              <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm transaction-delete-btn">
                                                                  <span><i class="ri-delete-bin-5-line"></i></span>
                                                                  <span
                                                                      class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                      role="tooltip">
                                                                      Delete
                                                                  </span>
                                                              </button>
                                                          </div>
                                                      </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <nav aria-label="Page navigation">
                                            <ul class="ti-pagination sm:ltr:float-right sm:rtl:float-left justify-center mb-4">
                                                <li class="page-item disabled"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">Previous</a></li>
                                                <li class="page-item"><a class="page-link px-3 py-[0.375rem] active" href="javascript:void(0);">1</a></li>
                                                <li class="page-item"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">2</a></li>
                                                <li class="page-item"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">3</a></li>
                                                <li class="page-item"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">Next</a></li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-3 col-span-12">
                                <div class="box custom-box">
                                    <div class="box custom-box">
                                        <div class="box-body !p-0">
                                            <div class="p-6 border-b dark:border-defaultborder/10 border-dashed flex items-start flex-wrap gap-2">
                                                <div class="svg-icon-background bg-primary/10 fill-primary me-4">
                                                    <svg xmlns="http://www.w3.org/2000/svg" data-name="Layer 1" viewBox="0 0 24 24" class="svg-primary"><path d="M13,16H7a1,1,0,0,0,0,2h6a1,1,0,0,0,0-2ZM9,10h2a1,1,0,0,0,0-2H9a1,1,0,0,0,0,2Zm12,2H18V3a1,1,0,0,0-.5-.87,1,1,0,0,0-1,0l-3,1.72-3-1.72a1,1,0,0,0-1,0l-3,1.72-3-1.72a1,1,0,0,0-1,0A1,1,0,0,0,2,3V19a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V13A1,1,0,0,0,21,12ZM5,20a1,1,0,0,1-1-1V4.73L6,5.87a1.08,1.08,0,0,0,1,0l3-1.72,3,1.72a1.08,1.08,0,0,0,1,0l2-1.14V19a3,3,0,0,0,.18,1Zm15-1a1,1,0,0,1-2,0V14h2Zm-7-7H7a1,1,0,0,0,0,2h6a1,1,0,0,0,0-2Z"/></svg>
                                                </div>
                                                <div class="flex-grow">
                                                    <h6 class="mb-2 text-[0.75rem]">New Transactions
                                                        <span class="badge bg-primary text-white font-semibold ltr:float-right rtl:float-left">
                                                          12,345
                                                        </span>
                                                    </h6>
                                                    <div class="pb-0 mt-0">
                                                        <div>
                                                            <h4 class="text-[1.125rem] font-semibold mb-2"><span class="count-up" data-count="42">42</span><span class="text-[#8c9097] dark:text-white/50 ltr:float-right rtl:float-left text-[.6875rem] font-normal">Last Year</span></h4>
                                                            <p class="text-[#8c9097] dark:text-white/50 text-[.6875rem] mb-0 leading-none">
                                                                <span class="text-success me-1 font-semibold">
                                                                    <i class="ri-arrow-up-s-line me-1 align-middle"></i>3.25%
                                                                </span>
                                                                <span>this month</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-4 border-b dark:border-defaultborder/10 border-dashed flex items-start flex-wrap gap-2">
                                                <div class="svg-icon-background bg-success/10 fill-success me-4">
                                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="svg-success"><path d="M11.5,20h-6a1,1,0,0,1-1-1V5a1,1,0,0,1,1-1h5V7a3,3,0,0,0,3,3h3v5a1,1,0,0,0,2,0V9s0,0,0-.06a1.31,1.31,0,0,0-.06-.27l0-.09a1.07,1.07,0,0,0-.19-.28h0l-6-6h0a1.07,1.07,0,0,0-.28-.19.29.29,0,0,0-.1,0A1.1,1.1,0,0,0,11.56,2H5.5a3,3,0,0,0-3,3V19a3,3,0,0,0,3,3h6a1,1,0,0,0,0-2Zm1-14.59L15.09,8H13.5a1,1,0,0,1-1-1ZM7.5,14h6a1,1,0,0,0,0-2h-6a1,1,0,0,0,0,2Zm4,2h-4a1,1,0,0,0,0,2h4a1,1,0,0,0,0-2Zm-4-6h1a1,1,0,0,0,0-2h-1a1,1,0,0,0,0,2Zm13.71,6.29a1,1,0,0,0-1.42,0l-3.29,3.3-1.29-1.3a1,1,0,0,0-1.42,1.42l2,2a1,1,0,0,0,1.42,0l4-4A1,1,0,0,0,21.21,16.29Z"/></svg>
                                                </div>
                                                <div class="flex-grow">
                                                    <h6 class="mb-2 text-[0.75rem]">Completed Transactions
                                                        <span class="badge bg-success text-white font-semibold ltr:float-right rtl:float-left">
                                                            4,176
                                                        </span>
                                                    </h6>
                                                    <div>
                                                        <h4 class="text-[1.125rem] font-semibold mb-2"><span class="count-up" data-count="319">320</span><span class="text-[#8c9097] dark:text-white/50 ltr:float-right rtl:float-left text-[.6875rem] font-normal">Last Year</span></h4>
                                                        <p class="text-[#8c9097] dark:text-white/50 text-[.6875rem] mb-0 leading-none">
                                                            <span class="text-danger me-1 font-semibold">
                                                                <i class="ri-arrow-down-s-line me-1 align-middle"></i>1.16%
                                                            </span>
                                                            <span>this month</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-start p-4 border-b dark:border-defaultborder/10 border-dashed flex-wrap gap-2">
                                                <div class="svg-icon-background bg-warning/10 !fill-warning me-4">
                                                    <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" class="svg-warning"><path d="M19,12h-7V5c0-0.6-0.4-1-1-1c-5,0-9,4-9,9s4,9,9,9s9-4,9-9C20,12.4,19.6,12,19,12z M12,19.9c-3.8,0.6-7.4-2.1-7.9-5.9C3.5,10.2,6.2,6.6,10,6.1V13c0,0.6,0.4,1,1,1h6.9C17.5,17.1,15.1,19.5,12,19.9z M15,2c-0.6,0-1,0.4-1,1v6c0,0.6,0.4,1,1,1h6c0.6,0,1-0.4,1-1C22,5.1,18.9,2,15,2z M16,8V4.1C18,4.5,19.5,6,19.9,8H16z"/></svg>
                                                </div>
                                                <div class="flex-grow">
                                                    <h6 class="mb-2 text-[0.75rem]">Pending Transactions
                                                        <span class="badge bg-warning text-white font-semibold ltr:float-right rtl:float-left">
                                                            7,064
                                                        </span>
                                                    </h6>
                                                    <div>
                                                        <h4 class="text-[1.125rem] font-semibold mb-2"><span class="count-up" data-count="81">82</span><span class="text-[#8c9097] dark:text-white/50 ltr:float-right rtl:float-left text-[.6875rem] font-normal">Last Year</span></h4>
                                                        <p class="text-[#8c9097] dark:text-white/50 text-[.6875rem] mb-0 leading-none">
                                                            <span class="text-success me-1 font-semibold">
                                                                <i class="ri-arrow-up-s-line me-1 align-middle"></i>0.25%
                                                            </span>
                                                            <span>this month</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-start p-4 border-b dark:border-defaultborder/10 border-dashed flex-wrap gap-2">
                                                <div class="svg-icon-background bg-secondary/10 fill-secondary me-4">
                                                    <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" class="svg-secondary"><path d="M19,12h-7V5c0-0.6-0.4-1-1-1c-5,0-9,4-9,9s4,9,9,9s9-4,9-9C20,12.4,19.6,12,19,12z M12,19.9c-3.8,0.6-7.4-2.1-7.9-5.9C3.5,10.2,6.2,6.6,10,6.1V13c0,0.6,0.4,1,1,1h6.9C17.5,17.1,15.1,19.5,12,19.9z M15,2c-0.6,0-1,0.4-1,1v6c0,0.6,0.4,1,1,1h6c0.6,0,1-0.4,1-1C22,5.1,18.9,2,15,2z M16,8V4.1C18,4.5,19.5,6,19.9,8H16z"/></svg>
                                                </div>
                                                <div class="flex-grow">
                                                    <h6 class="mb-2 text-[0.75rem]">Inprogress Transactions
                                                        <span class="badge bg-secondary text-white font-semibold ltr:float-right rtl:float-left">
                                                            1,105
                                                        </span>
                                                    </h6>
                                                    <div>
                                                        <h4 class="text-[1.125rem] font-semibold mb-2"><span class="count-up" data-count="32">33</span><span class="text-[#8c9097] dark:text-white/50 ltr:float-right rtl:float-left text-[.6875rem] font-normal">Last Year</span></h4>
                                                        <p class="text-[#8c9097] dark:text-white/50 text-[.6875rem] mb-0 leading-none">
                                                            <span class="text-success me-1 font-semibFold">
                                                                <i class="ri-arrow-down-s-line me-1 align-middle"></i>0.46%
                                                            </span>
                                                            <span>this month</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="p-6 pb-2">
                                                <p class="text-[.9375rem] font-semibold">Transactions Statistics <span class="text-[#8c9097] dark:text-white/50 font-normal">(Last 6 months) :</span></p>
                                                <div id="transactions"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End::row-1 -->

@endsection

@section('scripts')

        <!-- Apex Charts JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- Internal Invoice List JS -->
        @vite('resources/assets/js/crypto-transactions-list.js')

        
@endsection
