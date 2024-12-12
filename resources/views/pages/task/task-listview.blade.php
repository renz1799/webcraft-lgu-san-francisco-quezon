@extends('layouts.master')

@section('styles')
 
        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">

        <!-- Tom Select Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/tom-select/css/tom-select.default.min.css')}}">

        <!-- Flat Picker JS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/flatpickr/flatpickr.min.css')}}">

@endsection

@section('content')

                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> List View</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Task
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                    List View
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-9 col-span-12">
                                <div class="box">
                                    <div class="box-header justify-between">
                                        <div class="box-title">
                                            Total Tasks
                                        </div>
                                        <div class="flex">
                                            <button type="button" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full !py-1 !px-2 !text-[0.75rem]" data-hs-overlay="#create-task">
                                                <i class="ri-add-line font-semibold align-middle"></i> Create Task
                                              </button>
                                            <div id="create-task" class="hs-overlay hidden ti-modal">
                                                <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out min-h-[calc(100%-3.5rem)] flex items-center">
                                                  <div class="ti-modal-content">
                                                    <div class="ti-modal-header">
                                                        <h6 class="modal-title" id="staticBackdropLabel2">Add Task
                                                        </h6>
                                                      <button type="button" class="hs-dropdown-toggle ti-modal-close-btn" data-hs-overlay="#create-task">
                                                        <span class="sr-only">Close</span>
                                                        <svg class="w-3.5 h-3.5" width="8" height="8" viewBox="0 0 8 8" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                          <path d="M0.258206 1.00652C0.351976 0.912791 0.479126 0.860131 0.611706 0.860131C0.744296 0.860131 0.871447 0.912791 0.965207 1.00652L3.61171 3.65302L6.25822 1.00652C6.30432 0.958771 6.35952 0.920671 6.42052 0.894471C6.48152 0.868271 6.54712 0.854471 6.61352 0.853901C6.67992 0.853321 6.74572 0.865971 6.80722 0.891111C6.86862 0.916251 6.92442 0.953381 6.97142 1.00032C7.01832 1.04727 7.05552 1.1031 7.08062 1.16454C7.10572 1.22599 7.11842 1.29183 7.11782 1.35822C7.11722 1.42461 7.10342 1.49022 7.07722 1.55122C7.05102 1.61222 7.01292 1.6674 6.96522 1.71352L4.31871 4.36002L6.96522 7.00648C7.05632 7.10078 7.10672 7.22708 7.10552 7.35818C7.10442 7.48928 7.05182 7.61468 6.95912 7.70738C6.86642 7.80018 6.74102 7.85268 6.60992 7.85388C6.47882 7.85498 6.35252 7.80458 6.25822 7.71348L3.61171 5.06702L0.965207 7.71348C0.870907 7.80458 0.744606 7.85498 0.613506 7.85388C0.482406 7.85268 0.357007 7.80018 0.264297 7.70738C0.171597 7.61468 0.119017 7.48928 0.117877 7.35818C0.116737 7.22708 0.167126 7.10078 0.258206 7.00648L2.90471 4.36002L0.258206 1.71352C0.164476 1.61976 0.111816 1.4926 0.111816 1.36002C0.111816 1.22744 0.164476 1.10028 0.258206 1.00652Z" fill="currentColor"/>
                                                        </svg>
                                                      </button>
                                                    </div>
                                                    <div class="ti-modal-body">
                                                        <div class="grid grid-cols-12 gap-2">
                                                            <div class="xl:col-span-6 col-span-12">
                                                                <label for="task-name" class="form-label">Task Name</label>
                                                                <input type="text" class="form-control" id="task-name" placeholder="Task Name">
                                                            </div>
                                                            <div class="xl:col-span-6 col-span-12">
                                                                <label for="task-id" class="form-label">Task ID</label>
                                                                <input type="text" class="form-control" id="task-id" placeholder="Task ID">
                                                            </div>
                                                            <div class="xl:col-span-6 col-span-12">
                                                                <label class="form-label">Assigned Date</label>
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                        <input type="text" class="form-control" id="assignedDate" placeholder="Choose date and time">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="xl:col-span-6 col-span-12">
                                                                <label class="form-label">Due Date</label>
                                                                <div class="form-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                                        <input type="text" class="form-control" id="dueDate" placeholder="Choose date and time">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="xl:col-span-6 col-span-12">
                                                                <label class="form-label">Status</label>
                                                                <select class="form-control" data-trigger id="choices-single-default">
                                                                    <option value="New">New</option>
                                                                    <option value="Completed">Completed</option>
                                                                    <option value="Inprogress">Inprogress</option>
                                                                    <option value="Pending">Pending</option>
                                                                </select>
                                                            </div>
                                                            <div class="xl:col-span-6 col-span-12">
                                                                <label class="form-label">Priority</label>
                                                                <select class="form-control" data-trigger id="choices-single-default1">
                                                                    <option value="High">High</option>
                                                                    <option value="Medium">Medium</option>
                                                                    <option value="Low">Low</option>
                                                                </select>
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
                                                        </div>
                                                    </div>
                                                    <div class="ti-modal-footer">
                                                      <button type="button" class="hs-dropdown-toggle ti-btn ti-btn-light" data-hs-overlay="#create-task">
                                                        Cancel
                                                      </button>
                                                      <a class="ti-btn btn-wave ti-btn-primary-full" href="javascript:void(0);">
                                                        Add Task
                                                      </a>
                                                    </div>
                                                  </div>
                                                </div>
                                            </div>
                                            <div class="hs-dropdown ti-dropdown ms-2">
                                                <button type="button" aria-label="button" class="ti-btn btn-wave ti-btn-secondary ti-btn-sm" aria-expanded="false">
                                                    <i class="ti ti-dots-vertical"></i>
                                                </button>
                                                <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                    <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block" href="javascript:void(0);">New Tasks</a></li>
                                                    <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block" href="javascript:void(0);">Pending Tasks</a></li>
                                                    <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block" href="javascript:void(0);">Completed Tasks</a></li>
                                                    <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block" href="javascript:void(0);">Inprogress Tasks</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="table-responsive">
                                            <table class="table whitespace-nowrap table-bordered min-w-full">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">
                                                            <input class="form-check-input check-all" type="checkbox" id="all-tasks" value="" aria-label="...">
                                                        </th>
                                                        <th scope="col" class="text-start">Task</th>
                                                        <th scope="col" class="text-start">Task ID</th>
                                                        <th scope="col" class="text-start">Assigned Date</th>
                                                        <th scope="col" class="text-start">Status</th>
                                                        <th scope="col" class="text-start">Due Date</th>
                                                        <th scope="col" class="text-start">Priority</th>
                                                        <th scope="col" class="text-start">Assigned To</th>
                                                        <th scope="col" class="text-start">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border border-defaultborder task-list">
                                                        <td class="task-checkbox"><input class="form-check-input" type="checkbox" value="" aria-label="..."></td>
                                                        <td>
                                                            <span class="font-semibold">Design New Landing Page</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">SPK - 01</span>
                                                        </td>
                                                        <td>
                                                            02-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold text-primary">New</span>
                                                        </td>
                                                        <td>
                                                            10-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary/10 text-secondary">Medium</span>
                                                        </td>
                                                        <td>
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
                                                                <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                                    +2
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <i class="ri-edit-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                    Edit
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm task-delete-btn">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                      Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder task-list">
                                                        <td class="task-checkbox"><input class="form-check-input" type="checkbox" value="" aria-label="..." checked></td>
                                                        <td>
                                                            <span class="font-semibold">New Project Buleprint</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">SPK - 04</span>
                                                        </td>
                                                        <td>
                                                            05-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold text-secondary">Inprogress</span>
                                                        </td>
                                                        <td>
                                                            15-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-danger/10 text-danger">High</span>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                                </span>
                                                                <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                                    +4
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <i class="ri-edit-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                    Edit
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm task-delete-btn">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                      Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder task-list">
                                                        <td class="task-checkbox"><input class="form-check-input" type="checkbox" value="" aria-label="..."></td>
                                                        <td>
                                                            <span class="font-semibold">Server Side Validation</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">SPK - 11</span>
                                                        </td>
                                                        <td>
                                                            12-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold text-warning">Pending</span>
                                                        </td>
                                                        <td>
                                                            16-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success/10 text-success">Low</span>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="img">
                                                                </span>
                                                                <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                                    +5
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <i class="ri-edit-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                    Edit
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm task-delete-btn">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                      Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder task-list">
                                                        <td class="task-checkbox"><input class="form-check-input" type="checkbox" value="" aria-label="..."></td>
                                                        <td>
                                                            <span class="font-semibold">New Plugin Development</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">SPK - 24</span>
                                                        </td>
                                                        <td>
                                                            08-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold text-success">Completed</span>
                                                        </td>
                                                        <td>
                                                            17-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success/10 text-success">Low</span>
                                                        </td>
                                                        <td>
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
                                                                <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                                    +2
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <i class="ri-edit-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                    Edit
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm task-delete-btn">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                      Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder task-list">
                                                        <td class="task-checkbox"><input class="form-check-input" type="checkbox" value="" aria-label="..." checked></td>
                                                        <td>
                                                            <span class="font-semibold">Designing New Authentication Page</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">SPK - 16</span>
                                                        </td>
                                                        <td>
                                                            03-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold text-secondary">Inprogress</span>
                                                        </td>
                                                        <td>
                                                            08-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary/10 text-secondary">Medium</span>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="img">
                                                                </span>
                                                                <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                                    +3
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <i class="ri-edit-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                    Edit
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm task-delete-btn">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                      Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder task-list">
                                                        <td class="task-checkbox"><input class="form-check-input" type="checkbox" value="" aria-label="..." checked></td>
                                                        <td>
                                                            <span class="font-semibold">Documentation For New Template</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">SPK - 07</span>
                                                        </td>
                                                        <td>
                                                            12-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold text-primary">New</span>
                                                        </td>
                                                        <td>
                                                            25-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-danger/10 text-danger">High</span>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                                </span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <i class="ri-edit-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                    Edit
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm task-delete-btn">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                      Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder task-list">
                                                        <td class="task-checkbox"><input class="form-check-input" type="checkbox" value="" aria-label="..."></td>
                                                        <td>
                                                            <span class="font-semibold">Updating Old UI</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">SPK - 13</span>
                                                        </td>
                                                        <td>
                                                            06-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold text-success">Completed</span>
                                                        </td>
                                                        <td>
                                                            12-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success/10 text-success">Low</span>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="img">
                                                                </span>
                                                                <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                                    +1
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <i class="ri-edit-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                    Edit
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm task-delete-btn">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                      Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder task-list">
                                                        <td class="task-checkbox"><input class="form-check-input" type="checkbox" value="" aria-label="..." checked></td>
                                                        <td>
                                                            <span class="font-semibold">Developing New Events In Plugins</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">SPK - 20</span>
                                                        </td>
                                                        <td>
                                                            14-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold text-warning">Pending</span>
                                                        </td>
                                                        <td>
                                                            19-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-danger/10 text-danger">High</span>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="img">
                                                                </span>
                                                                <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                                    +2
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <i class="ri-edit-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                    Edit
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm task-delete-btn">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                      Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder task-list">
                                                        <td class="task-checkbox"><input class="form-check-input" type="checkbox" value="" aria-label="..."></td>
                                                        <td>
                                                            <span class="font-semibold">Fixing Minor Ui Issues</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">SPK - 26</span>
                                                        </td>
                                                        <td>
                                                            11-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold text-success">Completed</span>
                                                        </td>
                                                        <td>
                                                            18-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-secondary/10 text-secondary">Medium</span>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/14.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="img">
                                                                </span>
                                                                <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                                    +1
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <i class="ri-edit-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                    Edit
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm task-delete-btn">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                      Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-defaultborder task-list">
                                                        <td class="task-checkbox"><input class="form-check-input" type="checkbox" value="" aria-label="..."></td>
                                                        <td>
                                                            <span class="font-semibold">Designing Of New Ecommerce Website</span>
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold">SPK - 32</span>
                                                        </td>
                                                        <td>
                                                            03-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="font-semibold text-secondary">Inprogress</span>
                                                        </td>
                                                        <td>
                                                            09-06-2023
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-success/10 text-success">Low</span>
                                                        </td>
                                                        <td>
                                                            <div class="avatar-list-stacked">
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="img">
                                                                </span>
                                                                <span class="avatar avatar-sm avatar-rounded">
                                                                    <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="img">
                                                                </span>
                                                                <a class="avatar avatar-sm bg-primary avatar-rounded text-white" href="javascript:void(0);">
                                                                    +4
                                                                </a>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-primary ti-btn-sm">
                                                                    <i class="ri-edit-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                    Edit
                                                                    </span>
                                                                </button>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <button type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-danger ms-1 ti-btn-sm task-delete-btn">
                                                                    <i class="ri-delete-bin-5-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                      Delete
                                                                    </span>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <nav aria-label="Page navigation">
                                            <ul class="ti-pagination sm:justify-end justify-center mb-0">
                                                <li class="page-item disabled"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">Previous</a></li>
                                                <li class="page-item"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">1</a></li>
                                                <li class="page-item"><a class="page-link active px-3 py-[0.375rem]" href="javascript:void(0);">2</a></li>
                                                <li class="page-item"><a class="page-link px-3 py-[0.375rem]" href="javascript:void(0);">Next</a></li>
                                            </ul>
                                        </nav>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-3 col-span-12">
                                <div class="box">
                                    <div class="box-body !p-0">
                                        <div class="!p-6 border-b dark:border-defaultborder/10 border-dashed flex items-start">
                                            <div class="svg-icon-background bg-primary/10 me-6">
                                                <svg xmlns="http://www.w3.org/2000/svg" data-name="Layer 1" viewBox="0 0 24 24" class="svg-primary"><path d="M13,16H7a1,1,0,0,0,0,2h6a1,1,0,0,0,0-2ZM9,10h2a1,1,0,0,0,0-2H9a1,1,0,0,0,0,2Zm12,2H18V3a1,1,0,0,0-.5-.87,1,1,0,0,0-1,0l-3,1.72-3-1.72a1,1,0,0,0-1,0l-3,1.72-3-1.72a1,1,0,0,0-1,0A1,1,0,0,0,2,3V19a3,3,0,0,0,3,3H19a3,3,0,0,0,3-3V13A1,1,0,0,0,21,12ZM5,20a1,1,0,0,1-1-1V4.73L6,5.87a1.08,1.08,0,0,0,1,0l3-1.72,3,1.72a1.08,1.08,0,0,0,1,0l2-1.14V19a3,3,0,0,0,.18,1Zm15-1a1,1,0,0,1-2,0V14h2Zm-7-7H7a1,1,0,0,0,0,2h6a1,1,0,0,0,0-2Z"/></svg>
                                            </div>
                                            <div class="flex-grow">
                                                <h6 class="!mb-1 text-[0.75rem]">New Tasks
                                                    <span class="badge bg-primary text-white font-semibold ltr:float-right rtl:float-left">
                                                      12,345
                                                    </span>
                                                </h6>
                                                <div class="pb-0 mt-0">
                                                    <div>
                                                        <h4 class="text-[1.125rem] font-semibold mb-1"><span class="count-up" data-count="42">42</span><span class="text-muted ltr:float-right rtl:float-left text-[.6875rem] font-normal">Last Year</span></h4>
                                                        <p class="text-muted text-[.6875rem] mb-0 leading-none">
                                                            <span class="text-success me-1 font-semibold">
                                                                <i class="ri-arrow-up-s-line me-1 align-middle"></i>3.25%
                                                            </span>
                                                            <span>this month</span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-6 border-b dark:border-defaultborder/10 border-dashed flex items-start">
                                            <div class="svg-icon-background bg-success/10 !fill-success me-6">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="svg-success"><path d="M11.5,20h-6a1,1,0,0,1-1-1V5a1,1,0,0,1,1-1h5V7a3,3,0,0,0,3,3h3v5a1,1,0,0,0,2,0V9s0,0,0-.06a1.31,1.31,0,0,0-.06-.27l0-.09a1.07,1.07,0,0,0-.19-.28h0l-6-6h0a1.07,1.07,0,0,0-.28-.19.29.29,0,0,0-.1,0A1.1,1.1,0,0,0,11.56,2H5.5a3,3,0,0,0-3,3V19a3,3,0,0,0,3,3h6a1,1,0,0,0,0-2Zm1-14.59L15.09,8H13.5a1,1,0,0,1-1-1ZM7.5,14h6a1,1,0,0,0,0-2h-6a1,1,0,0,0,0,2Zm4,2h-4a1,1,0,0,0,0,2h4a1,1,0,0,0,0-2Zm-4-6h1a1,1,0,0,0,0-2h-1a1,1,0,0,0,0,2Zm13.71,6.29a1,1,0,0,0-1.42,0l-3.29,3.3-1.29-1.3a1,1,0,0,0-1.42,1.42l2,2a1,1,0,0,0,1.42,0l4-4A1,1,0,0,0,21.21,16.29Z"/></svg>
                                            </div>
                                            <div class="flex-grow">
                                                <h6 class="mb-1 text-[0.75rem]">Completed Tasks
                                                    <span class="badge bg-success text-white font-semibold ltr:float-right rtl:float-left">
                                                        4,176
                                                    </span>
                                                </h6>
                                                <div>
                                                    <h4 class="text-[1.125rem] font-semibold mb-1"><span class="count-up" data-count="319">320</span><span class="text-muted ltr:float-right rtl:float-left text-[.6875rem] font-normal">Last Year</span></h4>
                                                    <p class="text-muted text-[.6875rem] mb-0 leading-none">
                                                        <span class="text-danger me-1 font-semibold">
                                                            <i class="ri-arrow-down-s-line me-1 align-middle"></i>1.16%
                                                        </span>
                                                        <span>this month</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-6 border-b dark:border-defaultborder/10 border-dashed flex items-start">
                                            <div class="svg-icon-background bg-warning/10 me-4 !fill-warning">
                                                <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" class="svg-warning"><path d="M19,12h-7V5c0-0.6-0.4-1-1-1c-5,0-9,4-9,9s4,9,9,9s9-4,9-9C20,12.4,19.6,12,19,12z M12,19.9c-3.8,0.6-7.4-2.1-7.9-5.9C3.5,10.2,6.2,6.6,10,6.1V13c0,0.6,0.4,1,1,1h6.9C17.5,17.1,15.1,19.5,12,19.9z M15,2c-0.6,0-1,0.4-1,1v6c0,0.6,0.4,1,1,1h6c0.6,0,1-0.4,1-1C22,5.1,18.9,2,15,2z M16,8V4.1C18,4.5,19.5,6,19.9,8H16z"/></svg>
                                            </div>
                                            <div class="flex-grow">
                                                <h6 class="mb-1 text-[0.75rem]">Pending Tasks
                                                    <span class="badge bg-warning text-white font-semibold ltr:float-right rtl:float-left">
                                                        7,064
                                                    </span>
                                                </h6>
                                                <div>
                                                    <h4 class="text-[1.125rem] font-semibold mb-1"><span class="count-up" data-count="81">82</span><span class="text-muted ltr:float-right rtl:float-left text-[.6875rem] font-normal">Last Year</span></h4>
                                                    <p class="text-muted text-[.6875rem] mb-0 leading-none">
                                                        <span class="text-success me-1 font-semibold">
                                                            <i class="ri-arrow-up-s-line me-1 align-middle"></i>0.25%
                                                        </span>
                                                        <span>this month</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-6 border-b dark:border-defaultborder/10 border-dashed flex items-start">
                                            <div class="svg-icon-background bg-secondary/10 text-secondary !fill-secondary me-4">
                                                <svg xmlns="http://www.w3.org/2000/svg" enable-background="new 0 0 24 24" viewBox="0 0 24 24" class="svg-secondary"><path d="M19,12h-7V5c0-0.6-0.4-1-1-1c-5,0-9,4-9,9s4,9,9,9s9-4,9-9C20,12.4,19.6,12,19,12z M12,19.9c-3.8,0.6-7.4-2.1-7.9-5.9C3.5,10.2,6.2,6.6,10,6.1V13c0,0.6,0.4,1,1,1h6.9C17.5,17.1,15.1,19.5,12,19.9z M15,2c-0.6,0-1,0.4-1,1v6c0,0.6,0.4,1,1,1h6c0.6,0,1-0.4,1-1C22,5.1,18.9,2,15,2z M16,8V4.1C18,4.5,19.5,6,19.9,8H16z"/></svg>
                                            </div>
                                            <div class="flex-grow">
                                                <h6 class="mb-1 text-[0.75rem]">Inprogress Tasks
                                                    <span class="badge bg-secondary text-white font-semibold ltr:float-right rtl:float-left">
                                                        1,105
                                                    </span>
                                                </h6>
                                                <div>
                                                    <h4 class="text-[1.125rem] font-semibold mb-1"><span class="count-up" data-count="32">33</span><span class="text-muted ltr:float-right rtl:float-left text-[.6875rem] font-normal">Last Year</span></h4>
                                                    <p class="text-muted text-[.6875rem] mb-0 leading-none">
                                                        <span class="text-success me-1 font-semibFold">
                                                            <i class="ri-arrow-down-s-line me-1 align-middle"></i>0.46%
                                                        </span>
                                                        <span>this month</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-6 pb-2">
                                            <p class="text-[.9375rem] font-semibold">Tasks Statistics <span class="text-muted font-normal">(Last 6 months) :</span></p>
                                            <div id="task-list-stats"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End::row-1 -->

@endsection

@section('scripts')

        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

        <!-- Apex Charts JS -->
        <script src="{{asset('build/assets/libs/apexcharts/apexcharts.min.js')}}"></script>

        <!-- Flat Picker JS -->
        <script src="{{asset('build/assets/libs/flatpickr/flatpickr.min.js')}}"></script>

        <!-- Internal Invoice List JS -->
        @vite('resources/assets/js/task-list.js')
        

@endsection