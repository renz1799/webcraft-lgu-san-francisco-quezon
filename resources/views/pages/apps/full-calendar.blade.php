@extends('layouts.master')

@section('styles')
 
        <!-- Full Calendar CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/fullcalendar/main.min.css')}}">
      
@endsection

@section('content')
 
                  <!-- Page Header -->
                  <div class="block justify-between page-header md:flex">
                      <div>
                          <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Full Calendar</h3>
                      </div>
                      <ol class="flex items-center whitespace-nowrap min-w-0">
                          <li class="text-[0.813rem] ps-[0.5rem]">
                            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                              Apps
                              <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                            </a>
                          </li>
                          <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                              Full Calendar
                          </li>
                      </ol>
                  </div>
                  <!-- Page Header Close -->

                  <!-- Start::row-1 -->
                  <div class="grid grid-cols-12 gap-6">
                      <div class="xl:col-span-3 col-span-12">
                          <div class="box custom-box">
                              <div class="py-4 px-[1.25rem] border-b dark:border-defaultborder/10  !grid">
                                  <button type="button" class="ti-btn btn-wave ti-btn-primary"><i class="ri-add-line align-middle me-1 font-semibold inline-block"></i>Create New Event</button>
                              </div>
                              <div class="box-body !p-0">
                                  <div id="external-events" class="border-b dark:border-defaultborder/10 p-4">
                                      <div
                                        class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event !bg-primary border !border-primary">
                                        <div class="fc-event-main">Calendar Events</div>
                                      </div>
                                      <div
                                        class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event !bg-secondary border !border-secondary"
                                        data-class="bg-secondary">
                                        <div class="fc-event-main">Birthday EVents</div>
                                      </div>
                                      <div class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event !bg-success border !border-success"
                                        data-class="bg-success">
                                        <div class="fc-event-main">Holiday Calendar</div>
                                      </div>
                                      <div class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event !bg-info border !border-info"
                                        data-class="bg-info">
                                        <div class="fc-event-main">Office Events</div>
                                      </div>
                                      <div
                                        class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event !bg-warning border !border-warning"
                                        data-class="bg-warning">
                                        <div class="fc-event-main">Other Events</div>
                                      </div>
                                      <div class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event !bg-danger border !border-danger"
                                        data-class="bg-danger">
                                        <div class="fc-event-main">Festival Events</div>
                                      </div>
                                      <div class="fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event !bg-teal border !border-teal"
                                        data-class="bg-danger">
                                        <div class="fc-event-main">Timeline Events</div>
                                      </div>
                                  </div>
                                  <div class="p-4 border-b dark:border-defaultborder/10 ">
                                    <div class="flex items-center mb-4 justify-between">
                                      <h6 class="font-semibold">
                                        Activity :
                                      </h6>
                                      <button type="button" class="ti-btn btn-wave !py-1 !px-2 !text-[0.75rem] ti-btn-primary">View All</button>
                                    </div>
                                    <ul class="list-none mb-0 fullcalendar-events-activity" id="full-calendar-activity">
                                      <li>
                                        <div class="flex items-center justify-between flex-wrap">
                                          <p class="mb-1 font-semibold">
                                            Monday, Jan 1,2023
                                          </p>
                                          <span class="badge bg-light text-default mb-1">12:00PM - 1:00PM</span>
                                        </div>
                                        <p class="mb-0 text-mutedtext-[0.75rem]">
                                          Meeting with a client about new project requirement.
                                        </p>
                                      </li>
                                      <li>
                                        <div class="flex items-center justify-between flex-wrap">
                                          <p class="mb-1 font-semibold">
                                            Thursday, Dec 29,2022
                                          </p>
                                          <span class="badge bg-success text-white mb-1">Completed</span>
                                        </div>
                                        <p class="mb-0 text-muted text-[0.75rem]">
                                          Birthday party of niha suka
                                        </p>
                                      </li>
                                      <li>
                                        <div class="flex items-center justify-between flex-wrap">
                                          <p class="mb-1 font-semibold">
                                            Wednesday, Jan 3,2023
                                          </p>
                                          <span class="badge bg-warning/10 text-warning mb-1">Reminder</span>
                                        </div>
                                        <p class="mb-0 text-mutedtext-[0.75rem]">
                                          WOrk taget for new project is completing
                                        </p>
                                      </li>
                                      <li>
                                        <div class="flex items-center justify-between flex-wrap">
                                          <p class="mb-1 font-semibold">
                                            Friday, Jan 20,2023
                                          </p>
                                          <span class="badge bg-light text-default mb-1">06:00PM - 09:00PM</span>
                                        </div>
                                        <p class="mb-0 text-mutedtext-[0.75rem]">
                                          Watch new movie with family
                                        </p>
                                      </li>
                                      <li>
                                        <div class="flex items-center justify-between flex-wrap">
                                          <p class="mb-1 font-semibold">
                                            Saturday, Jan 07,2023
                                          </p>
                                          <span class="badge bg-danger/10 text-danger mb-1">Due Date</span>
                                        </div>
                                        <p class="mb-0 text-muted text-[0.75rem]">
                                          Last day to pay the electricity bill and water bill.need to check the bank details.
                                        </p>
                                      </li>
                                    </ul>
                                  </div>
                                  <div class="p-4">
                                    <img src="{{asset('build/assets/images/media/media-83.svg')}}" alt="">
                                  </div>
                              </div>
                          </div>
                      </div>
                      <div class="xl:col-span-9 col-span-12">
                          <div class="box custom-box">
                              <div class="box-header">
                                  <div class="box-title">Full Calendar</div>
                              </div>
                              <div class="box-body">
                                  <div id="calendar2"></div>
                              </div>
                          </div>
                      </div>
                  </div>
                  <!--End::row-1 -->

@endsection

@section('scripts')

        <!-- Moment JS -->
        <script src="{{asset('build/assets/libs/moment/moment.js')}}"></script>

        <!-- Fullcalendar JS -->
        <script src="{{asset('build/assets/libs/fullcalendar/main.min.js')}}"></script>
        @vite('resources/assets/js/fullcalendar.js')


@endsection