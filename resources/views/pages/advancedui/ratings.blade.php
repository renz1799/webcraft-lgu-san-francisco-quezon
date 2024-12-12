@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')
        
                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3
                                class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">
                                Ratings</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                                <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate"
                                    href="javascript:void(0);">
                                    Advanced Ui
                                    <i
                                        class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 "
                                aria-current="page">
                                Ratings
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row-1 -->
                    <div class="grid grid-cols-12 xxl:gap-x-5">
                        <div class="col-span-12 xxxl:col-span-4">
                            <div class="box">
                                <div class="box-header">
                                    <h5 class="box-title"> Basic usage</h5>
                                </div>
                                <div class="box-body">
                                    <div class="p-5 border border-dashed border-gray-200 dark:border-white/10 rounded-sm">
                                        <!-- Rating -->
                                        <div class="flex flex-row-reverse justify-end items-center"> <input
                                                id="hs-ratings-readonly-1" type="radio"
                                                class="peer -ms-5 size-5 !bg-transparent !border-0 !text-transparent cursor-pointer appearance-none !checked:bg-none !bg-none !focus:bg-none focus:ring-0 focus:ring-offset-0"
                                                name="hs-ratings-readonly" value="1"> <label for="hs-ratings-readonly-1"
                                                class="peer-checked:text-warning text-gray-200 pointer-events-none dark:text-gray-200">
                                                <i class="ri-star-fill text-xl"></i> </label> <input
                                                id="hs-ratings-readonly-2" type="radio"
                                                class="peer -ms-5 size-5 !bg-transparent !border-0 !text-transparent cursor-pointer appearance-none !checked:bg-none !bg-none !focus:bg-none focus:ring-0 focus:ring-offset-0"
                                                name="hs-ratings-readonly" value="2"> <label for="hs-ratings-readonly-2"
                                                class="peer-checked:text-warning text-gray-200 pointer-events-none dark:text-gray-200">
                                                <i class="ri-star-fill text-xl"></i> </label> <input
                                                id="hs-ratings-readonly-3" type="radio"
                                                class="peer -ms-5 size-5 !bg-transparent !border-0 !text-transparent cursor-pointer appearance-none !checked:bg-none !bg-none !focus:bg-none focus:ring-0 focus:ring-offset-0"
                                                name="hs-ratings-readonly" value="3"> <label for="hs-ratings-readonly-3"
                                                class="peer-checked:text-warning text-gray-200 pointer-events-none dark:text-gray-200">
                                                <i class="ri-star-fill text-xl"></i> </label> <input
                                                id="hs-ratings-readonly-4" type="radio"
                                                class="peer -ms-5 size-5 !bg-transparent !border-0 !text-transparent cursor-pointer appearance-none !checked:bg-none !bg-none !focus:bg-none focus:ring-0 focus:ring-offset-0"
                                                name="hs-ratings-readonly" value="4"> <label for="hs-ratings-readonly-4"
                                                class="peer-checked:text-warning text-gray-200 pointer-events-none dark:text-gray-200">
                                                <i class="ri-star-fill text-xl"></i> </label> <input
                                                id="hs-ratings-readonly-5" type="radio"
                                                class="peer -ms-5 size-5 !bg-transparent !border-0 !text-transparent cursor-pointer appearance-none !checked:bg-none !bg-none !focus:bg-none focus:ring-0 focus:ring-offset-0"
                                                name="hs-ratings-readonly" value="5"> <label for="hs-ratings-readonly-5"
                                                class="peer-checked:text-warning text-gray-200 pointer-events-none dark:text-gray-200">
                                                <i class="ri-star-fill text-xl"></i> </label> </div>
                                        <!-- End Rating -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 xxxl:col-span-4">
                            <div class="box">
                                <div class="box-header">
                                    <h5 class="box-title"> Button with star shapes.</h5>
                                </div>
                                <div class="box-body">
                                    <div class="p-5 border border-dashed border-gray-200 dark:border-white/10 rounded-sm">
                                        <!-- Rating -->
                                        <div class="flex items-center">
                                            <button type="button"
                                                class="size-5 me-1 inline-flex justify-center items-center text-2xl rounded-full text-warning disabled:opacity-50 disabled:pointer-events-none dark:text-warning">
                                                <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg"
                                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path
                                                        d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="size-5 me-1 inline-flex justify-center items-center text-2xl rounded-full text-warning disabled:opacity-50 disabled:pointer-events-none dark:text-warning">
                                                <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg"
                                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path
                                                        d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="size-5 me-1 inline-flex justify-center items-center text-2xl rounded-full text-warning disabled:opacity-50 disabled:pointer-events-none dark:text-warning">
                                                <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg"
                                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path
                                                        d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="size-5 me-1 inline-flex justify-center items-center text-2xl rounded-full text-warning disabled:opacity-50 disabled:pointer-events-none dark:text-warning">
                                                <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg"
                                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path
                                                        d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="size-5 me-1 inline-flex justify-center items-center text-2xl rounded-full text-gray-400 hover:text-warning disabled:opacity-50 disabled:pointer-events-none dark:text-gray-200 dark:hover:text-warning">
                                                <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg"
                                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path
                                                        d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- End Rating -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 xxxl:col-span-4">
                            <div class="box">
                                <div class="box-header">
                                    <h5 class="box-title"> Button with heart shapes.</h5>
                                </div>
                                <div class="box-body">
                                    <div class="p-5 border border-dashed border-gray-200 dark:border-white/10 rounded-sm">
                                        <!-- Rating -->
                                        <div class="flex items-center">
                                            <button type="button"
                                                class="size-5 me-1 inline-flex justify-center items-center text-2xl rounded-full text-danger disabled:opacity-50 disabled:pointer-events-none dark:text-danger">
                                                <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg"
                                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd"
                                                        d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="size-5 me-1 inline-flex justify-center items-center text-2xl rounded-full text-danger disabled:opacity-50 disabled:pointer-events-none dark:text-danger">
                                                <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg"
                                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd"
                                                        d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="size-5 me-1 inline-flex justify-center items-center text-2xl rounded-full text-danger disabled:opacity-50 disabled:pointer-events-none dark:text-danger">
                                                <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg"
                                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd"
                                                        d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="size-5 me-1 inline-flex justify-center items-center text-2xl rounded-full text-danger disabled:opacity-50 disabled:pointer-events-none dark:text-danger">
                                                <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg"
                                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd"
                                                        d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z" />
                                                </svg>
                                            </button>
                                            <button type="button"
                                                class="size-5 me-1 inline-flex justify-center items-center text-2xl rounded-full text-gray-400 hover:text-danger disabled:opacity-50 disabled:pointer-events-none dark:text-gray-300 dark:hover:text-danger">
                                                <svg class="flex-shrink-0 size-5" xmlns="http://www.w3.org/2000/svg"
                                                    width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                                    <path fill-rule="evenodd"
                                                        d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314z" />
                                                </svg>
                                            </button>
                                        </div>
                                        <!-- End Rating -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 xxxl:col-span-4">
                            <div class="box">
                                <div class="box-header">
                                    <h5 class="box-title"> Static Ratings.</h5>
                                </div>
                                <div class="box-body">
                                    <div class="p-5 border border-dashed border-gray-200 dark:border-white/10 rounded-sm">
                                        <!-- Rating -->
                                        <div class="flex items-center">
                                            <svg class="flex-shrink-0 me-1 size-5 text-warning dark:text-warning"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" viewBox="0 0 16 16">
                                                <path
                                                    d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                            </svg>
                                            <svg class="flex-shrink-0 me-1 size-5 text-warning dark:text-warning"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" viewBox="0 0 16 16">
                                                <path
                                                    d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                            </svg>
                                            <svg class="flex-shrink-0 me-1 size-5 text-warning dark:text-warning"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" viewBox="0 0 16 16">
                                                <path
                                                    d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                            </svg>
                                            <svg class="flex-shrink-0 me-1 size-5 text-gray-400 dark:text-gray-300"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" viewBox="0 0 16 16">
                                                <path
                                                    d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                            </svg>
                                            <svg class="flex-shrink-0 me-1 size-5 text-gray-400 dark:text-gray-300"
                                                xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                fill="currentColor" viewBox="0 0 16 16">
                                                <path
                                                    d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                            </svg>
                                        </div>
                                        <!-- End Rating -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 xxxl:col-span-4">
                            <div class="box">
                                <div class="box-header">
                                    <h5 class="box-title"> Emoji's Ratings.</h5>
                                </div>
                                <div class="box-body">
                                    <div class="p-5 border border-dashed border-gray-200 dark:border-white/10 rounded-sm">
                                        <!-- Rate review -->
                                        <div class="sm:flex items-center justify-between space-y-2 sm:space-y-0">
                                            <p class="text-sm mb-0 font-semibold"> Did this answer your question?</p>
                                            <!-- Rating -->
                                            <div class="flex justify-center items-center">
                                                <button type="button"
                                                    class="size-8 inline-flex justify-center items-center text-xl rounded-full hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                                    &#128532;
                                                </button>
                                                <button type="button"
                                                    class="size-8 inline-flex justify-center items-center text-xl rounded-full hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                                    &#128528;
                                                </button>
                                                <button type="button"
                                                    class="size-8 inline-flex justify-center items-center text-xl rounded-full hover:bg-gray-100 disabled:opacity-50 disabled:pointer-events-none dark:hover:bg-gray-700 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                                    &#129321;
                                                </button>
                                            </div>
                                            <!-- End Rating -->
                                        </div>
                                        <!-- End Rate review -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-12 xxxl:col-span-4">
                            <div class="box">
                                <div class="box-header">
                                    <h5 class="box-title"> Rate with thumb buttons.</h5>
                                </div>
                                <div class="box-body">
                                    <div class="p-5 border border-dashed border-gray-200 dark:border-white/10 rounded-sm">
                                        <!-- Rating -->
                                        <div class="mt-2 flex justify-center items-center gap-x-2">
                                            <h6 class="text-gray-800 dark:text-white">
                                                Was this page helpful?
                                            </h6>
                                            <button type="button"
                                                class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-sm border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-bodybg dark:border-white/10 dark:text-white dark:hover:bg-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="M7 10v12" />
                                                    <path
                                                        d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z" />
                                                </svg>
                                                Yes
                                            </button>
                                            <button type="button"
                                                class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-sm border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 disabled:opacity-50 disabled:pointer-events-none dark:bg-bodybg dark:border-white/10 dark:text-white dark:hover:bg-gray-800 dark:focus:outline-none dark:focus:ring-1 dark:focus:ring-gray-600">
                                                <svg class="flex-shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="M17 14V2" />
                                                    <path
                                                        d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22h0a3.13 3.13 0 0 1-3-3.88Z" />
                                                </svg>
                                                No
                                            </button>
                                        </div>
                                        <!-- End Rating -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End::row-1 -->

                    <!-- Start::row-2 -->
                    <div class="grid grid-cols-12 gap-6">
                        <div class="xxl:col-span-4 xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">
                                        Basic Rater
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="flex flex-wrap items-center justify-between">
                                        <p class="text-[.875rem] mb-0 font-semibold">Show Some <span
                                                class="text-danger">❤</span> with rating :</p>
                                        <div id="rater-basic"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="xxl:col-span-4 xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">
                                        5 star rater with steps
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="flex flex-wrap items-center justify-between">
                                        <p class="text-[.875rem] mb-0 font-semibold">Dont forget to rate the product :</p>
                                        <div id="rater-steps"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="xxl:col-span-4 xl:col-span-12 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">
                                        Custom messages
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="flex flex-wrap items-center justify-between">
                                        <p class="text-[.875rem] mb-0 font-semibold">Your rating is much
                                            appreciated&#128079; :</p>
                                        <div id="custom-messages"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="xxl:col-span-6 xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">
                                        Unlimited number of stars readOnly
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="flex flex-wrap items-center justify-between">
                                        <p class="text-[.875rem] mb-0 font-semibold">Thanks for rating :</p>
                                        <div id="stars-unlimited"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="xxl:col-span-6 xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">
                                        5 Star rater with custom isBusyText and simulated backend
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="flex flex-wrap items-center justify-between">
                                        <p class="text-[.875rem] mb-0 font-semibold">Thanks for rating :</p>
                                        <div id="stars-busytext"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="xxl:col-span-4 xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">
                                        On hover event
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="flex flex-wrap items-center justify-between">
                                        <p class="text-[.875rem] mb-0 font-semibold">Please give your valuable rating :</p>
                                        <div class="flex flex-wrap items-center">
                                            <div id="stars-hover"></div>
                                            <span class="live-rating badge bg-success/10 text-success ms-4">
                                                1
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="xxl:col-span-4 xl:col-span-6 col-span-12">
                            <div class="box custom-box">
                                <div class="box-header">
                                    <div class="box-title">
                                        Clear/reset rater
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="flex flex-wrap items-center justify-between">
                                        <p class="text-[.875rem] mb-0 font-semibold">Thank You so much for your support :
                                        </p>
                                        <div class="flex flex-wrap items-center">
                                            <div id="rater-reset"></div>
                                            <button type="button" aria-label="button"
                                                class="ti-btn btn-wave ti-btn-icon ti-btn-sm ti-btn-danger !ms-4"
                                                id="rater-reset-button">
                                                <i class="ri-restart-line"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-2 -->

@endsection

@section('scripts')

        <!-- Rater JS -->
        <script src="{{asset('build/assets/libs/rater-js/index.js')}}"></script>

        <!-- Internal Ratings JS -->
        @vite('resources/assets/js/ratings.js')
        

@endsection