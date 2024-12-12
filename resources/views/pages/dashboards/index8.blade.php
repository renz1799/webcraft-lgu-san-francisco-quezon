@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')

                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Projects</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                Dashboards
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                Projects
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <div class="grid grid-cols-12 gap-x-6">
                        <div class="xxl:col-span-9 col-span-12">
                            <div class="grid grid-cols-12 gap-x-6">
                                <div class="xxl:col-span-5 col-span-12">
                                    <div class="grid grid-cols-12 gap-x-6">
                                        <div class="sm:col-span-6 col-span-12">
                                            <div class="box">
                                                <div class="box-body flex justify-between items-center">
                                                    <div>
                                                        <p class="mb-1">Completed Projects</p>
                                                        <h4 class="font-semibold mb-1 text-[1.5rem]">109</h4>
                                                        <span class="badge bg-success/10 text-success inline-flex">1.5% <i class="ti ti-trending-up ms-1"></i></span><span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] ms-1">this month</span>
                                                    </div>
                                                    <div>
                                                        <span class="avatar avatar-md bg-primary text-white p-2">
                                                            <i class="ti ti-file-check text-[1.25rem] text-white opacity-[0.7]"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-6 col-span-12">
                                            <div class="box">
                                                <div class="box-body flex justify-between items-center">
                                                    <div>
                                                        <p class="mb-1">Overdue Projects</p>
                                                        <h4 class="font-semibold mb-1 text-[1.5rem]">18</h4>
                                                        <span class="badge bg-danger/10 text-danger inline-flex">0.23% <i class="ti ti-trending-down ms-1"></i></span><span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] ms-1">this month</span>
                                                    </div>
                                                    <div>
                                                        <span class="avatar avatar-md bg-secondary text-white p-2">
                                                            <i class="ti ti-briefcase text-[1.25rem] opacity-[0.7]"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-6 col-span-12">
                                            <div class="box">
                                                <div class="box-body flex justify-between items-center">
                                                    <div>
                                                        <p class="mb-1">Total Projects</p>
                                                        <h4 class="font-semibold mb-1 text-[1.5rem]">389</h4>
                                                        <span class="badge bg-success/10 text-success inline-flex">0.67% <i class="ti ti-trending-up ms-1"></i></span><span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] ms-1">this month</span>
                                                    </div>
                                                    <div>
                                                        <span class="avatar avatar-md bg-success text-white p-2">
                                                            <i class="ti ti-album text-[1.25rem] opacity-[0.7]"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="sm:col-span-6 col-span-12">
                                            <div class="box">
                                                <div class="box-body flex justify-between items-center">
                                                    <div>
                                                        <p class="mb-1">Pending Projects</p>
                                                        <h4 class="font-semibold mb-1 text-[1.5rem]">227</h4>
                                                        <span class="badge bg-success/10 text-success inline-flex">0.53% <i class="ti ti-trending-up ms-1"></i></span><span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] ms-1">this month</span>
                                                    </div>
                                                    <div>
                                                        <span class="avatar avatar-md bg-warning text-white p-2">
                                                            <i class="ti ti-chart-pie-2 text-[1.25rem] opacity-[0.7]"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="xl:col-span-12 col-span-12">
                                            <div class="box">
                                                <div class="box-header justify-between">
                                                    <div class="box-title">
                                                        Project Analysis
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
                                                    <div id="projectAnalysis"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="xxl:col-span-4 col-span-12">
                                    <div class="grid grid-cols-12 gap-x-6">
                                        <div class="xl:col-span-12 col-span-12">
                                            <div class="box">
                                                <div class="box-header justify-between">
                                                    <div class="box-title">
                                                        Team Members
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
                                                    <ul class="list-none team-members-card mb-0">
                                                        <li>
                                                            <a href="javascript:void(0)">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex items-start">
                                                                        <span class="avatar avatar-sm leading-none">
                                                                            <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="" class="rounded-md">
                                                                        </span>
                                                                        <div class="ms-4 leading-none">
                                                                            <span class="font-semibold">Melissa Smith</span>
                                                                            <span class="block text-[0.6875rem] text-[#8c9097] dark:text-white/50 mt-2">Ui Developer</span>
                                                                        </div>
                                                                    </div>
                                                                    <div id="user1"></div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0)">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex items-start">
                                                                        <span class="avatar avatar-sm leading-none">
                                                                            <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="" class="rounded-md">
                                                                        </span>
                                                                        <div class="ms-4 leading-none">
                                                                            <span class="font-semibold">Jason Momoa</span>
                                                                            <span class="block text-[0.6875rem] text-[#8c9097] dark:text-white/50 mt-2">React Developer</span>
                                                                        </div>
                                                                    </div>
                                                                    <div id="user2"></div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0)">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex items-start">
                                                                        <span class="avatar avatar-sm leading-none">
                                                                            <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="" class="rounded-md">
                                                                        </span>
                                                                        <div class="ms-4 leading-none">
                                                                            <span class="font-semibold">Kamala Hars</span>
                                                                            <span class="block text-[0.6875rem] text-[#8c9097] dark:text-white/50 mt-2">Testing</span>
                                                                        </div>
                                                                    </div>
                                                                    <div id="user3"></div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0)">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex items-start">
                                                                        <span class="avatar avatar-sm leading-none">
                                                                            <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="" class="rounded-md">
                                                                        </span>
                                                                        <div class="ms-4 leading-none">
                                                                            <span class="font-semibold">Diego Sanch</span>
                                                                            <span class="block text-[0.6875rem] text-[#8c9097] dark:text-white/50 mt-2">Angular Developer</span>
                                                                        </div>
                                                                    </div>
                                                                    <div id="user4"></div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a href="javascript:void(0)">
                                                                <div class="flex items-center justify-between">
                                                                    <div class="flex items-start">
                                                                        <span class="avatar avatar-sm leading-none">
                                                                            <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="" class="rounded-md">
                                                                        </span>
                                                                        <div class="ms-4 leading-none">
                                                                            <span class="font-semibold">Jake Sully</span>
                                                                            <span class="block text-[0.6875rem] text-[#8c9097] dark:text-white/50 mt-2">Web Designer</span>
                                                                        </div>
                                                                    </div>
                                                                    <div id="user5"></div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="xl:col-span-12 col-span-12">
                                            <div class="box">
                                                <div class="box-header justify-between">
                                                    <div class="box-title">
                                                        Main Tasks
                                                    </div>
                                                    <div class="hs-dropdown ti-dropdown">
                                                        <a href="javascript:void(0);" class="px-2 font-normal text-[0.75rem] text-[#8c9097] dark:text-white/50"
                                                          aria-expanded="false">
                                                          Today<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                                        </a>
                                                        <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                                          <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                              href="javascript:void(0);">Weak</a></li>
                                                          <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                              href="javascript:void(0);">Month</a></li>
                                                          <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                              href="javascript:void(0);">Year</a></li>
                                                        </ul>
                                                      </div>
                                                </div>
                                                <div class="box-body">
                                                    <ul class="list-none projects-maintask-card">
                                                        <li>
                                                            <div class="flex items-start">
                                                                <div class="flex items-start flex-grow">
                                                                    <span class="me-4">
                                                                        <input class="form-check-input" type="checkbox" id="checkboxNoLabel1" value="" aria-label="...">
                                                                    </span>
                                                                    <div class="flex-grow">
                                                                        <span>
                                                                            Designing a landing page
                                                                        </span>
                                                                        <span class="block mt-1">
                                                                            <span class="avatar-list-stacked">
                                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                                                </span>
                                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                                    <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                                                </span>
                                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                                                </span>
                                                                            </span>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <span class="badge bg-primary/10 text-primary">
                                                                        In progress
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="flex items-start">
                                                                <div class="flex items-start flex-grow">
                                                                    <span class="me-4">
                                                                        <input class="form-check-input" type="checkbox" id="checkboxNoLabel2" value="" aria-label="..." checked>
                                                                    </span>
                                                                    <div class="flex-grow">
                                                                        <span>
                                                                            Testing of new project ui
                                                                        </span>
                                                                        <span class="block mt-1">
                                                                            <span class="avatar-list-stacked">
                                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                                    <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                                                </span>
                                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                                    <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img">
                                                                                </span>
                                                                            </span>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <span class="badge bg-success/10 text-success">
                                                                        Completed
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="flex items-start">
                                                                <div class="flex items-start flex-grow">
                                                                    <span class="me-4">
                                                                        <input class="form-check-input" type="checkbox" id="checkboxNoLabel3" value="" aria-label="...">
                                                                    </span>
                                                                    <div class="flex-grow">
                                                                        <span>
                                                                            Fixing bugs in registration page
                                                                        </span>
                                                                        <span class="block mt-1">
                                                                            <span class="avatar-list-stacked">
                                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                                    <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                                                </span>
                                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                                    <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                                                </span>
                                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                                    <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                                                </span>
                                                                            </span>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <span class="badge bg-warning/10 text-warning">
                                                                        pending
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </li>
                                                        <li>
                                                            <div class="flex items-start">
                                                                <div class="flex items-start flex-grow">
                                                                    <span class="me-4">
                                                                        <input class="form-check-input" type="checkbox" id="checkboxNoLabel4" value="" aria-label="..." checked>
                                                                    </span>
                                                                    <div class="flex-grow">
                                                                        <span>
                                                                            Designing new dashboard
                                                                        </span>
                                                                        <span class="block mt-1">
                                                                            <span class="avatar-list-stacked">
                                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                                    <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                                                </span>
                                                                                <span class="avatar avatar-xs avatar-rounded">
                                                                                    <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                                                </span>
                                                                            </span>
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <span class="badge bg-primary/10 text-primary">
                                                                        In progress
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="xxl:col-span-3 col-span-12">
                                    <div class="box">
                                        <div class="box-header justify-between">
                                            <div class="box-title">
                                                Daily Tasks
                                            </div>
                                            <div class="hs-dropdown ti-dropdown">
                                                <a href="javascript:void(0);" class="px-2 font-normal text-[0.75rem] text-[#8c9097] dark:text-white/50"
                                                  aria-expanded="false">
                                                  View All<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                                </a>
                                                <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                                  <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                      href="javascript:void(0);">Download</a></li>
                                                  <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                      href="javascript:void(0);">Import</a></li>
                                                  <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                      href="javascript:void(0);">Export</a></li>
                                                </ul>
                                              </div>
                                        </div>
                                        <div class="box-body">
                                            <ul class="list-none daily-task-card my-2">
                                                <li>
                                                    <div class="box border border-primary/25 shadow-none mb-0">
                                                        <div class="box-body">
                                                            <p class="text-[0.875rem] font-semibold mb-2 leadining-none">Home Page Design
                                                                <a aria-label="anchor" href="javascript:void(0);"><i class="bi bi-plus-square ltr:float-right rtl:float-left text-primary text-[1.125rem]"></i></a>
                                                            </p>
                                                            <div class="flex flex-wrap gap-2 mb-4">
                                                                <span class="badge text-primary bg-primary/10">Framework</span>
                                                                <span class="badge text-secondary bg-secondary/10">Angular</span>
                                                                <span class="badge text-info bg-info/10">Php</span>
                                                            </div>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="box border border-warning/25 shadow-none custom-card">
                                                        <div class="box-body">
                                                            <p class="text-[0.875rem] font-semibold mb-2 leadining-none">About Us Page redesign
                                                                <a aria-label="anchor" href="javascript:void(0);"><i class="bi bi-plus-square ltr:float-right rtl:float-left text-warning text-[1.125rem]"></i></a>
                                                            </p>
                                                            <div class="flex flex-wrap gap-2 mb-4">
                                                                <span class="badge text-danger bg-danger/10">Html</span>
                                                                <span class="badge text-warning bg-warning/10">Symphony</span>
                                                                <span class="badge text-success bg-success/10">Php</span>
                                                            </div>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="box border border-success/25 shadow-none custom-card">
                                                        <div class="box-body">
                                                            <p class="text-[0.875rem] font-semibold mb-2 leadining-none">About Us Page redesign
                                                                <a aria-label="anchor" href="javascript:void(0);"><i class="bi bi-plus-square ltr:float-right rtl:float-left text-success text-[1.125rem]"></i></a>
                                                            </p>
                                                            <div class="flex flex-wrap gap-2 mb-4">
                                                                <span class="badge text-danger bg-danger/10">Html</span>
                                                                <span class="badge text-warning bg-warning/10">Symphony</span>
                                                                <span class="badge text-success bg-success/10">Php</span>
                                                            </div>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="box border border-secondary/25 shadow-none custom-card !mb-0">
                                                        <div class="box-body">
                                                            <p class="text-[0.875rem] font-semibold mb-2 leadining-none">New Project Discussion
                                                                <a aria-label="anchor" href="javascript:void(0);"><i class="bi bi-plus-square ltr:float-right rtl:float-left text-secondary text-[1.125rem]"></i></a>
                                                            </p>
                                                            <div class="flex flex-wrap gap-2 mb-4">
                                                                <span class="badge text-info bg-info/10">React</span>
                                                                <span class="badge text-primary bg-primary/10">Typescript</span>
                                                            </div>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/14.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="xxl:col-span-3 col-span-12">
                            <div class="grid grid-cols-12 gap-x-6">
                                <div class="xxl:col-span-12 col-span-12">
                                    <div class="box shadow-none projects-tracking-card overflow-hidden text-center">
                                        <div class="box-body">
                                            <img src="{{asset('build/assets/images/media/media-86.svg')}}" alt="" class="mb-1 inline-flex">
                                            <div>
                                                <span class="text-[0.9375rem] font-semibold block mt-6 mb-4">Track your work progress here</span>
                                                <button type="button" class="ti-btn !py-1 !px-2 bg-primary !text-[0.75rem] text-white  btn-wave">Track Here</button>
                                            </div>
                                            <span class="shape-1 text-primary"><i class="ti ti-circle text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-2 text-secondary"><i class="ti ti-triangle text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-3 text-warning"><i class="ti ti-square text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-4 text-info"><i class="ti ti-square-rotated text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-5 text-success"><i class="ti ti-pentagon text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-6 text-danger"><i class="ti ti-star text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-7 text-pinkmain"><i class="ti ti-hexagon text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-8 text-tealmain"><i class="ti ti-octagon text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-9 text-primary"><i class="ti ti-circle text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-10 text-secondary"><i class="ti ti-triangle text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-11 text-warning"><i class="ti ti-square text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-12 text-info"><i class="ti ti-square-rotated text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-13 text-success"><i class="ti ti-pentagon text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-14 text-danger"><i class="ti ti-star text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-15 text-pinkmain"><i class="ti ti-hexagon text-[1.25rem] font-bold"></i></span>
                                            <span class="shape-16 text-tealmain"><i class="ti ti-octagon text-[1.25rem] font-bold"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="xxl:col-span-12 col-span-12">
                                    <div class="box">
                                        <div class="box-header justify-between">
                                            <div class="box-title">
                                                Recent Transactions
                                            </div>
                                            <div class="hs-dropdown ti-dropdown">
                                                <a href="javascript:void(0);" class="px-2 font-normal text-[0.75rem] text-[#8c9097] dark:text-white/50"
                                                  aria-expanded="false">
                                                  View All<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                                </a>
                                                <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                                  <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                      href="javascript:void(0);">Download</a></li>
                                                  <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                      href="javascript:void(0);">Import</a></li>
                                                  <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                      href="javascript:void(0);">Export</a></li>
                                                </ul>
                                              </div>
                                        </div>
                                        <div class="box-body">
                                            <ul class="list-none project-transactions-card">
                                                <li>
                                                    <div class="flex items-start">
                                                        <div class="me-3">
                                                            <span class="avatar avatar-rounded font-bold avatar-md !text-primary bg-primary/10">
                                                                S
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <span class="block font-semibold">Simon Cowall</span>
                                                            <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Feb 28,2023 - 12:54PM</span>
                                                        </div>
                                                        <div>
                                                            <h6 class="font-semibold text-[1rem]">$21,442</h6>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="flex items-start">
                                                        <div class="me-3">
                                                            <span class="avatar avatar-rounded font-bold avatar-md !text-secondary bg-secondary/10">
                                                                M
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <span class="block font-semibold">Melissa Blue</span>
                                                            <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Mar 28,2023 - 10:14AM</span>
                                                        </div>
                                                        <div>
                                                            <h6 class="font-semibold text-[1rem]">$8,789</h6>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="flex items-start">
                                                        <div class="me-3">
                                                            <span class="avatar avatar-rounded font-bold avatar-md !text-success bg-success/10">
                                                                G
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <span class="block font-semibold">Gabriel Shin</span>
                                                            <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Mar 16,2023 - 05:27PM</span>
                                                        </div>
                                                        <div>
                                                            <h6 class="font-semibold text-[1rem]">$13,677</h6>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="flex items-start">
                                                        <div class="me-3">
                                                            <span class="avatar avatar-rounded font-bold avatar-md !text-warning bg-warning/10">
                                                                Y
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <span class="block font-semibold">Yohasimi Nakiyaro</span>
                                                            <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Mar 19,2023 - 04:45PM</span>
                                                        </div>
                                                        <div>
                                                            <h6 class="font-semibold text-[1rem]">$3,543</h6>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="flex items-start">
                                                        <div class="me-3">
                                                            <span class="avatar avatar-rounded font-bold avatar-md !text-info bg-info/10">
                                                                B
                                                            </span>
                                                        </div>
                                                        <div class="flex-grow">
                                                            <span class="block font-semibold">Brenda Lynn</span>
                                                            <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Mar 10,2023 - 05:25PM</span>
                                                        </div>
                                                        <div>
                                                            <h6 class="font-semibold">$7,890</h6>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-12 gap-x-6">
                        <div class="xxl:col-span-12 col-span-12">
                            <div class="box">
                                <div class="box-header justify-between">
                                    <div class="box-title">
                                        Projects Summary
                                    </div>
                                    <div class="flex flex-wrap">
                                        <div class="me-4 my-1">
                                            <input class="ti-form-control form-control-sm !rounded-sm" type="text" placeholder="Search Here" aria-label=".form-control-sm example">
                                        </div>
                                        <div class="hs-dropdown ti-dropdown !py-1 !mb-2">
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
                                                    <th scope="col" class="!text-start">S.No</th>
                                                    <th scope="col" class="!text-start">Title</th>
                                                    <th scope="col" class="!text-start">Assigned To</th>
                                                    <th scope="col" class="!text-start">Tasks</th>
                                                    <th scope="col" class="!text-start">Progress</th>
                                                    <th scope="col" class="!text-start">Status</th>
                                                    <th scope="col" class="!text-start">Due Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="border border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        1
                                                    </th>
                                                    <td>
                                                        Home Page
                                                    </td>
                                                    <td>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                            </span>
                                                            <a class="avatar avatar-xs bg-primary avatar-rounded text-white text-[0.65rem] font-normal" href="javascript:void(0);">
                                                                +2
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td>110/180</td>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <div class="progress progress-animate progress-xs w-full" >
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-0"></div>
                                                            </div>
                                                            <div class="ms-2">0%</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary/10 text-primary">In Progress</span>
                                                    </td>
                                                    <td>
                                                        14-04-2023
                                                    </td>
                                                </tr>
                                                <tr class="border border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        2
                                                    </th>
                                                    <td>
                                                        Landing Design
                                                    </td>
                                                    <td>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>95/100</td>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <div class=" progress progress-animate progress-xs w-full" >
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-[95%]"></div>
                                                            </div>
                                                            <div class="ms-2">95%</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary/10 text-primary">In Progress</span>
                                                    </td>
                                                    <td>
                                                        20-04-2023
                                                    </td>
                                                </tr>
                                                <tr class="border border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        3
                                                    </th>
                                                    <td>
                                                        New Template Design
                                                    </td>
                                                    <td>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>90/100</td>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <div class="progress progress-animate progress-xs w-full" >
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-0"></div>
                                                            </div>
                                                            <div class="ms-2">0%</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-warning/10 text-warning">Pending</span>
                                                    </td>
                                                    <td>
                                                        29-05-2023
                                                    </td>
                                                </tr>
                                                <tr class="border border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        4
                                                    </th>
                                                    <td>
                                                        HR Management Template Design
                                                    </td>
                                                    <td>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                            </span>
                                                            <a class="avatar avatar-xs bg-primary avatar-rounded text-white text-[0.65rem] font-normal" href="javascript:void(0);">
                                                                +5
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td>26/71</td>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <div class="progress progress-animate progress-xs w-full" >
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-[35%]"></div>
                                                            </div>
                                                            <div class="ms-2">35%</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary/10 text-primary">In Progress</span>
                                                    </td>
                                                    <td>
                                                        18-04-2023
                                                    </td>
                                                </tr>
                                                <tr class="border border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        5
                                                    </th>
                                                    <td>
                                                        Designing New Template
                                                    </td>
                                                    <td>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/16.jpg')}}" alt="img">
                                                            </span>
                                                            <a class="avatar avatar-xs bg-primary avatar-rounded text-white text-[0.65rem] font-normal" href="javascript:void(0);">
                                                                +3
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td>26/71</td>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <div class="progress progress-animate progress-xs w-full" >
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-full"></div>
                                                            </div>
                                                            <div class="ms-2">100%</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-success/10 text-success">Completed</span>
                                                    </td>
                                                    <td>
                                                        11-04-2023
                                                    </td>
                                                </tr>
                                                <tr class="border border-inherit border-solid hover:bg-gray-100 dark:hover:bg-light dark:border-defaultborder/10">
                                                    <th scope="row" class="!text-start">
                                                        6
                                                    </th>
                                                    <td>
                                                        Documentation Project
                                                    </td>
                                                    <td>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-xs avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </td>
                                                    <td>45/90</td>
                                                    <td>
                                                        <div class="flex items-center">
                                                            <div class="progress progress-animate progress-xs w-full" >
                                                                <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary w-1/2"></div>
                                                            </div>
                                                            <div class="ms-2">50%</div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary/10 text-primary">In Progress</span>
                                                    </td>
                                                    <td>
                                                        18-04-2023
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
                    </div>
        
@endsection

@section('scripts')       

        <!-- Apex Charts JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- Used For Multiple Colored Timeline Chart -->
        <script src="{{asset('build/assets/libs/moment/moment.js')}}"></script>

        <!-- Projects-Dashboard JS -->
        @vite('resources/assets/js/projects-dashboard.js')


@endsection