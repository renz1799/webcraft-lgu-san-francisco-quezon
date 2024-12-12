@extends('layouts.master')

@section('styles')
 
        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">

        <!-- Tom Select Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/tom-select/css/tom-select.default.min.css')}}">

        <link rel="stylesheet" href="{{asset('build/assets/libs/quill/quill.snow.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/quill/quill.bubble.css')}}">

        <!-- Filepond CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond/filepond.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css')}}">
        <link rel="stylesheet" href="{{asset('build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css')}}">

        <!-- Date & Time Picker CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/flatpickr/flatpickr.min.css')}}">
      
@endsection

@section('content')
 
                        <!-- Page Header -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Blog Create </h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                  <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                  Blog
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                  </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                    Blog Create 
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="xxl:col-span-9 xl:col-span-12 lg:col-span-12 md:col-span-12 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">New Blog</div>
                                    </div>
                                    <div class="box-body">
                                        <div class="grid grid-cols-12 gap-4">
                                            <div class="xl:col-span-12 col-span-12">
                                                <label for="blog-title" class="form-label">Blog Title</label>
                                                <input type="text" class="form-control block w-full text-[0.875rem] !rounded-md" id="blog-title" placeholder="Blog Title">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label for="blog-category" class="form-label">Blog Category</label>
                                                <select class="form-control block w-full text-[0.875rem] !rounded-md" data-trigger name="blog-category" id="blog-category">
                                                    <option value="">Select Category</option>
                                                    <option value="Choice 1">Nature</option>
                                                    <option value="Choice 2">Sports</option>
                                                    <option value="Choice 3">Food</option>
                                                    <option value="Choice 3">Travel</option>
                                                    <option value="Choice 3">Fashion</option>
                                                    <option value="Choice 3">Beauty</option>
                                                </select>
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label for="blog-author" class="form-label">Blog Author</label>
                                                <input type="text" class="form-control block w-full text-[0.875rem] !rounded-md" id="blog-author" placeholder="Enter Name">
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label for="blog-author-email" class="form-label">Email</label>
                                                <input type="text" class="form-control block w-full text-[0.875rem] !rounded-md" id="blog-author-email" placeholder="Enter Email">
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label for="publish-date" class="form-label">Publish Date</label>
                                                <input type="text" class="form-control block w-full text-[0.875rem] !rounded-md" id="publish-date" placeholder="Choose date">
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label for="publish-time" class="form-label">Publish Time</label>
                                                <input type="text" class="form-control block w-full text-[0.875rem] !rounded-md" id="publish-time" placeholder="Choose time">
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label for="product-status-add" class="form-label">Published Status</label>
                                                <select class="form-control block w-full text-[0.875rem] !rounded-md" data-trigger name="product-status-add" id="product-status-add">
                                                    <option value="">Select</option>
                                                    <option value="Published">Published</option>
                                                    <option value="Scheduled">Hold</option>
                                                </select>
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label for="blog-tags" class="form-label">Blog Tags</label>
                                                <select class="form-control block w-full text-[0.875rem] !rounded-md" name="blog-tags" id="blog-tags" multiple>
                                                    <option value="Top Blog" selected>Top Blog</option>
                                                    <option value="Blogger">Blogger</option>
                                                    <option value="Adventure">Adventure</option>
                                                    <option value="Landscape" selected>Landscape</option>
                                                </select>
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label class="form-label">Blog Content</label>
                                                <div id="blog-content"></div>
                                            </div>
                                            <div class="xl:col-span-12 col-span-12 blog-images-container">
                                                <label for="blog-author-email" class="form-label">Blog Images</label>
                                                <input type="file" class="blog-images" name="filepond" multiple data-allow-reorder="true" data-max-file-size="3MB" data-max-files="6">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label class="form-label">Blog Type</label>
                                                <div class="flex items-center">
                                                    <div class="form-check !ps-0 me-4">
                                                        <input class="form-check-input" type="radio" name="blog-type" id="blog-free1" checked>
                                                        <label class="form-check-label" for="blog-free1">
                                                            Free
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="blog-type" id="blog-paid1">
                                                        <label class="form-check-label" for="blog-paid1">
                                                            Paid
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="box-footer">
                                        <div class="text-end">
                                            <button type="button" class="ti-btn btn-wave !py-1 !px-2 ti-btn-light !text-[0.75rem] !font-medium me-2">Save As Draft</button>
                                            <button type="button" class="ti-btn btn-wave bg-primary text-white !py-1 !px-2 !text-[0.75rem] !font-medium">Post Blog</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="xxl:col-span-3 xl:col-span-12 lg:col-span-12 md:col-span-12 sm:col-span-12 col-span-12">
                                <div class="box">
                                    <div class="box-header">
                                        <div class="box-title">
                                            Recent Blogs
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <div class="flex gap-2 flex-wrap items-center">
                                                    <span class="avatar avatar-xl me-1">
                                                        <img src="{{asset('build/assets/images/media/media-39.jpg')}}" class="img-fluid !rounded-md" alt="...">
                                                    </span>
                                                    <div class="flex-grow">
                                                        <a href="javascript:void(0);" class="text-[0.875rem] font-semibold mb-0">Animals</a>
                                                        <p class="mb-1 popular-blog-content text-truncate">
                                                            There are many variations of passages of Lorem Ipsum available
                                                        </p>
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">24,Nov 2022 - 18:27</span>
                                                    </div>
                                                    <div>
                                                        <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-light ti-btn-sm rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></button>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="flex gap-2 flex-wrap  items-center">
                                                    <span class="avatar avatar-xl me-1">
                                                        <img src="{{asset('build/assets/images/media/media-56.jpg')}}" class="img-fluid !rounded-md" alt="...">
                                                    </span>
                                                    <div class="flex-grow">
                                                        <a href="javascript:void(0);" class="text-[0.875rem] font-semibold mb-0">Travel</a>
                                                        <p class="mb-1 popular-blog-content text-truncate">
                                                            Latin words, combined with a handful of model sentence
                                                        </p>
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">28,Nov 2022 - 10:45</span>
                                                    </div>
                                                    <div>
                                                        <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-light ti-btn-sm rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></button>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="flex gap-2 flex-wrap items-center">
                                                    <span class="avatar avatar-xl me-1">
                                                        <img src="{{asset('build/assets/images/media/media-54.jpg')}}" class="img-fluid !rounded-md" alt="...">
                                                    </span>
                                                    <div class="flex-grow">
                                                        <a href="javascript:void(0);" class="text-[0.875rem] font-semibold mb-0">Interior</a>
                                                        <p class="mb-1 popular-blog-content text-truncate">
                                                            Contrary to popular belief, Lorem Ipsum is not simply random
                                                        </p>
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">30,Nov 2022 - 08:32</span>
                                                    </div>
                                                    <div>
                                                        <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-light ti-btn-sm rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></button>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="flex gap-2 flex-wrap items-center">
                                                    <span class="avatar avatar-xl me-1">
                                                        <img src="{{asset('build/assets/images/media/media-52.jpg')}}" class="img-fluid !rounded-md" alt="...">
                                                    </span>
                                                    <div class="flex-grow">
                                                        <a href="javascript:void(0);" class="text-[0.875rem] font-semibold mb-0">Nature</a>
                                                        <p class="mb-1 popular-blog-content text-truncate">
                                                            It was popularised in the 1960s with the release of Letraset sheets containing
                                                        </p>
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">3,Dec 2022 - 12:56</span>
                                                    </div>
                                                    <div>
                                                        <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-light ti-btn-sm rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></button>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="flex gap-2 flex-wrap        items-center">
                                                    <span class="avatar avatar-xl me-1">
                                                        <img src="{{asset('build/assets/images/media/media-74.jpg')}}" class="img-fluid !rounded-md" alt="...">
                                                    </span>
                                                    <div class="flex-grow">
                                                        <a href="javascript:void(0);" class="text-[0.875rem] font-semibold mb-0">Health</a>
                                                        <p class="mb-1 popular-blog-content text-truncate">
                                                            It was popularised in the 1960s with the release of Letraset sheets containing
                                                        </p>
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">16,Dec 2022 - 04:56</span>
                                                    </div>
                                                    <div>
                                                        <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-light ti-btn-sm rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></button>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="flex gap-2 flex-wrap        items-center">
                                                    <span class="avatar avatar-xl me-1">
                                                        <img src="{{asset('build/assets/images/media/media-49.jpg')}}" class="img-fluid !rounded-md" alt="...">
                                                    </span>
                                                    <div class="flex-grow">
                                                        <a href="javascript:void(0);" class="text-[0.875rem] font-semibold mb-0">Food</a>
                                                        <p class="mb-1 popular-blog-content text-truncate">
                                                            It was popularised in the 1960s with the release of Letraset sheets containing
                                                        </p>
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">31,Dec 2022 - 18:06</span>
                                                    </div>
                                                    <div>
                                                        <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-light ti-btn-sm rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></button>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                    <div class="flex gap-2 flex-wrap items-center">
                                                    <span class="avatar avatar-xl me-1">
                                                        <img src="{{asset('build/assets/images/media/media-76.jpg')}}" class="img-fluid !rounded-md" alt="...">
                                                    </span>
                                                    <div class="flex-grow">
                                                        <a href="javascript:void(0);" class="text-[0.875rem] font-semibold mb-0">Travel</a>
                                                        <p class="mb-1 popular-blog-content text-truncate">
                                                            It was popularised in the 1960s with the release of Letraset sheets containing
                                                        </p>
                                                        <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">15,Dec 2022 - 14:31</span>
                                                    </div>
                                                    <div>
                                                        <button aria-label="button" type="button" class="ti-btn btn-wave ti-btn-light ti-btn-sm rtl:rotate-180"><i class="ri-arrow-right-s-line"></i></button>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item text-center">
                                                <button type="button" class="ti-btn btn-wave ti-btn-primary !font-medium">Load more</button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End::row-1 -->

@endsection

@section('scripts')
        
        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

        <!-- Date & Time Picker JS -->
        <script src="{{asset('build/assets/libs/flatpickr/flatpickr.min.js')}}"></script>

        <!-- Quill Editor JS -->
        <script src="{{asset('build/assets/libs/quill/quill.min.js')}}"></script>

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

        <!-- Internal Create Blog JS -->
        @vite('resources/assets/js/blog-create.js')


@endsection