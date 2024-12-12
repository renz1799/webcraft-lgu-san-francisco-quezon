@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')
 
                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Pie Charts</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Apex Charts
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                    Pie Charts
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Basic Pie Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="pie-basic"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Simple Donut Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="donut-simple"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Updating Donut Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="donut-update"></div>
                                        <div class="text-center mt-4">
                                            <button type="button" class="btn btn-primary btn-sm" id="randomize">Randomize</button>
                                            <button type="button" class="btn btn-primary btn-sm" id="add">Add</button>
                                            <button type="button" class="btn btn-primary btn-sm" id="remove">Remove</button>
                                            <button type="button" class="btn btn-primary btn-sm" id="reset">Reset</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Monochrome Pie Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="pie-monochrome"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Gradient Donut Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="donut-gradient"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Donut Chart With Patterns</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="donut-pattern"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">Image Filled Pie Chart</div>
                                    </div>
                                    <div class="box-body">
                                        <div id="pie-image"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End::row-1 -->

@endsection

@section('scripts')

        <!-- Apex Charts JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- Internal Apex Pie Charts JS -->
        @vite('resources/assets/js/apexcharts-pie.js')
        

@endsection