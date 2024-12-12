@extends('layouts.master')

@section('styles')
 
        <!-- Leaflet Maps CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/leaflet/leaflet.css')}}">

@endsection

@section('content')

                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Leaflet Maps</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                Maps
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                              Leaflet Maps
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row-1 -->
                    <div class="grid grid-cols-12 gap-x-6">
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Leaflet Map</div>
                                </div>
                                <div class="box-body">
                                    <div id="map"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Map With Markers,Circles and Polygons</div>
                                </div>
                                <div class="box-body">
                                    <div id="map1"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Map With Popup</div>
                                </div>
                                <div class="box-body">
                                    <div id="map-popup"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Map With Custom Icon</div>
                                </div>
                                <div class="box-body">
                                    <div id="map-custom-icon"></div>
                                </div>
                            </div>
                        </div>
                        <div class="xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">Interactive Choropleth Map</div>
                                </div>
                                <div class="box-body">
                                    <div id="interactive-map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->     

@endsection

@section('scripts')
     
        <!-- Leaflet Maps JS -->
        <script src="{{asset('build/assets/libs/leaflet/leaflet.js')}}"></script>
        @vite('resources/assets/js/leaflet.js')


@endsection