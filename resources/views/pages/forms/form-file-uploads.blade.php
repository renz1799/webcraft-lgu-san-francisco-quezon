@extends('layouts.master')

@section('styles')
 
        <!-- Dropzone File Upload  Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/dropzone/dropzone.css')}}">

        <!-- filepond File Upload  Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond/filepond.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css')}}">
      
@endsection

@section('content')

                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> File Uploads</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    Form Elements
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                    File Uploads
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="col-span-12 lg:col-span-6 ">
                                <div class="box">
                                    <div class="box-header">
                                        <h5 class="box-title">Basic File Input</h5>
                                    </div>
                                    <div class="box-body">
                                        <div>
                                            <label for="file-input" class="sr-only">Choose file</label>
                                            <input type="file" name="file-input" id="file-input" class="block w-full border border-gray-200 focus:shadow-sm dark:focus:shadow-white/10 rounded-sm text-sm focus:z-10 focus:outline-0 focus:border-gray-200 dark:focus:border-white/10 dark:border-white/10 dark:text-white/50
                                              file:border-0
                                              file:bg-light file:me-4
                                              file:py-3 file:px-4
                                              dark:file:bg-black/20 dark:file:text-white/50">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 lg:col-span-6 ">
                                <div class="box">
                                    <div class="box-header">
                                        <h5 class="box-title">File Input Buttons</h5>
                                    </div>
                                    <div class="box-body">
                                        <div>
                                            <label class="block">
                                                <span class="sr-only">Choose Profile photo</span>
                                                <input type="file" class="block w-full border border-gray-200 focus:shadow-sm dark:focus:shadow-white/10 rounded-sm text-sm focus:z-10 focus:outline-0 focus:border-gray-200 dark:focus:border-white/10 dark:border-white/10 dark:text-[#8c9097] dark:text-white/50
                                                file:me-4 file:py-2 file:px-4
                                                file:rounded-s-sm file:border-0
                                                file:text-sm file:font-semibold
                                                file:bg-primary file:text-white
                                                hover:file:bg-primary focus-visible:outline-none
                                              ">
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End::row-1 -->

                        <!-- Start::row-2 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="col-span-12 lg:col-span-6">
                                <div class="box">
                                    <div class="box-header">
                                        <h5 class="box-title">File Input Sizes</h5>
                                    </div>
                                    <div class="box-body space-y-3">
                                        <div>
                                            <label for="small-file-input" class="sr-only">Choose file</label>
                                            <input type="file" name="small-file-input" id="small-file-input" class="block w-full border border-gray-200 focus:shadow-sm dark:focus:shadow-white/10 rounded-sm text-sm focus:z-10 focus:outline-0 focus:border-gray-200 dark:focus:border-white/10 dark:border-white/10 dark:text-white/50
                                              file:border-0
                                              file:bg-light file:me-4
                                              file:py-2 file:px-4
                                              dark:file:bg-black/20 dark:file:text-white/50">
                                        </div>

                                        <div>
                                            <label for="file-input-medium" class="sr-only">Choose file</label>
                                            <input type="file" name="file-input-medium" id="file-input-medium" class="block w-full border border-gray-200 focus:shadow-sm dark:focus:shadow-white/10 rounded-sm text-sm focus:z-10 focus:outline-0 focus:border-gray-200 dark:focus:border-white/10 dark:border-white/10 dark:text-white/50
                                              file:border-0
                                              file:bg-light file:me-4
                                              file:py-3 file:px-4
                                              dark:file:bg-black/20 dark:file:text-white/50">
                                        </div>

                                        <div>
                                            <label for="large-file-input" class="sr-only">Choose file</label>
                                            <input type="file" name="large-file-input" id="large-file-input" class="block w-full border border-gray-200 focus:shadow-sm dark:focus:shadow-white/10 rounded-sm text-sm focus:z-10 focus:outline-0 focus:border-gray-200 dark:focus:border-white/10 dark:border-white/10 dark:text-[#8c9097] dark:text-white/50
                                              file:border-0
                                              file:bg-light file:me-4
                                              file:py-3 file:px-4 file:sm:py-5
                                              dark:file:bg-black/20 dark:file:text-white/50">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 lg:col-span-6">
                                <div class="box">
                                    <div class="box-header">
                                        <h5 class="box-title">Dropzone File Upload</h5>
                                    </div>
                                    <div class="box-body">
                                        <div data-single="true" action="/file-upload" class="dropzone"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Start::row-2 -->
                        
                        <!-- End::row-3 -->
                        <div class="grid grid-cols-12 gap-6">
                            <div class="col-span-12 lg:col-span-4">
                                <div class="box">
                                    <div class="box-header">
                                        <h5 class="box-title">Single File Upload</h5>
                                    </div>
                                    <div class="box-body">
                                        <input type="file" class="filepond basic-filepond" data-allow-reorder="true" data-max-file-size="3MB" data-max-files="1">
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 lg:col-span-4">
                                <div class="box">
                                    <div class="box-header">
                                        <h5 class="box-title">Multiple File Upload</h5>
                                    </div>
                                    <div class="box-body">
                                        <input type="file" class="filepond multiple-filepond" multiple data-allow-reorder="true" data-max-file-size="3MB" data-max-files="5">
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 lg:col-span-4">
                                <div class="box">
                                    <div class="box-header">
                                        <h5 class="box-title">circular File Upload</h5>
                                    </div>
                                    <div class="box-body">
                                        <input type="file" class="filepond circular-filepond" accept="image/png, image/jpeg, image/gif">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End::row-3 -->

@endsection

@section('scripts')

        <!-- DropZone File Upload JS -->
        <script src="{{asset('build/assets/libs/dropzone/dropzone-min.js')}}"></script>

        <!-- Filepond File Upload JS -->
        <script src="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js')}}"></script>
        <script src="{{asset('build/assets/libs/filepond/filepond.min.js')}}"></script>
        @vite('resources/assets/js/fileupload.js')

        
@endsection