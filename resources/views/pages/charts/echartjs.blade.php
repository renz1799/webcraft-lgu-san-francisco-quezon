@extends('layouts.master')

@section('styles')

      
@endsection

@section('content')


                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Echarts</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                Charts
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                            </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                Echarts
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row-1 -->
                    <div class="grid grid-cols-12 gap-x-6">
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Basic Line Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-basic-line" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Smoothed Line Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-smoothed-line" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Basic Area Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-basic-area" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Stacked Line Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-stacked-line" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Stacked Area Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-stacked-area" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Step Line Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-step-line" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Basic Bar Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-bar-basic" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Bar With Background Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-bar-background" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Style For a Single Bar Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-bar-single" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Water Fall Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-waterfall" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Bar With Negative Values Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-negative-values" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Bar With Labels Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-bar-labels" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Horizontal Bar Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-bar-horizontal" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Horizontal Stacked Bar Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-stacked-horizontal" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Pie Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-pie" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Doughnut Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-doughnut" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Basic Scatter Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-scatter" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Bubble Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-bubble" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Candlestick Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-candlestick" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Basic Radar Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-basic-radar" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Heatmap Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-heatmap" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Treemap Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-treemap" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Funnel Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-funnel" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Basic Gauge Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-gauge-basic" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Simple Graph Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-simple-graph" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Pictorial Chart</div>
                                </div>
                                <div class="box-body">
                                    <div id="echart-pictorial" class="echart-charts"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->

@endsection

@section('scripts')

        <!-- Echarts JS -->
        <script src="{{asset('build/assets/libs/echarts/echarts.min.js')}}"></script>

        <!-- Internal Echarts JS -->
        @vite('resources/assets/js/echarts.js')


@endsection