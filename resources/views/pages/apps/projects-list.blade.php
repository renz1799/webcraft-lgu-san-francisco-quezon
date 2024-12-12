@extends('layouts.master')

@section('styles')
        
        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">
      
@endsection

@section('content')
 
                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Projects List</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Projects
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                    Projects List
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-body p-4">
                                        <div class="flex items-center justify-between flex-wrap gap-4">
                                            <div class="flex flex-wrap gap-1 newproject">
                                                <a href="{{url('projects-create')}}" class="ti-btn btn-wave ti-btn-primary-full me-2 !mb-0"><i class="ri-add-line me-1 font-semibold align-middle"></i>New Project</a>
                                                <select class="form-control !w-auto" data-trigger name="choices-single-default" id="choices-single-default">
                                                    <option value="">Sort By</option>
                                                    <option value="Newest">Newest</option>
                                                    <option value="Date Added">Date Added</option>
                                                    <option value="Type">Type</option>
                                                    <option value="A - Z">A - Z</option>
                                                </select>
                                            </div>
                                            <div class="avatar-list-stacked">
                                                <span class="avatar avatar-sm avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-sm avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-sm avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-sm avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-sm avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-sm avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-sm avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-sm avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img">
                                                </span>
                                                <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                    +8
                                                </a>
                                            </div>
                                            <div class="flex" role="search">
                                                <input class="form-control me-2" type="search" placeholder="Search Project" aria-label="Search">
                                                <button class="ti-btn btn-wave ti-btn-light !mb-0" type="submit">Search</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End::row-1 -->

                        <!-- Start::row-2 -->
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="xxl:col-span-3 xl:col-span-4 md:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header items-center !justify-start flex-wrap !flex">
                                        <div class="me-2">
                                            <span class="avatar avatar-rounded p-1 bg-danger/10 text-danger">
                                                <img src="{{asset('build/assets/images/company-logos/1.png')}}" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);" class="font-semibold text-[.875rem] block text-truncate project-list-title">Design &amp; Developing New Project</a>
                                            <span class="text-[#8c9097] dark:text-white/50 block text-[0.75rem]">Total <strong class="text-defaulttextcolor">18/22</strong> tasks completed</span>
                                        </div>
                                        <div class="hs-dropdown ti-dropdown">
                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn btn-wave ti-btn-sm ti-btn-light !mb-0" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-eye-line align-middle me-1 inline-flex"></i>View</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-edit-line align-middle me-1 inline-flex"></i>Edit</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle inline-flex"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <div class="font-semibold mb-1">Team :</div>
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
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                        +2
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="font-semibold mb-1">Priority :</div>
                                                <span class="badge bg-success/10 text-success">Low</span>
                                            </div>
                                        </div>
                                        <div class="font-semibold mb-1">Description :</div>
                                        <p class="text-[#8c9097] dark:text-white/50 mb-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi maiores similique tempore.</p>
                                        <div class="font-semibold mb-1">Status :</div>
                                        <div>
                                            <div></div>
                                            <div class="progress progress-xs progress-animate" >
                                                <div class="progress-bar bg-primary w-4/5"></div>
                                            </div>
                                            <div class="mt-1"><span class="text-primary font-semibold">80%</span> Completed</div>
                                        </div>
                                    </div>
                                    <div class="box-footer flex items-center justify-between">
                                        <div>
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Assigned Date :</span>
                                            <span class="font-semibold block">24,May 2023</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Due Date :</span>
                                            <span class="font-semibold block">12,Jul 2023</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-4 md:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header items-center !justify-start flex-wrap !flex">
                                        <div class="me-2">
                                            <span class="avatar avatar-rounded p-1 bg-warning/10">
                                                <img src="{{asset('build/assets/images/company-logos/2.png')}}" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);" class="font-semibold text-[.875rem] block !truncate project-list-title">Content Management System (CMS) Integration</a>
                                            <span class="text-[#8c9097] dark:text-white/50 block text-[0.75rem]">Total <strong class="text-defaulttextcolor">26/68</strong> tasks completed</span>
                                        </div>
                                        <div class="hs-dropdown ti-dropdown">
                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn btn-wave ti-btn-sm ti-btn-light !mb-0" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-eye-line align-middle me-1 inline-flex"></i>View</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-edit-line align-middle me-1 inline-flex"></i>Edit</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle inline-flex"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <div class="font-semibold mb-1">Team :</div>
                                                <div class="avatar-list-stacked">
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                        +4
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="font-semibold mb-1">Priority :</div>
                                                <span class="badge bg-info/10 text-info">Medium</span>
                                            </div>
                                        </div>
                                        <div class="font-semibold mb-1">Description :</div>
                                        <p class="text-[#8c9097] dark:text-white/50 mb-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi maiores similique tempore.</p>
                                        <div class="font-semibold mb-1">Status :</div>
                                        <div>
                                            <div></div>
                                            <div class="progress progress-xs progress-animate" >
                                                <div class="progress-bar bg-primary w-[45%]"></div>
                                            </div>
                                            <div class="mt-1"><span class="text-primary font-semibold">45%</span> Completed</div>
                                        </div>
                                    </div>
                                    <div class="box-footer flex items-center justify-between">
                                        <div>
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Assigned Date :</span>
                                            <span class="font-semibold block">20,May 2023</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Due Date :</span>
                                            <span class="font-semibold block">10,Jun 2023</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-4 md:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header items-center !justify-start flex-wrap !flex">
                                        <div class="me-2">
                                            <span class="avatar avatar-rounded p-1 bg-secondary/10">
                                                <img src="{{asset('build/assets/images/company-logos/3.png')}}" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);" class="font-semibold text-[.875rem] block text-truncate project-list-title">Task Scheduler and Automation</a>
                                            <span class="text-[#8c9097] dark:text-white/50 block text-[0.75rem]">Total <strong class="text-default">21/45</strong> tasks completed</span>
                                        </div>
                                        <div class="hs-dropdown ti-dropdown">
                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn btn-wave ti-btn-sm ti-btn-light !mb-0" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-eye-line align-middle me-1 inline-flex"></i>View</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-edit-line align-middle me-1 inline-flex"></i>Edit</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle inline-flex"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <div class="font-semibold mb-1">Team :</div>
                                                <div class="avatar-list-stacked">
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                        +1
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="font-semibold mb-1">Priority :</div>
                                                <span class="badge bg-success/10 text-success">Low</span>
                                            </div>
                                        </div>
                                        <div class="font-semibold mb-1">Description :</div>
                                        <p class="text-[#8c9097] dark:text-white/50 mb-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi maiores similique tempore.</p>
                                        <div class="font-semibold mb-1">Status :</div>
                                        <div>
                                            <div></div>
                                            <div class="progress progress-xs progress-animate" >
                                                <div class="progress-bar bg-primary w-[66%]"></div>
                                            </div>
                                            <div class="mt-1"><span class="text-primary font-semibold">66%</span> Completed</div>
                                        </div>
                                    </div>
                                    <div class="box-footer flex items-center justify-between">
                                        <div>
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Assigned Date :</span>
                                            <span class="font-semibold block">31,May 2023</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Due Date :</span>
                                            <span class="font-semibold block">10,Jul 2023</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-4 md:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header items-center !justify-start flex-wrap !flex">
                                        <div class="me-2">
                                            <span class="avatar avatar-rounded p-1 bg-success/10 text-success">
                                                <img src="{{asset('build/assets/images/company-logos/5.png')}}" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);" class="font-semibold text-[.875rem] block text-truncate project-list-title">Advanced Search and Filtering</a>
                                            <span class="text-[#8c9097] dark:text-white/50 block text-[0.75rem]">Total <strong class="text-default">45/54</strong> tasks completed</span>
                                        </div>
                                        <div class="hs-dropdown ti-dropdown">
                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn btn-wave ti-btn-sm ti-btn-light !mb-0" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-eye-line align-middle me-1 inline-flex"></i>View</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-edit-line align-middle me-1 inline-flex"></i>Edit</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle inline-flex"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <div class="font-semibold mb-1">Team :</div>
                                                <div class="avatar-list-stacked">
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                        +2
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="font-semibold mb-1">Priority :</div>
                                                <span class="badge bg-danger/10 text-danger">High</span>
                                            </div>
                                        </div>
                                        <div class="font-semibold mb-1">Description :</div>
                                        <p class="text-[#8c9097] dark:text-white/50 mb-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi maiores similique tempore.</p>
                                        <div class="font-semibold mb-1">Status :</div>
                                        <div>
                                            <div></div>
                                            <div class="progress progress-xs progress-animate" >
                                                <div class="progress-bar bg-primary w-[89%]"></div>
                                            </div>
                                            <div class="mt-1"><span class="text-primary font-semibold">89%</span> Completed</div>
                                        </div>
                                    </div>
                                    <div class="box-footer flex items-center justify-between">
                                        <div>
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Assigned Date :</span>
                                            <span class="font-semibold block">02,Jun 2023</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Due Date :</span>
                                            <span class="font-semibold block">15,Jun 2023</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-4 md:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header items-center !justify-start flex-wrap !flex">
                                        <div class="me-2">
                                            <span class="avatar avatar-rounded p-1 bg-primary/10">
                                                <img src="{{asset('build/assets/images/company-logos/8.png')}}" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);" class="font-semibold text-[.875rem] block text-truncate project-list-title">Data Export and Reporting</a>
                                            <span class="text-[#8c9097] dark:text-white/50 block text-[0.75rem]">Total <strong class="text-default">14/26</strong> tasks completed</span>
                                        </div>
                                        <div class="hs-dropdown ti-dropdown">
                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn btn-wave ti-btn-sm ti-btn-light !mb-0" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-eye-line align-middle me-1 inline-flex"></i>View</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-edit-line align-middle me-1 inline-flex"></i>Edit</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle inline-flex"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <div class="font-semibold mb-1">Team :</div>
                                                <div class="avatar-list-stacked">
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                        +1
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="font-semibold mb-1">Priority :</div>
                                                <span class="badge bg-info/10 text-info">Medium</span>
                                            </div>
                                        </div>
                                        <div class="font-semibold mb-1">Description :</div>
                                        <p class="text-[#8c9097] dark:text-white/50 mb-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi maiores similique tempore.</p>
                                        <div class="font-semibold mb-1">Status :</div>
                                        <div>
                                            <div></div>
                                            <div class="progress progress-xs progress-animate" >
                                                <div class="progress-bar bg-primary w-3/5"></div>
                                            </div>
                                            <div class="mt-1"><span class="text-primary font-semibold">60%</span> Completed</div>
                                        </div>
                                    </div>
                                    <div class="box-footer flex items-center justify-between">
                                        <div>
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Assigned Date :</span>
                                            <span class="font-semibold block">29,May 2023</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Due Date :</span>
                                            <span class="font-semibold block">08,Jun 2023</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-4 md:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header items-center !justify-start flex-wrap !flex">
                                        <div class="me-2">
                                            <span class="avatar avatar-rounded p-1 bg-success/10 text-success">
                                                <img src="{{asset('build/assets/images/company-logos/10.png')}}" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);" class="font-semibold text-[.875rem] block text-truncate project-list-title">Activity Log and Audit Trail</a>
                                            <span class="text-[#8c9097] dark:text-white/50 block text-[0.75rem]">Total <strong class="text-default">28/64</strong> tasks completed</span>
                                        </div>
                                        <div class="hs-dropdown ti-dropdown">
                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn btn-wave ti-btn-sm ti-btn-light !mb-0" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-eye-line align-middle me-1 inline-flex"></i>View</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-edit-line align-middle me-1 inline-flex"></i>Edit</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle inline-flex"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <div class="font-semibold mb-1">Team :</div>
                                                <div class="avatar-list-stacked">
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img">
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="font-semibold mb-1">Priority :</div>
                                                <span class="badge bg-success/10 text-success">Low</span>
                                            </div>
                                        </div>
                                        <div class="font-semibold mb-1">Description :</div>
                                        <p class="text-[#8c9097] dark:text-white/50 mb-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi maiores similique tempore.</p>
                                        <div class="font-semibold mb-1">Status :</div>
                                        <div>
                                            <div></div>
                                            <div class="progress progress-xs progress-animate" >
                                                <div class="progress-bar bg-primary w-[45%]"></div>
                                            </div>
                                            <div class="mt-1"><span class="text-primary font-semibold">45%</span> Completed</div>
                                        </div>
                                    </div>
                                    <div class="box-footer flex items-center justify-between">
                                        <div>
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Assigned Date :</span>
                                            <span class="font-semibold block">04,Jun 2023</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Due Date :</span>
                                            <span class="font-semibold block">15,Jun 2023</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-4 md:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header items-center !justify-start flex-wrap !flex">
                                        <div class="me-2">
                                            <span class="avatar avatar-rounded p-1 bg-info/10 text-info">
                                                <img src="{{asset('build/assets/images/company-logos/9.png')}}" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);" class="font-semibold text-[.875rem] block text-truncate project-list-title">Role-Based Access Control (RBAC) Implementation</a>
                                            <span class="text-[#8c9097] dark:text-white/50 block text-[0.75rem]">Total <strong class="text-default">86/122</strong> tasks completed</span>
                                        </div>
                                        <div class="hs-dropdown ti-dropdown">
                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn btn-wave ti-btn-sm ti-btn-light !mb-0" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-eye-line align-middle me-1 inline-flex"></i>View</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-edit-line align-middle me-1 inline-flex"></i>Edit</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle inline-flex"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <div class="font-semibold mb-1">Team :</div>
                                                <div class="avatar-list-stacked">
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/14.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                        +2
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="font-semibold mb-1">Priority :</div>
                                                <span class="badge bg-danger/10 text-danger">High</span>
                                            </div>
                                        </div>
                                        <div class="font-semibold mb-1">Description :</div>
                                        <p class="text-[#8c9097] dark:text-white/50 mb-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi maiores similique tempore.</p>
                                        <div class="font-semibold mb-1">Status :</div>
                                        <div>
                                            <div></div>
                                            <div class="progress progress-xs progress-animate" >
                                                <div class="progress-bar bg-primary w-[65%]"></div>
                                            </div>
                                            <div class="mt-1"><span class="text-primary font-semibold">65%</span> Completed</div>
                                        </div>
                                    </div>
                                    <div class="box-footer flex items-center justify-between">
                                        <div>
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Assigned Date :</span>
                                            <span class="font-semibold block">24,Jun 2023</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Due Date :</span>
                                            <span class="font-semibold block">05,Jul 2023</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-4 md:col-span-6 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header items-center !justify-start flex-wrap !flex">
                                        <div class="me-2">
                                            <span class="avatar avatar-rounded p-1 bg-teal/10">
                                                <img src="{{asset('build/assets/images/company-logos/6.png')}}" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-grow">
                                            <a href="javascript:void(0);" class="font-semibold text-[.875rem] block text-truncate project-list-title">Customizable Themes and Layouts</a>
                                            <span class="text-[#8c9097] dark:text-white/50 block text-[0.75rem]">Total <strong class="text-default">20/26</strong> tasks completed</span>
                                        </div>
                                        <div class="hs-dropdown ti-dropdown">
                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn btn-wave ti-btn-sm ti-btn-light !mb-0" aria-expanded="false">
                                                <i class="fe fe-more-vertical"></i>
                                            </a>
                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-eye-line align-middle me-1 inline-flex"></i>View</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-edit-line align-middle me-1 inline-flex"></i>Edit</a></li>
                                                <li><a class="ti-dropdown-item" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle inline-flex"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <div class="font-semibold mb-1">Team :</div>
                                                <div class="avatar-list-stacked">
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img">
                                                    </span>
                                                    <span class="avatar avatar-sm avatar-rounded">
                                                        <img src="{{asset('build/assets/images/faces/16.jpg')}}" alt="img">
                                                    </span>
                                                    <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                        +2
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <div class="font-semibold mb-1">Priority :</div>
                                                <span class="badge bg-info/10 text-info">Medium</span>
                                            </div>
                                        </div>
                                        <div class="font-semibold mb-1">Description :</div>
                                        <p class="text-[#8c9097] dark:text-white/50 mb-3">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Nisi maiores similique tempore.</p>
                                        <div class="font-semibold mb-1">Status :</div>
                                        <div>
                                            <div></div>
                                            <div class="progress progress-xs progress-animate" >
                                                <div class="progress-bar bg-primary w-3/4"></div>
                                            </div>
                                            <div class="mt-1"><span class="text-primary font-semibold">75%</span> Completed</div>
                                        </div>
                                    </div>
                                    <div class="box-footer flex items-center justify-between">
                                        <div>
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Assigned Date :</span>
                                            <span class="font-semibold block">20,Jun 2023</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem] block">Due Date :</span>
                                            <span class="font-semibold block">18,Jul 2023</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End::row-2 -->
                        
                        <nav aria-label="Page navigation">
                            <ul class="ti-pagination ltr:float-right rtl:float-left mb-4">
                                <li class="page-item disabled"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">Previous</a></li>
                                <li class="page-item"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">1</a></li>
                                <li class="page-item"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">2</a></li>
                                <li class="page-item"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">Next</a></li>
                            </ul>
                        </nav>

@endsection

@section('scripts')

        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

@endsection