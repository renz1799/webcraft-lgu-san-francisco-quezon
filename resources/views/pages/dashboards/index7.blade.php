@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')
 
                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Analytics</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                Dashboards
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                Analytics
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->
                    
                    <div class="grid grid-cols-12 gap-x-6">
                        <div class="xl:col-span-7 col-span-12">
                            <div class="grid grid-cols-12 gap-x-6">
                            <div class="xl:col-span-4 lg:col-span-4 md:col-span-4 sm:col-span-6 col-span-12">
                                <div class="box">
                                    <div class="box-body">
                                        <div class="flex flex-wrap items-center justify-between">
                                            <div>
                                                <h6 class="font-semibold mb-3 text-[1rem]">Total Users</h6>
                                                <span class="text-[1.5625rem] font-semibold">9,789</span>
                                                <span class="block text-success text-[0.75rem]">+0.892 <i class="ti ti-trending-up ms-1"></i></span>
                                            </div>
                                            <div id="analytics-users"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-4 md:col-span-4 sm:col-span-6 col-span-12">
                                <div class="box">
                                    <div class="box-body">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h6 class="font-semibold mb-3 text-[1rem]">Live Visitors</h6>
                                                <span class="text-[1.5625rem] font-semibold">12,240</span>
                                                <span class="block text-danger text-[0.75rem]">+0.59<i class="ti ti-trending-down ms-1 inline-flex"></i></span>
                                            </div>
                                            <div>
                                                <span class="avatar avatar-md bg-secondary text-white">
                                                    <i class="ri-user-3-line"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-4 md:col-span-4 sm:col-span-6 col-span-12">
                                <div class="box overflow-hidden">
                                    <div class="box-body mb-3">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h6 class="font-semibold text-primary mb-4 text-[1rem]">Bounce Rate</h6>
                                                <span class="text-[1.5625rem] flex items-center">77.3% <span class=" text-[0.75rem] text-warning opacity-[0.7] ms-2">+0.59<i class="ti ti-arrow-big-up-line ms-1 inline-flex"></i></span></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="analytics-bouncerate" class="mt-1 w-full"></div>
                                </div>
                            </div>
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Audience Report
                                        </div>
                                        <div>
                                            <button type="button" class="ti-btn btn-wave ti-btn-primary btn-wave !font-medium"><i class="ri-share-forward-line me-1 align-middle inline-block"></i>Export</button>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div id="audienceReport"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-6 xl:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Top Countries Sessions vs Bounce Rate
                                        </div>
                                        <div class="hs-dropdown ti-dropdown">
                                            <a href="javascript:void(0);" class="px-2 font-normal text-[0.75rem] text-[#8c9097] dark:text-white/50"
                                              aria-expanded="false">
                                              View All<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                  href="javascript:void(0);">Today</a></li>
                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                  href="javascript:void(0);">This Week</a></li>
                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                  href="javascript:void(0);">Last Week</a></li>
                                            </ul>
                                          </div>
                                    </div>
                                    <div class="box-body">
                                        <div id="country-sessions"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-6 xl:col-span-12 col-span-12">
                                <div class="box overflow-hidden">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Traffic Sources
                                        </div>
                                        <div class="hs-dropdown ti-dropdown">
                                            <a href="javascript:void(0);" class="px-2 font-normal text-[0.75rem] text-[#8c9097] dark:text-white/50"
                                              aria-expanded="false">
                                              View All<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                  href="javascript:void(0);">Today</a></li>
                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                  href="javascript:void(0);">This Week</a></li>
                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                  href="javascript:void(0);">Last Week</a></li>
                                            </ul>
                                          </div>
                                    </div>
                                    <div class="box-body !p-0">
                                        <div class="table-responsive">
                                            <table class="table table-hover whitespace-nowrap min-w-full">
                                                <thead>
                                                    <tr>
                                                        <th scope="col" class="text-start">Browser</th>
                                                        <th scope="col" class="text-start">Sessions</th>
                                                        <th scope="col" class="text-start">Traffic</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                        <td>
                                                            <div class="flex items-center">
                                                                <span class="avatar avatar-rounded avatar-sm p-2 bg-light me-2">
                                                                    <i class="ri-google-fill text-[1.125rem] text-primary"></i>
                                                                </span>
                                                                <div class="font-semibold">Google</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span><i class="ri-arrow-up-s-fill me-1 text-success align-middle text-[1.125rem]"></i>23,379</span>
                                                        </td>
                                                        <td>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar bg-primary w-[78%]" >
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                        <td>
                                                            <div class="flex items-center">
                                                                <span class="avatar avatar-rounded avatar-sm p-2 bg-light me-2">
                                                                    <i class="ri-safari-line text-[1.125rem] text-secondary"></i>
                                                                </span>
                                                                <div class="font-semibold">Safari</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span><i class="ri-arrow-up-s-fill me-1 text-success align-middle text-[1.125rem]"></i>78,973</span>
                                                        </td>
                                                        <td>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar bg-primary w-[32%]" >
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                        <td>
                                                            <div class="flex items-center">
                                                                <span class="avatar avatar-rounded avatar-sm p-2 bg-light me-2">
                                                                    <i class="ri-opera-fill text-[1.125rem] text-success"></i>
                                                                </span>
                                                                <div class="font-semibold">Opera</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span><i class="ri-arrow-down-s-fill me-1 text-danger align-middle text-[1.125rem]"></i>12,457</span>
                                                        </td>
                                                        <td>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar bg-primary w-[21%]" >
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                        <td>
                                                            <div class="flex items-center">
                                                                <span class="avatar avatar-rounded avatar-sm p-2 bg-light me-2">
                                                                    <i class="ri-edge-fill text-[1.125rem] text-info"></i>
                                                                </span>
                                                                <div class="font-semibold">Edge</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span><i class="ri-arrow-up-s-fill me-1 text-success align-middle text-[1.125rem]"></i>8,570</span>
                                                        </td>
                                                        <td>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar bg-primary w-1/4" >
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                        <td>
                                                            <div class="flex items-center">
                                                                <span class="avatar avatar-rounded avatar-sm p-2 bg-light me-2">
                                                                    <i class="ri-firefox-fill text-[1.125rem] text-warning"></i>
                                                                </span>
                                                                <div class="font-semibold">Firefox</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span><i class="ri-arrow-down-s-fill me-1 text-danger align-middle text-[1.125rem]"></i>6,135</span>
                                                        </td>
                                                        <td>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar bg-primary w-[35%]" >
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                        <td class="border-bottom-0">
                                                            <div class="flex items-center">
                                                                <span class="avatar avatar-rounded avatar-sm p-2 bg-light me-2">
                                                                    <i class="ri-ubuntu-fill text-[1.125rem] text-danger"></i>
                                                                </span>
                                                                <div class="font-semibold">Ubuntu</div>
                                                            </div>
                                                        </td>
                                                        <td class="border-bottom-0">
                                                            <span><i class="ri-arrow-up-s-fill me-1 text-success align-middle text-[1.125rem]"></i>4,789</span>
                                                        </td>
                                                        <td class="border-bottom-0">
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar bg-primary w-[12%]" >
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                          </div>
                        </div>
                        <div class="xl:col-span-5 col-span-12">
                            <div class="grid grid-cols-12 gap-x-6">
                                <div class="xxl:col-span-5 cxl:ol-span-12 col-span-12">
                                    <div class="box custom-card upgrade-card text-white">
                                        <div class="box-body text-white">
                                            <span class="avatar avatar-xxl !border-0">
                                                <img src="{{asset('build/assets/images/media/media-84.png')}}" alt="">
                                            </span>
                                            <div class="upgrade-card-content">
                                                <span class="opacity-[0.7] font-normal mb-1 !text-white">Plan is expiring !</span>
                                                <span class="text-[0.9375rem] font-semibold block mb-[3rem] upgrade-text !text-white">Upgrade to premium</span>
                                                <button type="button" class="ti-btn !py-1 !px-2 bg-light text-defaulttextcolor !text-[0.75rem] font-medium btn-wave">Upgrade Now</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box">
                                        <div class="box-body !p-1">
                                            <div class="flex items-center flex-wrap">
                                                <div id="analytics-followers"></div>
                                                <div class="ms-1">
                                                    <p class="mb-1 text-[#8c9097] dark:text-white/50">Impressions</p>
                                                    <h5 class="font-semibold mb-0 text-[1.25rem]">9,903</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box">
                                        <div class="box-body !p-1">
                                            <div class="flex items-center flex-wrap">
                                                <div id="analytics-views"></div>
                                                <div class="ms-1">
                                                    <p class="mb-1 text-[#8c9097] dark:text-white/50">Clicks</p>
                                                    <h5 class="font-semibold mb-0 text-[1.25rem]">16,789</h5>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="xxl:col-span-7 xl:col-span-12 col-span-12">
                                    <div class="box">
                                        <div class="box-header justify-between">
                                            <div class="box-title">
                                                Sessions By Device
                                            </div>
                                            <div>
                                                <button type="button" class="ti-btn btn-wave ti-btn-primary 1 !text-[0.85rem] !m-0 !font-medium">View All</button>
                                            </div>
                                        </div>
                                        <div class="box-body !my-2 !py-6 !px-2">
                                            <div id="sessions"></div>
                                        </div>
                                        <div class="box-footer !p-0">
                                            <div class="grid grid-cols-12 justify-center">
                                                <div class="col-span-3 pe-0 text-center">
                                                    <div class="sm:p-4  p-2 ">
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Mobile</span>
                                                        <span class="block text-[1rem] font-semibold">68.3%</span>
                                                    </div>
                                                </div>
                                                <div class="col-span-3 px-0 text-center">
                                                    <div class="sm:p-4 p-2">
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Tablet</span>
                                                        <span class="block text-[1rem] font-semibold">17.68%</span>
                                                    </div>
                                                </div>
                                                <div class="col-span-3 px-0 text-center">
                                                    <div class="sm:p-4 p-2 ">
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Desktop</span>
                                                        <span class="block text-[1rem] font-semibold">10.5%</span>
                                                    </div>
                                                </div>
                                                <div class="col-span-3 px-0 text-center">
                                                    <div class="sm:p-4 p-2">
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Others</span>
                                                        <span class="block text-[1rem] font-semibold">5.16%</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="xl:col-span-12 col-span-12">
                                    <div class="box">
                                        <div class="box-header justify-between">
                                            <div class="box-title">Sessions Duration By New Users</div>
                                            <div class="hs-dropdown ti-dropdown">
                                                <a href="javascript:void(0);" class="px-2 font-normal text-[0.75rem] text-[#8c9097] dark:text-white/50"
                                                  aria-expanded="false">
                                                  View All<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                                </a>
                                                <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                                  <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                      href="javascript:void(0);">Today</a></li>
                                                  <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                      href="javascript:void(0);">This Week</a></li>
                                                  <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                      href="javascript:void(0);">Last Week</a></li>
                                                </ul>
                                              </div>
                                        </div>
                                        <div class="box-body">
                                            <div id="session-users"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-x-6">
                        <div class="xl:col-span-9 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Visitors By Channel Report
                                    </div>
                                    <div class="flex flex-wrap">
                                        <div class="me-3 my-1">
                                            <input class="ti-form-control form-control-sm" type="text" placeholder="Search Here" aria-label=".form-control-sm example">
                                        </div>
                                        <div class="hs-dropdown ti-dropdown my-1">
                                            <a href="javascript:void(0);"
                                              class="ti-btn btn-wave ti-btn-primary !bg-primary !text-white !py-1 !px-2 !text-[0.75rem] !m-0 !gap-0 !font-medium"
                                              aria-expanded="false">
                                              Sort By<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                  href="javascript:void(0);">New</a></li>
                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                  href="javascript:void(0);">Popular</a></li>
                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                  href="javascript:void(0);">Relevant</a></li>
                                            </ul>
                                          </div>
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover whitespace-nowrap table-bordered min-w-full">
                                            <thead>
                                                <tr>
                                                    <th scope="col" class="text-start">S.No</th>
                                                    <th scope="col" class="text-start">Channel</th>
                                                    <th scope="col" class="text-start">Sessions</th>
                                                    <th scope="col" class="text-start">Bounce Rate</th>
                                                    <th scope="col" class="text-start">Avg Session Duration</th>
                                                    <th scope="col" class="text-start">Goal Completed</th>
                                                    <th scope="col" class="text-start">Pages Per Session</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        1
                                                    </th>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <span class="avatar avatar-sm !mb-0 bg-primary/10 avatar-rounded">
                                                                <i class="ri-search-2-line text-[0.9375rem] font-semibiold text-primary"></i>
                                                            </span>
                                                            <span class="ms-2">
                                                                Organic Search
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>782</td>
                                                    <td>32.09%</td>
                                                    <td>
                                                        0 hrs : 0 mins : 32 secs
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary/10 text-primary">278</span>
                                                    </td>
                                                    <td>
                                                        2.9
                                                    </td>
                                                </tr>
                                                <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        2
                                                    </th>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <span class="avatar avatar-sm !mb-0 bg-secondary/10 avatar-rounded">
                                                                <i class="ri-globe-line text-[0.9375rem] font-semibiold text-secondary"></i>
                                                            </span>
                                                            <span class="ms-2">
                                                                Direct
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>882</td>
                                                    <td>39.38%</td>
                                                    <td>
                                                        0 hrs : 2 mins : 45 secs
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary/10 text-secondary">782</span>
                                                    </td>
                                                    <td>
                                                        1.5
                                                    </td>
                                                </tr>
                                                <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        3
                                                    </th>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <span class="avatar avatar-sm !mb-0 bg-success/10 avatar-rounded">
                                                                <i class="ri-share-forward-line text-[0.9375rem] font-semibiold text-success"></i>
                                                            </span>
                                                            <span class="ms-2">
                                                                Referral
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>322</td>
                                                    <td>22.67%</td>
                                                    <td>
                                                        0 hrs : 38 mins : 28 secs
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success/10 text-success">622</span>
                                                    </td>
                                                    <td>
                                                        3.2
                                                    </td>
                                                </tr>
                                                <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        4
                                                    </th>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <span class="avatar avatar-sm !mb-0 bg-info/10 avatar-rounded">
                                                                <i class="ri-reactjs-line text-[0.9375rem] font-semibiold text-info"></i>
                                                            </span>
                                                            <span class="ms-2">
                                                                Social
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>389</td>
                                                    <td>25.11%</td>
                                                    <td>
                                                        0 hrs : 12 mins : 89 secs
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-info/10 text-info">142</span>
                                                    </td>
                                                    <td>
                                                        1.4
                                                    </td>
                                                </tr>
                                                <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        5
                                                    </th>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <span class="avatar avatar-sm !mb-0 bg-warning/10 avatar-rounded">
                                                                <i class="ri-mail-line text-[0.9375rem] font-semibiold text-warning"></i>
                                                            </span>
                                                            <span class="ms-2">
                                                                Email
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>378</td>
                                                    <td>23.79%</td>
                                                    <td>
                                                        0 hrs : 14 mins : 27 secs
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning/10 text-warning">178</span>
                                                    </td>
                                                    <td>
                                                        1.6
                                                    </td>
                                                </tr>
                                                <tr class="border-t border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        6
                                                    </th>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <span class="avatar avatar-sm !mb-0 bg-danger/10 avatar-rounded">
                                                                <i class="ri-bank-card-line text-[0.9375rem] font-semibiold text-danger"></i>
                                                            </span>
                                                            <span class="ms-2">
                                                                Paid Search
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>488</td>
                                                    <td>28.77%</td>
                                                    <td>
                                                        0 hrs : 16 mins : 28 secs
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-danger/10 text-danger">578</span>
                                                    </td>
                                                    <td>
                                                        2.5
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="box-footer">
                                    <div class="sm:flex items-center">
                                      <div class="dark:text-defaulttextcolor/70">
                                        Showing 5 Entries <i class="bi bi-arrow-right ms-2 font-semibold"></i>
                                      </div>
                                      <div class="ms-auto">
                                        <nav aria-label="Page navigation" class="pagination-style-4">
                                            <ul class="ti-pagination mb-0">
                                                <li class="page-item disabled">
                                                    <a class="page-link" href="javascript:void(0);">
                                                        Prev
                                                    </a>
                                                </li>
                                                <li class="page-item"><a class="page-link active" href="javascript:void(0);">1</a></li>
                                                <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                <li class="page-item">
                                                    <a class="page-link !text-primary" href="javascript:void(0);">
                                                        next
                                                    </a>
                                                </li>
                                            </ul>
                                        </nav>
                                      </div>
                                    </div>
                                  </div>
                            </div>
                        </div>
                        <div class="xl:col-span-3 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Visitors By Countries
                                    </div>
                                    <div class="hs-dropdown ti-dropdown">
                                        <a href="javascript:void(0);" class="px-2 font-normal text-[0.75rem] text-[#8c9097] dark:text-white/50"
                                          aria-expanded="false">
                                          View All<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                        </a>
                                        <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                          <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                              href="javascript:void(0);">Today</a></li>
                                          <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                              href="javascript:void(0);">This Week</a></li>
                                          <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                              href="javascript:void(0);">Last Week</a></li>
                                        </ul>
                                      </div>
                                  </div>
                                <div class="box-body">
                                    <ul class="list-none mb-0 analytics-visitors-countries min-w-full">
                                        <li>
                                            <div class="flex items-center">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-sm !mb-0 text-default">
                                                        <img src="{{asset('build/assets/images/flags/us_flag.jpg')}}" alt="" class="!rounded-full h-[1.75rem] w-[1.75rem]">
                                                    </span>
                                                </div>
                                                <div class="ms-4 flex-grow leading-none">
                                                    <span class="text-[0.75rem]">United States</span>
                                                </div>
                                                <div>
                                                    <span class="text-default badge bg-light font-semibold mt-2">32,190</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="flex items-center">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-sm !mb-0 avatar-rounded text-default">
                                                        <img src="{{asset('build/assets/images/flags/germany_flag.jpg')}}" alt="" class="!rounded-full h-[1.75rem] w-[1.75rem]">
                                                    </span>
                                                </div>
                                                <div class="ms-4 flex-grow leading-none">
                                                    <span class="text-[0.75rem]">Germany</span>
                                                </div>
                                                <div>
                                                    <span class="text-default badge bg-light font-semibold mt-2">8,798</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="flex items-center">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-sm !mb-0 avatar-rounded text-default">
                                                        <img src="{{asset('build/assets/images/flags/mexico_flag.jpg')}}" alt="" class="!rounded-full h-[1.75rem] w-[1.75rem]">
                                                    </span>
                                                </div>
                                                <div class="ms-4 flex-grow leading-none">
                                                    <span class="text-[0.75rem]">Mexico</span>
                                                </div>
                                                <div>
                                                    <span class="text-default badge bg-light font-semibold mt-2">16,885</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="flex items-center">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-sm !mb-0 avatar-rounded text-default">
                                                        <img src="{{asset('build/assets/images/flags/uae_flag.jpg')}}" alt=""  class="!rounded-full h-[1.75rem] w-[1.75rem]">
                                                    </span>
                                                </div>
                                                <div class="ms-4 flex-grow leading-none">
                                                    <span class="text-[0.75rem]">Uae</span>
                                                </div>
                                                <div>
                                                    <span class="text-default badge bg-light font-semibold mt-2">14,885</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="flex items-center">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-sm !mb-0 avatar-rounded text-default">
                                                        <img src="{{asset('build/assets/images/flags/argentina_flag.jpg')}}" alt="" class="!rounded-full h-[1.75rem] w-[1.75rem]">
                                                    </span>
                                                </div>
                                                <div class="ms-4 flex-grow leading-none">
                                                    <span class="text-[0.75rem]">Argentina</span>
                                                </div>
                                                <div>
                                                    <span class="text-default badge bg-light font-semibold mt-2">17,578</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="flex items-center">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-sm !mb-0 avatar-rounded text-default">
                                                        <img src="{{asset('build/assets/images/flags/russia_flag.jpg')}}" alt=""  class="!rounded-full h-[1.75rem] w-[1.75rem]">
                                                    </span>
                                                </div>
                                                <div class="ms-4 flex-grow leading-none">
                                                    <span class="text-[0.75rem]">Russia</span>
                                                </div>
                                                <div>
                                                    <span class="text-default badge bg-light font-semibold mt-2">10,118</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="flex items-center">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-sm !mb-0 avatar-rounded text-default">
                                                        <img src="{{asset('build/assets/images/flags/china_flag.jpg')}}" alt="" class="!rounded-full h-[1.75rem] w-[1.75rem]">
                                                    </span>
                                                </div>
                                                <div class="ms-4 flex-grow leading-none">
                                                    <span class="text-[0.75rem]">China</span>
                                                </div>
                                                <div>
                                                    <span class="text-default badge bg-light font-semibold mt-2">6,578</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="flex items-center">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-sm !mb-0 avatar-rounded text-default">
                                                        <img src="{{asset('build/assets/images/flags/french_flag.jpg')}}" alt=""  class="!rounded-full h-[1.75rem] w-[1.75rem]">
                                                    </span>
                                                </div>
                                                <div class="ms-4 flex-grow leading-none">
                                                    <span class="text-[0.75rem]">France</span>
                                                </div>
                                                <div>
                                                    <span class="text-default badge bg-light font-semibold mt-2">2,345</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="flex items-center">
                                                <div class="leading-none">
                                                    <span class="avatar avatar-sm !mb-0 avatar-rounded text-default">
                                                        <img src="{{asset('build/assets/images/flags/canada_flag.jpg')}}" alt="" class="!rounded-full h-[1.75rem] w-[1.75rem]">
                                                    </span>
                                                </div>
                                                <div class="ms-4 flex-grow leading-none">
                                                    <span class="text-[0.75rem]">Canada</span>
                                                </div>
                                                <div>
                                                    <span class="text-default badge bg-light font-semibold mt-2">1,678</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

@endsection

@section('scripts')

        <!-- Apex Charts JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- Used For Sessions By Device Chart -->
        <script src="{{asset('build/assets/libs/moment/moment.js')}}"></script>

        <!-- Analytics-Dashboard JS -->
        @vite('resources/assets/js/analytics-dashboard.js')
        

@endsection