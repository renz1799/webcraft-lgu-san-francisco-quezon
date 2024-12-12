@extends('layouts.master')

@section('styles')

        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">

        <!-- flatpickr Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/flatpickr/flatpickr.min.css')}}">
      
@endsection

@section('content')

                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Contacts</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                    <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    CRM
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                    </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                    Contacts
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header flex items-center justify-between flex-wrap gap-4">
                                        <div class="box-title">
                                            Contacts<span class="badge bg-light text-default rounded-full ms-1 text-[0.75rem] align-middle">28</span>
                                        </div>
                                        <div class="flexflex-wrap gap-2">
                                            <a href="javascript:void(0);" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full !py-1 !px-2 !text-[0.75rem]" data-hs-overlay="#todo-compose"><i class="ri-add-line font-semibold align-middle"></i>Create Contact
                                            </a>
                                            <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem] !m-0">Export As CSV</button>
                                            <div class="hs-dropdown ti-dropdown">
                                                <a href="javascript:void(0);" class="ti-btn btn-wave ti-btn-light !py-1 !px-2 !text-[0.75rem] !m-0" aria-expanded="false">
                                                    Sort By<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                                </a>
                                                <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">Newest</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">Date Added</a></li>
                                                    <li><a class="ti-dropdown-item" href="javascript:void(0);">A - Z</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-body !p-0">
                                        <div class="table-responsive">
                                            <table class="table whitespace-nowrap min-w-full">
                                                <thead>
                                                    <tr>
                                                        <th scope="col">
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel" value="" aria-label="...">
                                                        </th>
                                                        <th scope="col" class="text-start">Contact Name</th>
                                                        <th scope="col" class="text-start">Lead Score</th>
                                                        <th scope="col" class="text-start">Email</th>
                                                        <th scope="col" class="text-start">Phone</th>
                                                        <th scope="col" class="text-start">Company</th>
                                                        <th scope="col" class="text-start">Lead Source</th>
                                                        <th scope="col" class="text-start">Tags</th>
                                                        <th scope="col" class="text-start">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr class="border border-x-0 border-defaultborder crm-contact">
                                                        <td>
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel1" value="" aria-label="...">
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-rounded avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts"><span class="block font-semibold">Lisa Convay</span></a>
                                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]" title="Last Contacted"><i class="ri-account-circle-line me-1 text-[0.8125rem] align-middle"></i>24, Jul 2023 - 4:45PM</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            258
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>lisaconvay2981@gmail.com</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1678-28993-223</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/company-logos/1.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>Spruko Technologies</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            Social Media
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center flex-wrap gap-1">
                                                                <span class="badge bg-primary/10 text-primary">New Lead</span>
                                                                <span class="badge bg-primary/10 text-primary">Prospect</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list">
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning ti-btn-icon" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info ti-btn-icon"><i class="ri-pencil-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger ti-btn-icon contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-x-0 border-defaultborder crm-contact">
                                                        <td>
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel2" value="" aria-label="...">
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-rounded avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts"><span class="block font-semibold">Jacob Smith</span></a>
                                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]"   title="Last Contacted"><i class="ri-account-circle-line me-1 text-[0.8125rem] align-middle"></i>15, Jul 2023 - 11:45AM</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            335
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>jacobsmith289@gmail.com</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>8122-2342-4453</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/company-logos/3.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>Spice Infotech</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            Direct mail
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center flex-wrap gap-1">
                                                                <span class="badge bg-primary/10 text-primary">Customer</span>
                                                                <span class="badge bg-danger/10 text-danger">Hot Lead</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list">
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning ti-btn-icon" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info ti-btn-icon"><i class="ri-pencil-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger ti-btn-icon contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-x-0 border-defaultborder crm-contact">
                                                        <td>
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel33" value="" aria-label="...">
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-rounded avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/14.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts"><span class="block font-semibold">Jake Sully</span></a>
                                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]"   title="Last Contacted"><i class="ri-account-circle-line me-1 text-[0.8125rem] align-middle"></i>10, Aug 2023 - 3:25PM</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            685
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>jakesully789@gmail.com</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1902-2001-3023</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/company-logos/4.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>Logitech ecostics</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            Blog Articles
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center flex-wrap gap-1">
                                                                <span class="badge bg-success/10 text-success">Partner</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list">
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning ti-btn-icon" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info ti-btn-icon"><i class="ri-pencil-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger ti-btn-icon contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-x-0 border-defaultborder crm-contact">
                                                        <td>
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel3" value="" aria-label="...">
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-rounded avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts"><span class="block font-semibold">Kiara Advain</span></a>
                                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]"   title="Last Contacted"><i class="ri-account-circle-line me-1 text-[0.8125rem] align-middle"></i>18, Aug 2023 - 10:10AM</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            425
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>kiaraadvain290@gmail.com</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1603-2032-1123</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/company-logos/5.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>Initech Info</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            Affiliates
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center flex-wrap gap-1">
                                                                <span class="badge bg-light text-defaulttextcolor">LostCustomer</span>
                                                                <span class="badge bg-secondary/10 text-secondary">Influencer</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list">
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning ti-btn-icon" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info ti-btn-icon"><i class="ri-pencil-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger ti-btn-icon contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-x-0 border-defaultborder crm-contact">
                                                        <td>
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel4" value="" aria-label="...">
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-rounded avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts"><span class="block font-semibold">Brenda Simpson</span></a>
                                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]"   title="Last Contacted"><i class="ri-account-circle-line me-1 text-[0.8125rem] align-middle"></i>19, Jul 2023 - 12:41PM</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            516
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>brendasimpson1993@gmail.com</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1129-2302-1092</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/company-logos/6.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>Massive Dynamic</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            Organic search
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center flex-wrap gap-1">
                                                                <span class="badge bg-pinkmain/10 text-pinkmain">Subscriber</span>
                                                                <span class="badge bg-success/10 text-success">Partner</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list">
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning ti-btn-icon" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info ti-btn-icon"><i class="ri-pencil-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger ti-btn-icon contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-x-0 border-defaultborder crm-contact">
                                                        <td>
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel5" value="" aria-label="...">
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-rounded avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts"><span class="block font-semibold">Json Taylor</span></a>
                                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]"   title="Last Contacted"><i class="ri-account-circle-line me-1 text-[0.8125rem] align-middle"></i>14, Aug 2023 - 5:18PM</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            127
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>jsontaylor345@gmail.com</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>9923-2344-2003</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/company-logos/7.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>Globex Corporation</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            Social media
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center flex-wrap gap-1">
                                                                <span class="badge bg-danger/10 text-danger">Hot Lead</span>
                                                                <span class="badge bg-info/10 text-info">Referral</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list">
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning ti-btn-icon" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info ti-btn-icon"><i class="ri-pencil-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger ti-btn-icon contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-x-0 border-defaultborder crm-contact">
                                                        <td>
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel6" value="" aria-label="...">
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-rounded avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts"><span class="block font-semibold">Dwayne Jhonson</span></a>
                                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]"   title="Last Contacted"><i class="ri-account-circle-line me-1 text-[0.8125rem] align-middle"></i>12, Jun 2023 - 11:38AM</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            368
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>dwayenejhonson78@gmail.com</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>7891-2093-1994</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/company-logos/8.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>Acme Corporation</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            Blog Articles
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center flex-wrap gap-1">
                                                                <span class="badge bg-warning/10 text-warning">Trial User</span>
                                                                <span class="badge bg-purplemain/10 text-purplemain">Cold Lead</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list">
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning ti-btn-icon" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info ti-btn-icon"><i class="ri-pencil-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger ti-btn-icon contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-x-0 border-defaultborder crm-contact">
                                                        <td>
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel7" value="" aria-label="...">
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-rounded avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts"><span class="block font-semibold">Emiley Jackson</span></a>
                                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]"   title="Last Contacted"><i class="ri-account-circle-line me-1 text-[0.8125rem] align-middle"></i>19, May 2023 - 1:57PM</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            563
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>emileyjackson678@gmail.com</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1899-2993-0923</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/company-logos/9.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>Soylent Corp</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            Organic search
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center flex-wrap gap-1">
                                                                <span class="badge bg-success/10 text-success">Influencer</span>
                                                                <span class="badge bg-info/10 text-info">Partner</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list">
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning ti-btn-icon" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info ti-btn-icon"><i class="ri-pencil-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger ti-btn-icon contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-x-0 border-defaultborder crm-contact">
                                                        <td>
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel8" value="" aria-label="...">
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-rounded avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts"><span class="block font-semibold">Jessica Morris</span></a>
                                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]"   title="Last Contacted"><i class="ri-account-circle-line me-1 text-[0.8125rem] align-middle"></i>28, Jul 2023 - 9:31AM</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            185
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>jessicamorris289@gmail.com</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1768-2332-4934</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/company-logos/10.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>Umbrella Corporation</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            Affiliates
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center flex-wrap gap-1">
                                                                <span class="badge bg-primary/10 text-primary">New Lead</span>
                                                                <span class="badge bg-light text-default">Lost Customer</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list">
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning ti-btn-icon" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info ti-btn-icon"><i class="ri-pencil-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger ti-btn-icon contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="border border-b-0 border-x-0 border-defaultborder crm-contact">
                                                        <td>
                                                            <input class="form-check-input" type="checkbox" id="checkboxNoLabel9" value="" aria-label="...">
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-rounded avatar-sm">
                                                                        <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts"><span class="block font-semibold">Michael Jeremy</span></a>
                                                                    <span class="block text-[#8c9097] dark:text-white/50 text-[0.6875rem]"   title="Last Contacted"><i class="ri-account-circle-line me-1 text-[0.8125rem] align-middle"></i>28, Jul 2023 - 9:31AM</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            240
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>michaeljeremy186@gmail.com</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div>
                                                                <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>4788-7822-4786</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center gap-2">
                                                                <div class="leading-none">
                                                                    <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                        <img src="{{asset('build/assets/images/company-logos/2.png')}}" alt="">
                                                                    </span>
                                                                </div>
                                                                <div>Hooli Technologies</div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            Direct mail
                                                        </td>
                                                        <td>
                                                            <div class="flex items-center flex-wrap gap-1">
                                                                <span class="badge bg-primary/10 text-primary">New Lead</span>
                                                                <span class="badge bg-pinkmain/10 text-pinkmain">Subscriber</span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="btn-list">
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning ti-btn-icon" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info ti-btn-icon"><i class="ri-pencil-line"></i></button>
                                                                <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger ti-btn-icon contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="box-footer border-t-0">
                                        <div class="flex items-center">
                                            <div>
                                                Showing 10 Entries <i class="bi bi-arrow-right ms-2 font-semibold"></i>
                                            </div>
                                            <div class="ms-auto">
                                                <nav aria-label="Page navigation" class="pagination-style-4">
                                                    <ul class="ti-pagination mb-0">
                                                        <li class="page-item disabled">
                                                            <a class="page-link" href="javascript:void(0);">
                                                                Prev
                                                            </a>
                                                        </li>
                                                        <li class="page-item "><a class="page-link active" href="javascript:void(0);">1</a></li>
                                                        <li class="page-item"><a class="page-link" href="javascript:void(0);">2</a></li>
                                                        <li class="page-item">
                                                            <a class="page-link text-primary" href="javascript:void(0);">
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
                        <!--End::row-1 -->

                        <!-- Start:: Contact Details Offcanvas -->
                        <div class="hs-overlay hidden ti-offcanvas ti-offcanvas-right !max-w-[25rem] !border-0" tabindex="-1" id="hs-overlay-contacts">
                            <div class="ti-offcanvas-body !p-0">
                                <div class="sm:flex items-start p-6 border-b border-dashed border-defaultborder dark:border-defaultborder/10 main-profile-cover">
                                    <div class="avatar avatar-xxl avatar-rounded online me-4 !bottom-0 !mb-0">
                                        <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="">
                                    </div>
                                    <div class="flex-grow main-profile-info">
                                        <div class="flex items-center justify-between">
                                            <h6 class="font-semibold mb-1 text-white">Lisa Convay</h6>
                                            <button type="button" class="ti-btn btn-wave flex-shrink-0 !p-0  transition-none text-white opacity-70 hover:opacity-100 hover:text-white focus:ring-0 focus:ring-offset-0 focus:ring-offset-transparent focus:outline-0 focus-visible:outline-0 !mb-0" data-hs-overlay="#hs-overlay-contacts">
                                                <span class="sr-only">Close modal</span>
                                                <i class="ri-close-line leading-none text-lg"></i>
                                            </button>
                                        </div>
                                        <p class="mb-1 text-white  op-7">Chief Executive Officer (C.E.O)</p>
                                        <p class="text-[0.75rem] text-white mb-4 opacity-[0.5]">
                                            <span class="me-3"><i class="ri-building-line me-1 align-middle"></i>Georgia</span>
                                            <span><i class="ri-map-pin-line me-1 align-middle"></i>Washington D.C</span>
                                        </p>
                                        <div class="flex mb-0">
                                            <div class="me-4">
                                                <p class="font-bold text-xl text-white text-shadow mb-0">113</p>
                                                <p class="mb-0 text-[0.6875rem] opacity-[0.5] text-white">Deals</p>
                                            </div>
                                            <div class="me-4">
                                                <p class="font-bold text-xl text-white text-shadow mb-0">$12.2k</p>
                                                <p class="mb-0 text-[0.6875rem] opacity-[0.5] text-white">Contributions</p>
                                            </div>
                                            <div class="me-4">
                                                <p class="font-bold text-xl text-white text-shadow mb-0">$10.67k</p>
                                                <p class="mb-0 text-[0.6875rem] opacity-[0.5] text-white">Comitted</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-6 border-b border-dashed border-defaultborder dark:border-defaultborder/10">
                                    <div class="mb-0">
                                        <p class="text-[0.9375rem] mb-2 font-semibold">Professional Bio :</p>
                                        <p class="text-[#8c9097] dark:text-white/50 op-8 mb-0">
                                            I am <b class="text-default">Lisa Convay,</b> here by conclude that,i am the founder and managing director of the prestigeous company name laugh at all and acts as the cheif executieve officer of the company.
                                        </p>
                                    </div>
                                </div>
                                <div class="p-6 border-b border-dashed border-defaultborder dark:border-defaultborder/10">
                                    <p class="text-[.875rem] mb-2 me-4 font-semibold">Contact Information :</p>
                                    <div class="">
                                        <div class="flex items-center mb-2">
                                            <div class="me-2">
                                                <span class="avatar avatar-sm avatar-rounded bg-light !text-[#8c9097] dark:text-white/50">
                                                    <i class="ri-mail-line align-middle text-[.875rem]"></i>
                                                </span>
                                            </div>
                                            <div>
                                                sonyataylor2531@gmail.com
                                            </div>
                                        </div>
                                        <div class="flex items-center mb-2">
                                            <div class="me-2">
                                                <span class="avatar avatar-sm avatar-rounded bg-light !text-[#8c9097] dark:text-white/50">
                                                    <i class="ri-phone-line align-middle text-[.875rem]"></i>
                                                </span>
                                            </div>
                                            <div>
                                                +(555) 555-1234
                                            </div>
                                        </div>
                                        <div class="flex items-center mb-0">
                                            <div class="me-2">
                                                <span class="avatar avatar-sm avatar-rounded bg-light !text-[#8c9097] dark:text-white/50">
                                                    <i class="ri-map-pin-line align-middle text-[.875rem]"></i>
                                                </span>
                                            </div>
                                            <div>
                                                MIG-1-11, Monroe Street, Georgetown, Washington D.C, USA,20071
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-6 border-b border-dashed border-defaultborder dark:border-defaultborder/10 flex items-center">
                                    <p class="text-[.875rem] mb-0 me-4 font-semibold">Social Networks :</p>
                                    <div class="btn-list mb-0 gap-2 flex">
                                        <button aria-label="button" type="button" class="ti-btn btn-wave w-[1.75rem] h-[1.75rem] text-[0.8rem] py-[0.26rem] px-2 rounded-sm ti-btn-primary mb-0">
                                            <i class="ri-facebook-line font-semibold"></i>
                                        </button>
                                        <button aria-label="button" type="button" class="ti-btn btn-wave w-[1.75rem] h-[1.75rem] text-[0.8rem] py-[0.26rem] px-2 rounded-sm ti-btn-secondary mb-0">
                                            <i class="ri-twitter-x-line font-semibold"></i>
                                        </button>
                                        <button aria-label="button" type="button" class="ti-btn btn-wave w-[1.75rem] h-[1.75rem] text-[0.8rem] py-[0.26rem] px-2 rounded-sm ti-btn-warning mb-0">
                                            <i class="ri-instagram-line font-semibold"></i>
                                        </button>
                                        <button aria-label="button" type="button" class="ti-btn btn-wave w-[1.75rem] h-[1.75rem] text-[0.8rem] py-[0.26rem] px-2 rounded-sm ti-btn-success mb-0">
                                            <i class="ri-github-line font-semibold"></i>
                                        </button>
                                        <button aria-label="button" type="button" class="ti-btn btn-wave w-[1.75rem] h-[1.75rem] text-[0.8rem] py-[0.26rem] px-2 rounded-sm ti-btn-danger mb-0">
                                            <i class="ri-youtube-line font-semibold"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="p-6 border-b border-dashed border-defaultborder dark:border-defaultborder/10">
                                    <p class="text-[.875rem] mb-2 me-4 font-semibold">Tags :</p>
                                    <div>
                                        <span class="badge bg-light text-[#8c9097] dark:text-white/50 m-1">New Lead</span>
                                        <span class="badge bg-light text-[#8c9097] dark:text-white/50 m-1">Others</span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <p class="text-[.875rem] mb-2 font-semibold">Relationship Manager :
                                        <a class="text-[.875rem] text-primary mb-0 ltr:float-right rtl:float-left" href="javascript:void(0);"><i class="ri-add-line me-1 align-middle"></i>Add Manager</a>
                                    </p>
                                    <div class="avatar-list-stacked">
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
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End:: Contact Details Offcanvas -->

                        <!-- Start:: Create Contact -->
                        <div id="todo-compose" class="hs-overlay hidden ti-modal">
                            <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
                            <div class="ti-modal-content">
                                <div class="ti-modal-header">
                                    <h6 class="modal-title text-[1rem] font-semibold text-defaulttextcolor" id="mail-ComposeLabel">Create Contact</h6>
                                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#todo-compose">
                                        <span class="sr-only">Close</span>
                                        <i class="ri-close-line"></i>
                                    </button>
                                </div>
                                <div class="ti-modal-body px-4">
                                    <div class="grid grid-cols-12 gap-4">
                                        <div class="xl:col-span-12 col-span-12">
                                            <div class="mb-0 text-center">
                                                <span class="avatar avatar-xxl avatar-rounded">
                                                    <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="" id="profile-img">
                                                    <span class="badge rounded-pill bg-primary avatar-badge">
                                                        <input type="file" name="photo" class="absolute w-full h-full opacity-0" id="profile-change">
                                                        <i class="fe fe-camera text-[.625rem] !text-white"></i>
                                                    </span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="xl:col-span-6 col-span-12">
                                            <label for="deal-title" class="form-label">Deal Title</label>
                                            <input type="text" class="form-control" id="deal-title" placeholder="Deal Title">
                                        </div>
                                        <div class="xl:col-span-6 col-span-12">
                                            <label for="contact-lead-score" class="form-label">Lead Score</label>
                                            <input type="number" class="form-control" id="contact-lead-score" placeholder="Lead Score">
                                        </div>
                                        <div class="xl:col-span-12 col-span-12">
                                            <label for="contact-mail" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="contact-mail" placeholder="Enter Email">
                                        </div>
                                        <div class="xl:col-span-12 col-span-12">
                                            <label for="contact-phone" class="form-label">Phone No</label>
                                            <input type="tel" class="form-control" id="contact-phone" placeholder="Enter Phone Number">
                                        </div>
                                        <div class="xl:col-span-12 col-span-12">
                                            <label for="company-name" class="form-label">Company Name</label>
                                            <input type="text" class="form-control" id="company-name" placeholder="Company Name">
                                        </div>
                                        <div class="xl:col-span-12 col-span-12">
                                            <label class="form-label">Lead Source</label>
                                            <select class="form-control" name="choices-multiple-remove-button1" id="choices-multiple-remove-button1">
                                                <option value="Choice 1">Social Media</option>
                                                <option value="Choice 2">Direct mail</option>
                                                <option value="Choice 3">Blog Articles</option>
                                                <option value="Choice 4">Affiliates</option>
                                                <option value="Choice 5">Organic search</option>
                                            </select>
                                        </div>
                                        <div class="xl:col-span-6 col-span-12">
                                            <label class="form-label">Last Contacted</label>
                                            <div class="form-group">
                                                <div class="input-group">
                                                    <div class="input-group-text text-[#8c9097] dark:text-white/50"> <i class="ri-calendar-line"></i> </div>
                                                    <input type="text" class="form-control" id="targetDate" placeholder="Choose date and time">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="xl:col-span-6 col-span-12">
                                            <label class="form-label">Tags</label>
                                            <select class="form-control" name="choices-multiple-remove-button2" id="choices-multiple-remove-button2" multiple>
                                                <option value="">Select Tag</option>
                                                <option value="Choice 1">New Lead</option>
                                                <option value="Choice 2">Prospect</option>
                                                <option value="Choice 3">Customer</option>
                                                <option value="Choice 4">Hot Lead</option>
                                                <option value="Choice 5">Partner</option>
                                                <option value="Choice 6">LostCustomer</option>
                                                <option value="Choice 7">Influencer</option>
                                                <option value="Choice 8">Subscriber</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="ti-modal-footer">
                                    <button type="button"
                                    class="hs-dropdown-toggle ti-btn btn-wave  ti-btn-light align-middle"
                                    data-hs-overlay="#todo-compose">
                                    Cancel
                                </button>
                                    <button type="button" class="ti-btn btn-wave bg-primary text-white !font-medium">Create Contact</button>
                                </div>
                            </div>
                            </div>
                        </div>
                        <!-- End:: Create Contact -->

@endsection

@section('scripts')

        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

        <!-- Flat Picker JS -->
        <script src="{{asset('build/assets/libs/flatpickr/flatpickr.min.js')}}"></script>

        <!-- CRM Contacts JS -->
        @vite('resources/assets/js/crm-contacts.js')


@endsection