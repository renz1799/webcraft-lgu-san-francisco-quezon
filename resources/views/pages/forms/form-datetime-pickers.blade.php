@extends('layouts.master')

@section('styles')

        <!-- FlatPickr CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/flatpickr/flatpickr.min.css')}}">
      
@endsection

@section('content')
 
                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Date &amp; Time Pickers</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Form Elements
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                  Date &amp; Time Pickers
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start:: row-1 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-3 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Basic Date picker
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-[#8c9097] dark:text-white/50 !border-e-0"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control" id="date" placeholder="Choose date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-3 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Date picker With Time
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-[#8c9097] dark:text-white/50 !border-e-0"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control" id="datetime" placeholder="Choose date with time">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-3 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Human Friendly dates
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-[#8c9097] dark:text-white/50 !border-e-0"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control" id="humanfrienndlydate" placeholder="Human friendly dates">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-3 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Date range picker
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-[#8c9097] dark:text-white/50 !border-e-0"> <i class="ri-calendar-line"></i> </div>
                                                <input type="text" class="form-control" id="daterange" placeholder="Date range picker">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End:: row-1 -->

                        <!-- Start:: row-2 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-3 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Basic Time picker
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-[#8c9097] dark:text-white/50 !border-e-0"> <i class="ri-time-line"></i> </div>
                                                <input type="text" class="form-control" id="timepikcr" placeholder="Choose time">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-3 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Time Picker with 24hr Format
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-[#8c9097] dark:text-white/50 !border-e-0"> <i class="ri-time-line"></i> </div>
                                                <input type="text" class="form-control" id="timepickr1" placeholder="Choose time in 24hr format">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-3 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Time Picker with Limits
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-[#8c9097] dark:text-white/50 !border-e-0"> <i class="ri-time-line"></i> </div>
                                                <input type="text" class="form-control" id="limittime" placeholder="choose time min 16:00 to max 22:30">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-3 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            DateTimePicker with Limited Time Range
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-text text-[#8c9097] dark:text-white/50 !border-e-0"> <i class="ri-time-line"></i> </div>
                                                <input type="text" class="form-control" id="limitdatetime" placeholder="date with time limit from 16:00 to 22:00">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End:: row-2 -->

                        <!-- Start:: row-3 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-6 col-span-12">
                                <div class="grid grid-cols-12">
                                    <div class="xl:col-span-12 col-span-12">
                                        <div class="box">
                                            <div class="box-header">
                                                <div class="box-title">
                                                    Date Picker with week numbers
                                                </div>
                                            </div>
                                            <div class="box-body">
                                                <div class="form-group mb-0">
                                                    <div class="input-group">
                                                        <div class="input-group-text text-[#8c9097] dark:text-white/50 !border-e-0"> <i class="ri-calendar-line"></i> </div>
                                                        <input type="text" class="form-control" id="weeknum" placeholder="Choose date">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="xl:col-span-12 col-span-12">
                                        <div class="box">
                                            <div class="box-header">
                                                <div class="box-title">
                                                    Inline Time Picker
                                                </div>
                                            </div>
                                            <div class="box-body">
                                                <div class="form-group mb-0">
                                                    <input type="text" class="form-control" id="inlinetime" placeholder="Choose time">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="xl:col-span-12 col-span-12">
                                        <div class="box">
                                            <div class="box-header">
                                                <div class="box-title">
                                                    Preloading time
                                                </div>
                                            </div>
                                            <div class="box-body">
                                                <div class="form-group mb-0">
                                                    <div class="input-group">
                                                        <div class="input-group-text text-[#8c9097] dark:text-white/50 !border-e-0"> <i class="ri-time-line"></i> </div>
                                                        <input type="text" class="form-control" id="pretime" placeholder="Preloading time">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-6 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Inline Calendar
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="form-group overflow-auto">
                                            <input type="text" class="form-control" id="inlinecalendar" placeholder="Choose date">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End:: row-3 -->
                       
@endsection

@section('scripts')

        <!-- Date & Time Picker JS -->
        <script src="{{asset('build/assets/libs/flatpickr/flatpickr.min.js')}}"></script>
        @vite('resources/assets/js/date-time_pickers.js')


@endsection