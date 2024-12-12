@extends('layouts.master')

@section('styles')
  
        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">
      
@endsection

@section('content')

                            <!-- Page Header -->
                            <div class="block justify-between page-header md:flex">
                                <div>
                                    <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Companies</h3>
                                </div>
                                <ol class="flex items-center whitespace-nowrap min-w-0">
                                    <li class="text-[0.813rem] ps-[0.5rem]">
                                        <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                        CRM
                                        <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                        </a>
                                    </li>
                                    <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                        Companies
                                    </li>
                                </ol>
                            </div>
                            <!-- Page Header Close -->

                            <!-- Start::row-1 -->
                            <div class="grid grid-cols-12 gap-6">
                                <div class="xl:col-span-12 col-span-12">
                                    <div class="box custom-box">
                                        <div class="box-header justify-between">
                                            <div class="box-title">
                                                Companies <span class="badge bg-light text-defaulttextcolor rounded-full ms-1 text-[0.75rem] align-middle">14</span>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <a href="javascript:void(0);" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full !py-1 !px-2 !text-[0.75rem]" data-hs-overlay="#todo-compose"><i class="ri-add-line font-semibold align-middle"></i>Add Company
                                                </a>
                                                <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Export As CSV</button>
                                                <div class="hs-dropdown ti-dropdown">
                                                    <a href="javascript:void(0);" class="ti-btn btn-wave ti-btn-light !py-1 !px-2 !text-[0.75rem]" aria-expanded="false">
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
                                                            <th scope="col" class="text-start">Company Name</th>
                                                            <th scope="col" class="text-start">Email</th>
                                                            <th scope="col" class="text-start">Phone</th>
                                                            <th scope="col" class="text-start">Industry</th>
                                                            <th scope="col" class="text-start">Company Size</th>
                                                            <th scope="col" class="text-start">Key Contact</th>
                                                            <th scope="col" class="text-start">Total Deals</th>
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
                                                                        <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/company-logos/1.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts">Spruko Technologies</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>sprukotechnologies2981@gmail.com</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1678-28993-223</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Information Technology
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center flex-wrap gap-1">
                                                                    <span class="badge bg-primary/10 text-primary">Corporate</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-rounded avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/4.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block font-semibold">Lisa Convay</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                258
                                                            </td>
                                                            <td>
                                                                <div class="btn-list">
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info"><i class="ri-pencil-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger contact-delete"><i class="ri-delete-bin-line"></i></button>
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
                                                                        <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/company-logos/3.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts">Spice Infotech</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>spiceinfotech289@gmail.com</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>8122-2342-4453</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Telecommunications
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center flex-wrap gap-1">
                                                                    <span class="badge bg-danger/10 text-danger">Small Business</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-rounded avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block font-semibold">Jacob Smith</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                335
                                                            </td>
                                                            <td>
                                                                <div class="btn-list">
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info"><i class="ri-pencil-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger contact-delete"><i class="ri-delete-bin-line"></i></button>
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
                                                                        <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/company-logos/4.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts">Logitech ecostics</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>logitecheco789@gmail.com</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1902-2001-3023</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Logistics
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center flex-wrap gap-1">
                                                                    <span class="badge bg-success/10 text-success">Micro Business</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-rounded avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/14.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block font-semibold">Jake Sully</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                685
                                                            </td>
                                                            <td>
                                                                <div class="btn-list">
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info"><i class="ri-pencil-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger contact-delete"><i class="ri-delete-bin-line"></i></button>
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
                                                                        <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/company-logos/5.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts">Initech Info</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>initechinfo290@gmail.com</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1603-2032-1123</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Information Technology
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center flex-wrap gap-1">
                                                                    <span class="badge bg-light text-default">Startup</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-rounded avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block font-semibold">Kiara Advain</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                425
                                                            </td>
                                                            <td>
                                                                <div class="btn-list">
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info"><i class="ri-pencil-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger contact-delete"><i class="ri-delete-bin-line"></i></button>
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
                                                                        <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/company-logos/6.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts">Massive Dynamic</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>massivedynamic1993@gmail.com</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1129-2302-1092</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Professional Services
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center flex-wrap gap-1">
                                                                    <span class="badge bg-pink/10 text-pinkmain">Large Enterprise</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-rounded avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block font-semibold">Brenda Simpson</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                516
                                                            </td>
                                                            <td>
                                                                <div class="btn-list">
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info"><i class="ri-pencil-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger contact-delete"><i class="ri-delete-bin-line"></i></button>
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
                                                                        <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/company-logos/7.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts">Globex Corporation</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>globexcorp345@gmail.com</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>9923-2344-2003</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Education
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center flex-wrap gap-1">
                                                                    <span class="badge bg-danger/10 text-danger">Small Business</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-rounded avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block font-semibold">Json Taylor</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                127
                                                            </td>
                                                            <td>
                                                                <div class="btn-list">
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info"><i class="ri-pencil-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger contact-delete"><i class="ri-delete-bin-line"></i></button>
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
                                                                        <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/company-logos/8.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts">Acme Corporation</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>acmecorporation78@gmail.com</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>7891-2093-1994</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Telecommunications
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center flex-wrap gap-1">
                                                                    <span class="badge bg-primary/10 text-primary">Corporate</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-rounded avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block font-semibold">Dwayne Jhonson</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                368
                                                            </td>
                                                            <td>
                                                                <div class="btn-list">
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info"><i class="ri-pencil-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger contact-delete"><i class="ri-delete-bin-line"></i></button>
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
                                                                        <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/company-logos/9.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts">Soylent Corp</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>soylentcorp678@gmail.com</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1899-2993-0923</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Manufacturing
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center flex-wrap gap-1">
                                                                    <span class="badge bg-warning/10 text-warning">Medium Size</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-rounded avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block font-semibold">Emiley Jackson</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                563
                                                            </td>
                                                            <td>
                                                                <div class="btn-list">
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info"><i class="ri-pencil-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger contact-delete"><i class="ri-delete-bin-line"></i></button>
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
                                                                        <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/company-logos/10.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts">Umbrella Corporation</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>umbrellacorp289@gmail.com</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>1768-2332-4934</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Healthcare
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center flex-wrap gap-1">
                                                                    <span class="badge bg-success/10 text-success">Micro Business</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-rounded avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block font-semibold">Jessica Morris</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                185
                                                            </td>
                                                            <td>
                                                                <div class="btn-list">
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info"><i class="ri-pencil-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        <tr class="border border-x-0 border-defaultborder crm-contact">
                                                            <td>
                                                                <input class="form-check-input" type="checkbox" id="checkboxNoLabel9" value="" aria-label="...">
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-sm p-1 bg-light avatar-rounded">
                                                                            <img src="{{asset('build/assets/images/company-logos/2.png')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <a href="javascript:void(0);" data-hs-overlay="#hs-overlay-contacts">Hooli Technologies</a>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block mb-1"><i class="ri-mail-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>hoolitech186@gmail.com</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div>
                                                                    <span class="block"><i class="ri-phone-line me-2 align-middle text-[.875rem] text-[#8c9097] dark:text-white/50 inline-flex"></i>4788-7822-4786</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                Information Technology
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center flex-wrap gap-1">
                                                                    <span class="badge bg-light text-default">Startup</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="leading-none">
                                                                        <span class="avatar avatar-rounded avatar-sm">
                                                                            <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="">
                                                                        </span>
                                                                    </div>
                                                                    <div>
                                                                        <span class="block font-semibold">Michael Jeremy</span>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                240
                                                            </td>
                                                            <td>
                                                                <div class="btn-list">
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-warning" data-hs-overlay="#hs-overlay-contacts"><i class="ri-eye-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-info"><i class="ri-pencil-line"></i></button>
                                                                    <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-sm ti-btn-danger contact-delete"><i class="ri-delete-bin-line"></i></button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="box-footer !border-t-0">
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

                            <!-- Start:: Company Details Offcanvas -->
                            <div class="hs-overlay hidden ti-offcanvas ti-offcanvas-right !max-w-[25rem] !border-0" tabindex="-1" id="hs-overlay-contacts">
                                <div class="ti-offcanvas-body !p-0">
                                    <div class="sm:flex items-start p-6 border-b border-dashed border-defaultborder dark:border-defaultborder/10 main-profile-cover">
                                        <div>
                                            <span class="avatar avatar-xxl avatar-rounded me-3 bg-light/10 p-2">
                                                <img src="{{asset('build/assets/images/company-logos/12.png')}}" alt="">
                                            </span>
                                        </div>
                                        <div class="flex-fill main-profile-info w-full">
                                            <div class="flex items-center justify-between">
                                                <h6 class="font-semibold mb-1 text-white">Spruko Technologies</h6>
                                                <button type="button" class="ti-btn btn-wave flex-shrink-0 !p-0  transition-none text-white opacity-70 hover:opacity-100 hover:text-white focus:ring-0 focus:ring-offset-0 focus:ring-offset-transparent focus:outline-0 focus-visible:outline-0 !mb-0" data-hs-overlay="#hs-overlay-contacts">
                                                    <span class="sr-only">Close modal</span>
                                                    <i class="ri-close-line leading-none text-lg"></i>
                                                </button>
                                            </div>
                                            <p class="mb-1 text-white opacity-70">Telecommunications</p>
                                            <p class="text-[0.75rem] text-white mb-4 opacity-50">
                                                <span class="me-3"><i class="ri-building-line me-1 align-middle"></i>Georgia</span>
                                                <span><i class="ri-map-pin-line me-1 align-middle"></i>Washington D.C</span>
                                            </p>
                                            <div class="flex mb-0">
                                                <div class="me-4">
                                                    <p class="font-bold text-xl text-white text-shadow mb-0">113</p>
                                                    <p class="mb-0 text-[0.6875rem] opacity-50 text-white">Deals</p>
                                                </div>
                                                <div class="me-4">
                                                    <p class="font-bold text-xl text-white text-shadow mb-0">$12.2k</p>
                                                    <p class="mb-0 text-[0.6875rem] opacity-50 text-white">Contributions</p>
                                                </div>
                                                <div class="me-4">
                                                    <p class="font-bold text-xl text-white text-shadow mb-0">$10.67k</p>
                                                    <p class="mb-0 text-[0.6875rem] opacity-50 text-white">Comitted</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6 border-b border-dashed border-defaultborder dark:border-defaultborder/10">
                                        <div class="mb-0">
                                            <p class="text-[0.9375rem] mb-2 font-semibold">Professional Bio :</p>
                                            <p class="text-[#8c9097] dark:text-white/50 op-8 mb-0">
                                                <b class="text-default">Spruko</b> Technologies is a leading technology company specializing in innovative software solutions for businesses worldwide. With a strong focus on cutting-edge technologies and a team of skilled professionals.
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
                                                    sprukotechnologies2981@gmail.com
                                                </div>
                                            </div>
                                            <div class="flex items-center mb-2">
                                                <div class="me-2">
                                                    <span class="avatar avatar-sm avatar-rounded bg-light !text-[#8c9097] dark:text-white/50">
                                                        <i class="ri-phone-line align-middle text-[.875rem]"></i>
                                                    </span>
                                                </div>
                                                <div>
                                                    1678-28993-223
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
                                                <i class="ri-twitter-line font-semibold"></i>
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
                                    <div class="p-6 border-b border-dashed border-defaultborder dark:border-defaultborder/10 flex items-center gap-3">
                                        <div class="text-[.875rem] font-semibold">Company Size:</div>
                                        <div>
                                            <span class="badge bg-primary/10 m-1">Corporate</span> - 1001+ Employees
                                        </div>
                                    </div>
                                    <div class="p-4 flex items-center gap-3">
                                        <div class="text-[.875rem] font-semibold">
                                            Key Contact :
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="leading-none">
                                                <span class="avatar avatar-rounded avatar-sm">
                                                    <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="img">
                                                </span>
                                            </div>
                                            <div class="font-semibold">Lisa Convay</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End:: Company Details Offcanvas -->

                            <!-- Start:: Add Company -->
                            <div id="todo-compose" class="hs-overlay hidden ti-modal">
                                <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
                                <div class="ti-modal-content">
                                    <div class="ti-modal-header">
                                        <h6 class="modal-title text-[1rem] font-semibold text-defaulttextcolor" id="mail-ComposeLabel">Add Company</h6>
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
                                                        <img src="{{asset('build/assets/images/company-logos/11.png')}}" alt="" id="profile-img">
                                                        <span class="badge rounded-pill bg-primary avatar-badge">
                                                            <input type="file" name="photo" class="absolute w-full h-full opacity-0" id="profile-change">
                                                            <i class="fe fe-camera text-[.625rem] !text-white"></i>
                                                        </span>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label for="company-name" class="form-label">Company Name</label>
                                                <input type="text" class="form-control" id="company-name" placeholder="Contact Name">
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label for="company-lead-score" class="form-label">Total Deals</label>
                                                <input type="number" class="form-control" id="company-lead-score" placeholder="Total Deals">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label for="company-mail" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="company-mail" placeholder="Enter Email">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label for="company-phone" class="form-label">Phone No</label>
                                                <input type="text" class="form-control" id="company-phone" placeholder="Enter Phone Number">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label for="key-contact" class="form-label">Key Contact</label>
                                                <input type="text" class="form-control" id="key-contact" placeholder="Contact Name">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label class="form-label">Industry</label>
                                                <select class="form-control" data-trigger>
                                                    <option value="">Select Insustry</option>
                                                    <option value="Choice 1">Information Technology</option>
                                                    <option value="Choice 2">Telecommunications</option>
                                                    <option value="Choice 3">Logistics</option>
                                                    <option value="Choice 4">Professional Services</option>
                                                    <option value="Choice 5">Education</option>
                                                    <option value="Choice 6">Education</option>
                                                    <option value="Choice 7">Manufacturing</option>
                                                    <option value="Choice 8">Healthcare</option>
                                                </select>
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label class="form-label">Company Size</label>
                                                <select class="form-control" data-trigger>
                                                    <option value="">Company Size</option>
                                                    <option value="Choice 1">Corporate</option>
                                                    <option value="Choice 2">Small Business</option>
                                                    <option value="Choice 3">Micro Business</option>
                                                    <option value="Choice 4">Startup</option>
                                                    <option value="Choice 5">Large Enterprise</option>
                                                    <option value="Choice 6">Medium Size</option>
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
                            <!-- End:: Add Company -->

@endsection

@section('scripts')

        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

        <!-- CRM Contacts JS -->
        @vite('resources/assets/js/crm-companies.js')
        

@endsection