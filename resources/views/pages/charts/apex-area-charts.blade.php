@extends('layouts.master')

@section('styles')

        <!-- Apexcharts CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/apexcharts/apexcharts.css')}}">
      
@endsection

@section('content')
 
                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Area Charts</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Apex Charts
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                  Area Charts
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Basic Area Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="area-basic"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Spline Area Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="area-spline"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Area Chart With Negative Values</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="area-negative"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Selection-Github Style Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="chart-months"></div>
                                        <div class="github-style flex items-center">
                                            <div class="me-2">
                                                <img class="userimg rounded" src="{{asset('build/assets/images/faces/1.jpg')}}"
                                                    data-hoverbox-user-id="634573" alt="" width="38" height="38">
                                            </div>
                                            <div class="userdetails lh-1">
                                                <a class="username fw-semibold text-sm">coder</a>
                                                <span class="cmeta block mt-1">
                                                    <span class="commits"></span> commits
                                                </span>
                                            </div>
                                        </div>
                                        <div id="chart-years"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Stacked Area Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="area-stacked"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Irregular Time Series Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="area-irregular"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Area Chart With Null Values</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="area-null"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header flex">
                                        <div class="box-title">Area Chart-Datetime X-Axis Chart</div>
                                        <div class="ms-auto flex">
                                            <button type="button" class="ti-btn-primary-full p-1 w-full rounded-s-sm" id="one_month">1M</button>
                                            <button type="button" class="ti-btn-primary-full p-1 w-full" id="six_months">6M</button>
                                            <button type="button" class="ti-btn-primary-full p-1 w-full" id="one_year">1Y</button>
                                            <button type="button" class="ti-btn-primary-full p-1 w-full rounded-e-sm" id="all">ALL</button>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div id="area-datetime"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End::row-1 -->

@endsection

@section('scripts')

        <!-- Apex Charts JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!---Used In Basic Area Chart-->
        <script src="{{asset('build/assets/apexcharts-stock-prices.js')}}"></script>


        <!-- Used For Secection-Github Style Chart -->
        <script src="{{asset('build/assets/apex-github-data.js')}}"></script>


        <!-- Used For Irregular Time Series Chart -->
        <script src="{{asset('build/assets/apexcharts-irregulardata.js')}}"></script>


        <script src="{{asset('build/assets/libs/moment/moment.js')}}"></script>

        <!-- Internal Apex Area Charts JS -->
        @vite('resources/assets/js/apexcharts-area.js')


@endsection