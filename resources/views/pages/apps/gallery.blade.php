@extends('layouts.master')

@section('styles')

        <!-- GLightbox CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/glightbox/css/glightbox.min.css')}}">
      
@endsection

@section('content')

                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Gallery</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                Apps
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                Gallery
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row-1 -->
                    <div class="grid grid-cols-12 gap-x-6">
                        <div class="lg:col-span-3 md:col-span-3 sm:col-span-6 col-span-12">
                            <a href="{{asset('build/assets/images/media/media-40.jpg')}}" class="glightbox box" data-gallery="gallery1">
                                <img src="{{asset('build/assets/images/media/media-40.jpg')}}" alt="image" >
                            </a>
                        </div>
                        <div class="lg:col-span-3 md:col-span-3 sm:col-span-6 col-span-12">
                            <a href="{{asset('build/assets/images/media/media-41.jpg')}}" class="glightbox box" data-gallery="gallery1">
                                <img src="{{asset('build/assets/images/media/media-41.jpg')}}" alt="image" >
                            </a>
                        </div>
                        <div class="lg:col-span-3 md:col-span-3 sm:col-span-6 col-span-12">
                            <a href="{{asset('build/assets/images/media/media-42.jpg')}}" class="glightbox box" data-gallery="gallery1">
                                <img src="{{asset('build/assets/images/media/media-42.jpg')}}" alt="image" >
                            </a>
                        </div>
                        <div class="lg:col-span-3 md:col-span-3 sm:col-span-6 col-span-12">
                            <a href="{{asset('build/assets/images/media/media-43.jpg')}}" class="glightbox box" data-gallery="gallery1">
                                <img src="{{asset('build/assets/images/media/media-43.jpg')}}" alt="image" >
                            </a>
                        </div>
                        <div class="lg:col-span-3 md:col-span-3 sm:col-span-6 col-span-12">
                            <a href="{{asset('build/assets/images/media/media-44.jpg')}}" class="glightbox box" data-gallery="gallery1">
                                <img src="{{asset('build/assets/images/media/media-44.jpg')}}" alt="image" >
                            </a>
                        </div>
                        <div class="lg:col-span-3 md:col-span-3 sm:col-span-6 col-span-12">
                            <a href="{{asset('build/assets/images/media/media-45.jpg')}}" class="glightbox box" data-gallery="gallery1">
                                <img src="{{asset('build/assets/images/media/media-45.jpg')}}" alt="image" >
                            </a>
                        </div>
                        <div class="lg:col-span-3 md:col-span-3 sm:col-span-6 col-span-12">
                            <a href="{{asset('build/assets/images/media/media-46.jpg')}}" class="glightbox box" data-gallery="gallery1">
                                <img src="{{asset('build/assets/images/media/media-46.jpg')}}" alt="image" >
                            </a>
                        </div>
                        <div class="lg:col-span-3 md:col-span-3 sm:col-span-6 col-span-12">
                            <a href="{{asset('build/assets/images/media/media-60.jpg')}}" class="glightbox box" data-gallery="gallery1">
                                <img src="{{asset('build/assets/images/media/media-60.jpg')}}" alt="image" >
                            </a>
                        </div>
                    </div>
                    <!--End::row-1 -->

@endsection

@section('scripts')

        <!-- Gallery JS -->
        <script src="{{asset('build/assets/libs/glightbox/js/glightbox.min.js')}}"></script>
        @vite('resources/assets/js/gallery.js')
        

@endsection