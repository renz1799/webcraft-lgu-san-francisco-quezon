@extends('layouts.master')

@section('styles')
 
        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">

        <!-- Filepond CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond/filepond.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css')}}">

        <!-- Dragula Cards CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/dragula/dragula.min.css')}}">

        <!-- FlatPickr CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/flatpickr/flatpickr.min.css')}}">
      
@endsection

@section('content')
 
                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Kanban Board</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Task
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                    Kanban Board
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start:: row-1 -->
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-body p-4">
                                        <div class="md:flex items-center justify-between flex-wrap gap-4">
                                            <div class="grid grid-cols-12 gap-2 md:w-[30%]">
                                                <div class="xl:col-span-5 col-span-12">
                                                    <a href="javascript:void(0);" class="hs-dropdown-toggle  ti-btn btn-wave bg-primary text-white !font-medium " data-hs-overlay="#add-board"><i class="ri-add-line !text-[1rem]"></i>New Board
                                                    </a>
                                                </div>
                                                <div class="xl:col-span-7 col-span-12">
                                                <select class="form-control w-full !rounded-md" data-trigger name="choices-single-default" id="choices-single-default">
                                                    <option value="">Sort By</option>
                                                    <option value="Newest">Newest</option>
                                                    <option value="Date Added">Date Added</option>
                                                    <option value="Type">Type</option>
                                                    <option value="A - Z">A - Z</option>
                                                </select>
                                                </div>
                                            </div>
                                            <div class="avatar-list-stacked my-3 md:my-0">
                                                <span class="avatar avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                </span>
                                                <span class="avatar avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img">
                                                </span>
                                                <a class="avatar bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                    +8
                                                </a>
                                            </div>
                                            <div class="flex" role="search">
                                                <input class="form-control w-full !rounded-sm me-2" type="search" placeholder="Search" aria-label="Search">
                                                <button class="ti-btn ti-btn-light !mb-0" type="submit">Search</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End:: row-1 -->

                        <!-- Start::row-2 -->
                        <div class="ynex-kanban-board text-defaulttextcolor dark:text-defaulttextcolor/70 text-defaultsize">
                            <div class="kanban-tasks-type new">
                                <div class="mb-4">
                                    <div class="flex justify-between items-center">
                                        <span class="block font-semibold text-[.9375rem]">NEW - 04</span>
                                        <div>
                                            <a href="javascript:void(0);" class="hs-dropdown-toggle ti-btn btn-wave !py-1 !px-2 !font-medium !text-[0.75rem] bg-white dark:bg-bodybg text-default border-0" data-hs-overlay="#add-task"><i class="ri-add-line"></i>Add Task
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="kanban-tasks " id="new-tasks">
                                    <div id="new-tasks-draggable" data-view-btn="new-tasks">
                                        <div class="box kanban-tasks new">
                                            <div class="box-body !p-0">
                                                <div class="p-4 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div class="inline-flex"><i class="ri-time-line me-1 align-middle"></i>Created - 28 May</div>
                                                        <div>2 days left</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default">#SPK - 11</span><span class="ms-1 badge bg-primary/10 text-primary">UI/UX</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content !mt-1">
                                                        <h6 class="font-semibold mb-1 text-[.9375rem]">New Dashboard design.</h6>
                                                        <div class="kanban-task-description">Lorem ipsum dolor sit amet consectetur adipisicing elit, Nulla soluta consectetur sit amet elit dolor sit amet.</div>
                                                    </div>
                                                </div>
                                                <div class="p-4 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">12</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">02</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box kanban-tasks new">
                                            <div class="box-body !p-0">
                                                <div class="p-4 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div><i class="ri-time-line align-middle"></i>Created - 30 May</div>
                                                        <div>2 days left</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default">#SPK - 05</span><span class="ms-1 badge bg-secondary/10 text-secondary">Marketing</span><span class="ms-1 badge bg-warning/10 text-warning">Finance</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content !mt-1">
                                                        <h6 class="font-semibold mb-1 text-[.9375rem]">Marketing next projects.</h6>
                                                        <div class="kanban-task-description">Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta </div>
                                                    </div>
                                                </div>
                                                <div class="p-3 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">40</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">08</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box kanban-tasks new">
                                            <div class="box-body !p-0">
                                                <div class="p-4 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div class="inline-flex"><i class="ri-time-line me-1 align-middle"></i>Created - 02 Jun</div>
                                                        <div>1 days left</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default">#SPK - 08</span><span class="ms-1 badge bg-success/10 text-success">Designing</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content mt-2">
                                                        <div class="task-image mt-2">
                                                            <img src="{{asset('build/assets/images/media/media-36.jpg')}}" class="img-fluid rounded kanban-image" alt="">
                                                        </div>
                                                        <h6 class="font-semibold mb-0 mt-2 text-[1rem]">Design multi usage landing.</h6>
                                                    </div>
                                                </div>
                                                <div class="p-4 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">16</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">28</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid mt-4">
                                    <button type="button" class="ti-btn btn-wave ti-btn-primary">View More</button>
                                </div>
                            </div>
                            <div class="kanban-tasks-type todo">
                                <div class="mb-4">
                                    <div class="flex justify-between items-center">
                                        <span class="block font-semibold text-[.9375rem]">TODO - 36</span>
                                        <div>
                                            <a href="javascript:void(0);" class="hs-dropdown-toggle ti-btn btn-wave !py-1 !px-2 !font-medium  !text-[0.75rem] bg-white dark:bg-bodybg text-default border-0" data-hs-overlay="#add-task"><i class="ri-add-line"></i>Add Task
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="kanban-tasks" id="todo-tasks">
                                    <div id="todo-tasks-draggable" data-view-btn="todo-tasks">
                                        <div class="box kanban-tasks todo">
                                            <div class="box-body !p-0">
                                                <div class="p-4 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div class="inline-flex"><i class="ri-time-line me-1 align-middle"></i>Created - 01 Jun</div>
                                                        <div>10 days left</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default">#SPK - 07</span><span class="ms-1 badge bg-pinkmain/10">Admin</span><span class="ms-1 badge bg-light text-default">Authentication</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content mt-2">
                                                        <h6 class="font-semibold mb-1 text-[.9375rem]">Adding Authentication Pages.</h6>
                                                        <div class="kanban-task-description">Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta </div>
                                                    </div>
                                                </div>
                                                <div class="p-3 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">06</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">02</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/14.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box kanban-tasks todo">
                                            <div class="box-body p-0">
                                                <div class="p-3 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div class="inline-flex"><i class="ri-time-line me-1 align-middle"></i>Created - 05 Jun</div>
                                                        <div>14 days left</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default">#SPK - 15</span><span class="ms-1 badge bg-success/10">Planning</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content mt-2">
                                                        <div class="task-image mt-2">
                                                            <img src="{{asset('build/assets/images/media/media-41.jpg')}}" class="img-fluid rounded kanban-image" alt="">
                                                        </div>
                                                        <h6 class="font-semibold mb-0 mt-2">New Project Discussion.</h6>
                                                    </div>
                                                </div>
                                                <div class="p-3 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">17</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">06</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid mt-4">
                                    <button type="button" class="ti-btn btn-wave ti-btn-primary">View More</button>
                                </div>
                            </div>
                            <div class="kanban-tasks-type in-progress">
                                <div class="mb-4">
                                    <div class="flex justify-between items-center">
                                        <span class="block font-semibold text-[.9375rem]">ON GOING - 25</span>
                                        <div>
                                            <a href="javascript:void(0);" class="hs-dropdown-toggle ti-btn btn-wave !py-1 !px-2 !font-medium !text-[0.75rem] bg-white dark:bg-bodybg text-default border-0" data-hs-overlay="#add-task"><i class="ri-add-line"></i>Add Task
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="kanban-tasks" id="inprogress-tasks">
                                    <div id="inprogress-tasks-draggable" data-view-btn="inprogress-tasks">
                                        <div class="box kanban-tasks todo">
                                            <div class="box-body !p-0">
                                                <div class="p-4 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div class="inline-flex"><i class="ri-time-line me-1 align-middle"></i>Created - 02 Jun</div>
                                                        <div>5 days left</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default">#SPK - 13</span><span class="ms-1 badge bg-primary/10 text-primary">UI Design</span><span class="ms-1 badge bg-danger/10 text-danger">Development</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content mt-2">
                                                        <h6 class="font-semibold mb-1 text-[.9375rem]">Create Calendar &amp; Mail pages.</h6>
                                                        <div class="kanban-task-description">Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta </div>
                                                    </div>
                                                </div>
                                                <div class="p-4 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">05</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">13</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box kanban-tasks todo">
                                            <div class="box-body !p-0">
                                                <div class="p-4 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div class="inline-flex"><i class="ri-time-line me-1 align-middle"></i>Created - 03 Jun</div>
                                                        <div>12 days left</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default ms-1">#SPK - 09</span><span class="ms-1 badge bg-teal/10 text-teal">Product</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content mt-2">
                                                        <h6 class="font-semibold mb-1 text-[.9375rem]">Project design Figma,Sketch.</h6>
                                                        <div class="kanban-task-description">Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta </div>
                                                    </div>
                                                </div>
                                                <div class="p-3 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">02</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">0</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid mt-4">
                                    <button type="button" class="ti-btn btn-wave ti-btn-primary">View More</button>
                                </div>
                            </div>
                            <div class="kanban-tasks-type inreview">
                                <div class="mb-4">
                                    <div class="flex justify-between items-center">
                                        <span class="block font-semibold text-[.9375rem]">IN REVIEW - 02</span>
                                        <div>
                                            <a href="javascript:void(0);" class="hs-dropdown-toggle ti-btn btn-wave !py-1 !px-2 !font-medium !text-[0.75rem] bg-white dark:bg-bodybg text-default border-0" data-hs-overlay="#add-task"><i class="ri-add-line"></i>Add Task
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="kanban-tasks" id="inreview-tasks">
                                    <div id="inreview-tasks-draggable" data-view-btn="inreview-tasks">
                                        <div class="box kanban-tasks interview">
                                            <div class="box-body !p-0">
                                                <div class="p-4 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div class="inline-flex"><i class="ri-time-line me-1 align-middle"></i>Created - 05 Jun</div>
                                                        <div>14 days left</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default">#SPK - 15</span><span class="ms-1 badge bg-purplemain/10 text-purplemain">Review</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content mt-2">
                                                        <div class="task-image mt-2">
                                                            <img src="{{asset('build/assets/images/media/media-43.jpg')}}" class="img-fluid rounded kanban-image" alt="">
                                                        </div>
                                                        <h6 class="font-semibold mb-0 mt-2">Design Architecture strategy.</h6>
                                                    </div>
                                                </div>
                                                <div class="p-3 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">09</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">35</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/7.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid mt-4">
                                    <button type="button" class="ti-btn btn-wave ti-btn-primary">View More</button>
                                </div>
                            </div>
                            <div class="kanban-tasks-type completed">
                                <div class="mb-4">
                                    <div class="flex justify-between items-center">
                                        <span class="block font-semibold text-[.9375rem]">COMPLETED - 36</span>
                                        <div>
                                            <a href="javascript:void(0);" class="hs-dropdown-toggle ti-btn btn-wave !py-1 !px-2 !font-medium !text-[0.75rem] bg-white dark:bg-bodybg text-default border-0" data-hs-overlay="#add-task"><i class="ri-add-line"></i>Add Task
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="kanban-tasks" id="completed-tasks">
                                    <div id="completed-tasks-draggable" data-view-btn="completed-tasks">
                                        <div class="box kanban-tasks completed">
                                            <div class="box-body !p-0">
                                                <div class="p-4 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div class="inline-flex"><i class="ri-time-line me-1 align-middle"></i>Created - 12 Jun</div>
                                                        <div class="text-success"><i class="ri-check-fill me-1 align-middle"></i>Done</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default">#SPK - 04</span><span class="ms-1 badge bg-success/10 text-success">UI/UX</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content mt-2">
                                                        <h6 class="font-semibold mb-1 text-[.9375rem]">Sash project update.</h6>
                                                        <div class="kanban-task-description">Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta </div>
                                                    </div>
                                                </div>
                                                <div class="p-4 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">18</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">03</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box kanban-tasks completed">
                                            <div class="box-body !p-0">
                                                <div class="p-4 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div class="inline-flex"><i class="ri-time-line me-1 align-middle"></i>Created - 10 Jun</div>
                                                        <div class="text-success"><i class="ri-check-fill me-1 align-middle"></i>Done</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default">#SPK - 10</span><span class="ms-1 badge bg-info/10 text-info">Development</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content mt-2">
                                                        <h6 class="font-semibold mb-1 text-[.9375rem]">React JS new version update.</h6>
                                                        <div class="kanban-task-description">Lorem ipsum dolor sit amet consectetur adipisicing elit. Nulla soluta </div>
                                                    </div>
                                                </div>
                                                <div class="p-4 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">22</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">12</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                            </span>
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box kanban-tasks completed">
                                            <div class="box-body !p-0">
                                                <div class="p-4 kanban-board-head">
                                                    <div class="flex text-[#8c9097] dark:text-white/50 justify-between mb-1 text-[.75rem] font-semibold">
                                                        <div class="inline-flex"><i class="ri-time-line me-1 align-middle"></i>Created - 07 Jun</div>
                                                        <div class="text-success"><i class="ri-check-fill me-1 align-middle"></i>Done</div>
                                                    </div>
                                                    <div class="flex items-center justify-between">
                                                        <div class="task-badges"><span class="badge bg-light text-default">#SPK - 16</span><span class="ms-1 badge bg-primary/10 text-primary">Discussion</span></div>
                                                        <div class="hs-dropdown ti-dropdown ltr:[--placement:bottom-right] rtl:[--placement:bottom-left]">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="ti-btn ti-btn-icon ti-btn-sm ti-btn-light" aria-expanded="false">
                                                                <i class="fe fe-more-vertical"></i>
                                                            </a>
                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-eye-line me-1 align-middle"></i>View</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-delete-bin-line me-1 align-middle"></i>Delete</a></li>
                                                                <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium !inline-flex" href="javascript:void(0);"><i class="ri-edit-line me-1 align-middle"></i>Edit</a></li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                    <div class="kanban-content mt-2">
                                                        <div class="task-image mt-2">
                                                            <img src="{{asset('build/assets/images/media/media-86.svg')}}" class="img-fluid rounded kanban-image bg-light" alt="">
                                                        </div>
                                                        <h6 class="font-semibold mb-0 mt-2">Project discussion with client.</h6>
                                                    </div>
                                                </div>
                                                <div class="p-4 border-t dark:border-defaultborder/10 border-dashed">
                                                    <div class="flex items-center justify-between">
                                                        <div class="inline-flex items-center">
                                                            <a href="javascript:void(0);" class="inline-flex items-center me-2 text-primary">
                                                                <span class="me-1"><i class="ri-thumb-up-fill align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">11</span>
                                                            </a>
                                                            <a href="javascript:void(0);" class="inline-flex items-center text-[#8c9097] dark:text-white/50">
                                                                <span class="me-1"><i class="ri-message-2-line align-middle font-normal"></i></span><span class="font-semibold text-[.75rem]">05</span>
                                                            </a>
                                                        </div>
                                                        <div class="avatar-list-stacked">
                                                            <span class="avatar avatar-sm avatar-rounded">
                                                                <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="img">
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid mt-4">
                                    <button type="button" class="ti-btn btn-wave ti-btn-primary">View More</button>
                                </div>
                            </div>
                        </div>
                        <!--End::row-2 -->

                        <!-- Start::add board modal -->
                        <div id="add-board" class="hs-overlay hidden ti-modal">
                            <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
                              <div class="ti-modal-content">
                                <div class="ti-modal-header">
                                    <h6 class="modal-title text-[1rem] !text-default dark:text-defaulttextcolor/70 font-semibold">Add Board</h6>
                                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold" data-hs-overlay="#add-board">
                                        <span class="sr-only">Close</span>
                                        <i class="ri-close-line"></i>
                                      </button>
                                </div>
                                <div class="ti-modal-body px-6">
                                    <div class="grid grid-cols-12 gy-2">
                                        <div class="xl:col-span-12 col-span-12">
                                            <label for="task-name" class="form-label">Task Name</label>
                                            <input type="text" class="form-control w-full !rounded-md" id="task-name" placeholder="Task Name">
                                        </div>
                                    </div>
                                </div>
                                <div class="ti-modal-footer">
                                    <button type="button"
                                    class="hs-dropdown-toggle ti-btn  ti-btn-light align-middle"
                                    data-hs-overlay="#add-board">
                                    Cancel
                                  </button>
                                <button type="button" class="ti-btn btn-wave bg-primary text-white !font-medium">Add Task</button>
                                </div>
                              </div>
                            </div>
                        </div>
                        <!-- End::add board modal -->

                        <!-- Start::add task modal -->
                        <div id="add-task" class="hs-overlay hidden ti-modal">
                            <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
                              <div class="ti-modal-content">
                                <div class="ti-modal-header">
                                    <h6 class="modal-title text-[1rem] font-semibold text-default dark:text-defaulttextcolor/70" id="mail-ComposeLabel">Add Task</h6>
                                      <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold" data-hs-overlay="#add-task">
                                        <span class="sr-only">Close</span>
                                        <i class="ri-close-line"></i>
                                      </button>
                                </div>
                                <div class="ti-modal-body px-4 !overflow-visible">
                                    <div class="grid grid-cols-12 gap-6">
                                        <div class="xl:col-span-6 col-span-12">
                                            <label for="task-name" class="form-label">Task Name</label>
                                            <input type="text" class="form-control w-full !rounded-md" id="task-name2" placeholder="Task Name">
                                        </div>
                                        <div class="xl:col-span-6 col-span-12">
                                            <label for="task-id" class="form-label">Task ID</label>
                                            <input type="text" class="form-control w-full !rounded-md" id="task-id" placeholder="Task ID">
                                        </div>
                                        <div class="xl:col-span-12 col-span-12">
                                            <label for="text-area" class="form-label">Task Description</label>
                                            <textarea class="form-control w-full !rounded-md" id="text-area" rows="2" placeholder="Write Description"></textarea>
                                        </div>
                                        <div class="xl:col-span-12 col-span-12">
                                            <label for="text-area" class="form-label">Task Images</label>
                                            <input type="file" class="multiple-filepond" name="filepond" multiple data-allow-reorder="true" data-max-file-size="3MB" data-max-files="6">
                                        </div>
                                        <div class="xl:col-span-12 col-span-12">
                                            <label class="form-label">Assigned To</label>
                                            <select class="form-control" name="choices-multiple-remove-button1" id="choices-multiple-remove-button1" multiple>
                                                <option value="Choice 1">Angelina May</option>
                                                <option value="Choice 2">Kiara advain</option>
                                                <option value="Choice 3">Hercules Jhon</option>
                                                <option value="Choice 4">Mayor Kim</option>
                                            </select>
                                        </div>
                                        <div class="xl:col-span-6 col-span-12">
                                            <label class="form-label">Target Date</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-muted !rounded-e-none"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control w-full !rounded-e-md" id="targetDate" placeholder="Choose date and time">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="xl:col-span-6 col-span-12">
                                            <label class="form-label">Tags</label>
                                            <select class="form-control w-full !rounded-md" name="choices-multiple-remove-button2" id="choices-multiple-remove-button2" multiple>
                                                <option value="">Select Tag</option>
                                                <option value="UI/UX">UI/UX</option>
                                                <option value="Marketing">Marketing</option>
                                                <option value="Finance">Finance</option>
                                                <option value="Designing">Designing</option>
                                                <option value="Admin">Admin</option>
                                                <option value="Authentication">Authentication</option>
                                                <option value="Product">Product</option>
                                                <option value="Development">Development</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="ti-modal-footer">
                                    <button type="button"
                                    class="hs-dropdown-toggle ti-btn  ti-btn-light align-middle"
                                    data-hs-overlay="#add-task">
                                    Cancel
                                  </button>
                                    <button type="button" class="ti-btn btn-wave bg-primary text-white !font-medium">Create</button>
                                </div>
                              </div>
                            </div>
                        </div>
                        <!-- End::add task modal -->

@endsection

@section('scripts')

        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

        <!-- Filepond JS -->
        <script src="{{asset('build/assets/libs/filepond/filepond.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js')}}"></script>

        <!-- Flat Picker JS -->
        <script src="{{asset('build/assets/libs/flatpickr/flatpickr.min.js')}}"></script>

        <!-- Dragula JS -->
        <script src="{{asset('build/assets/libs/dragula/dragula.min.js')}}"></script>

        <!-- Internal Task  JS -->
        @vite('resources/assets/js/task-kanban-board.js')


@endsection