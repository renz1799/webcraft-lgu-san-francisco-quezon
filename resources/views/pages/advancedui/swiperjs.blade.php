@extends('layouts.master')

@section('styles')

        <!-- Swiper CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/swiper/swiper-bundle.min.css')}}">
      
@endsection

@section('content')

                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Swiper JS</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                  Advanced Ui
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                  Swiper JS
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Basic Swiper
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper swiper-basic">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-27.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-26.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-25.jpg')}}"  class="!rounded-md" alt=""></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Swiper With Navigation
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper swiper-navigation">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-29.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-30.jpg')}}" class="!rounded-md" alt=""></div>
                                            </div>
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                          </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Swiper with Pagination
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper pagination">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-32.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-31.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-33.jpg')}}" class="!rounded-md" alt=""></div>
                                            </div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Dynamic Pagination
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper pagination-dynamic">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-21.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-17.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-16.jpg')}}" class="!rounded-md" alt=""></div>
                                            </div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Pagination With Progress
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper pagination-progress">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-12.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-8.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-5.jpg')}}" class="!rounded-md" alt=""></div>
                                            </div>
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Pagination Fraction
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper swiper-basic pagination-fraction">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-16.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-30.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-31.jpg')}}" class="!rounded-md" alt=""></div>
                                            </div>
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                            <div class="swiper-pagination"></div>
                                          </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Custom Paginatioin
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper  custom-pagination">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-25.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-5.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-33.jpg')}}" class="!rounded-md" alt=""></div>
                                            </div>
                                            <div class="swiper-pagination"></div>
                                          </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Scrollbar Swiper
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper scrollbar-swiper">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-30.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-29.jpg')}}" class="!rounded-md" alt=""></div>
                                            </div>
                                            <div class="swiper-scrollbar"></div>
                                          </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Vertical Swiper
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper mySwiper5 swiper-vertical !h-[352px]">
                                          <div class="swiper-wrapper">
                                            <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-8.jpg')}}" class="!rounded-md" alt=""></div>
                                            <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-32.jpg')}}" class="!rounded-md" alt=""></div>
                                            <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-17.jpg')}}" class="!rounded-md" alt=""></div>
                                          </div>
                                          <div class="swiper-pagination"></div>
                                        </div>
                                </div>
                            </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Mouse Wheel Control
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper vertical vertical-mouse-control !h-[352px]">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-28.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-30.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-32.jpg')}}" class="!rounded-md" alt=""></div>
                                            </div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Keyboard Control
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper keyboard-control">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-31.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-12.jpg')}}" class="!rounded-md" alt=""></div>
                                                <div class="swiper-slide"><img src="{{asset('build/assets/images/media/media-8.jpg')}}" class="!rounded-md" alt=""></div>
                                            </div>
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Effect Cube
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper swiper-effect-cube !h-[352px]">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <img src="{{asset('build/assets/images/media/media-62.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img src="{{asset('build/assets/images/media/media-63.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img src="{{asset('build/assets/images/media/media-64.jpg')}}" alt="img">
                                                </div>
                                            </div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Effect Fade
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper swiper-fade !h-[352px]">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide ">
                                                    <img src="{{asset('build/assets/images/media/media-18.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img src="{{asset('build/assets/images/media/media-19.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img src="{{asset('build/assets/images/media/media-20.jpg')}}" alt="img">
                                                </div>
                                            </div>
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xl:col-span-4 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Effect Flip
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper swiper-flip !h-[352px]">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <img src="{{asset('build/assets/images/media/media-20.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img src="{{asset('build/assets/images/media/media-62.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img src="{{asset('build/assets/images/media/media-63.jpg')}}" alt="img">
                                                </div>
                                            </div>
                                            <div class="swiper-button-next"></div>
                                            <div class="swiper-button-prev"></div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End::row-1 -->

                        <!-- Start:: row-2 -->
                        <div class="grid grid-cols-12">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Effect Coverflow
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="swiper swiper-overflow">
                                            <div class="swiper-wrapper">
                                                <div class="swiper-slide">
                                                    <img class="img-fluid" src="{{asset('build/assets/images/media/media-40.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img class="img-fluid" src="{{asset('build/assets/images/media/media-41.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img class="img-fluid" src="{{asset('build/assets/images/media/media-42.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img class="img-fluid" src="{{asset('build/assets/images/media/media-43.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img class="img-fluid" src="{{asset('build/assets/images/media/media-44.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img class="img-fluid" src="{{asset('build/assets/images/media/media-59.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img class="img-fluid" src="{{asset('build/assets/images/media/media-46.jpg')}}" alt="img">
                                                </div>
                                                <div class="swiper-slide">
                                                    <img class="img-fluid" src="{{asset('build/assets/images/media/media-61.jpg')}}" alt="img">
                                                </div>
                                            </div>
                                            <div class="swiper-pagination"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End:: row-2 -->
                       
@endsection

@section('scripts')

        <!-- Swiper JS -->
        <script src="{{asset('build/assets/libs/swiper/swiper-bundle.min.js')}}"></script>

        <!-- Internal Swiper JS -->
        @vite('resources/assets/js/swiper.js')
        

@endsection