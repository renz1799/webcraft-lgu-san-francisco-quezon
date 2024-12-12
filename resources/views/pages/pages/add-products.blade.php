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
                          <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Add Products</h3>
                      </div>
                      <ol class="flex items-center whitespace-nowrap min-w-0">
                          <li class="text-[0.813rem] ps-[0.5rem]">
                            <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                              Ecommerce
                              <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                            </a>
                          </li>
                          <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                            Add Products
                          </li>
                      </ol>
                  </div>
                  <!-- Page Header Close -->

                  <!-- Start::row-1 -->
                  <div class="grid grid-cols-12 gap-6">
                      <div class="xl:col-span-12  col-span-12">
                          <div class="box">
                              <div class="box-body add-products !p-0">
                                  <div class="p-6">
                                      <div class="grid grid-cols-12 md:gap-x-[3rem] gap-0">
                                          <div class="xxl:col-span-6 xl:col-span-12 lg:col-span-12 md:col-span-6 col-span-12">
                                              <div class="grid grid-cols-12 gap-4">
                                                  <div class="xl:col-span-12 col-span-12">
                                                      <label for="product-name-add" class="form-label">Product Name</label>
                                                      <input type="text" class="form-control w-full !rounded-md" id="product-name-add" placeholder="Name">
                                                      <label for="product-name-add" class="form-label mt-1 text-[0.75rem] opacity-[0.5] !text-[#8c9097] dark:text-white/50 !mb-0">*Product Name should not exceed 30 characters</label>
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="product-category-add" class="form-label">Category</label>
                                                      <select class="form-control w-full !rounded-md" data-trigger name="product-category-add" id="product-category-add">
                                                          <option value="">Category</option>
                                                          <option value="Clothing">Clothing</option>
                                                          <option value="Footwear">Footwear</option>
                                                          <option value="Accesories">Accesories</option>
                                                          <option value="Grooming">Grooming</option>
                                                          <option value="Ethnic & Festive">Ethnic &amp; Festive</option>
                                                          <option value="Jewellery">Jewellery</option>
                                                          <option value="Toys & Babycare">Toys &amp; Babycare</option>
                                                          <option value="Festive Gifts">Festive Gifts</option>
                                                          <option value="Kitchen">Kitchen</option>
                                                          <option value="Dining">Dining</option>
                                                          <option value="Home Decors">Home Decors</option>
                                                          <option value="Stationery">Stationery</option>
                                                      </select>
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="product-gender-add" class="form-label">Gender</label>
                                                      <select class="form-control w-full !rounded-md" data-trigger name="product-gender-add" id="product-gender-add">
                                                          <option value="">Select</option>
                                                          <option value="All">All</option>
                                                          <option value="Male">Male</option>
                                                          <option value="Female">Female</option>
                                                          <option value="Others">Others</option>
                                                      </select>
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="product-size-add" class="form-label">Size</label>
                                                      <select class="form-control w-full !rounded-md" data-trigger name="product-size-add" id="product-size-add">
                                                          <option value="">Select</option>
                                                          <option value="Extra Small">Extra Small</option>
                                                          <option value="Small">Small</option>
                                                          <option value="Medium">Medium</option>
                                                          <option value="Large">Large</option>
                                                          <option value="Extra Large">Extra Large</option>
                                                      </select>
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="product-brand-add" class="form-label">Brand</label>
                                                      <select class="form-control w-full !rounded-md" data-trigger name="product-brand-add" id="product-brand-add">
                                                          <option value="">Select</option>
                                                          <option value="Armani">Armani</option>
                                                          <option value="Lacoste">Lacoste</option>
                                                          <option value="Puma">Puma</option>
                                                          <option value="Spykar">Spykar</option>
                                                          <option value="Mufti">Mufti</option>
                                                          <option value="Home Town">Home Town</option>
                                                          <option value="Arrabi">Arrabi</option>
                                                      </select>
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12 color-selection">
                                                      <label for="product-color-add" class="form-label">Colors</label>
                                                      <select class="form-control w-full !rounded-md" name="product-color-add" id="product-color-add" multiple>
                                                          <option value="White">White</option>
                                                          <option value="Black">Black</option>
                                                          <option value="Red">Red</option>
                                                          <option value="Orange">Orange</option>
                                                          <option value="Yellow">Yellow</option>
                                                          <option value="Green">Green</option>
                                                          <option value="Blue">Blue</option>
                                                          <option value="Pink">Pink</option>
                                                          <option value="Purple">Purple</option>
                                                      </select>
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="product-cost-add" class="form-label">Enter Cost</label>
                                                      <input type="text" class="form-control w-full !rounded-md" id="product-cost-add" placeholder="Cost">
                                                      <label for="product-cost-add" class="form-label mt-1 text-[0.75rem] opacity-[0.5] !text-[#8c9097] dark:text-white/50 mb-0">*Mention final price of the product</label>
                                                  </div>
                                                  <div class="xl:col-span-12 col-span-12">
                                                      <label for="product-description-add" class="form-label">Product Description</label>
                                                      <textarea class="form-control w-full !rounded-md" id="product-description-add" rows="2"></textarea>
                                                      <label for="product-description-add" class="form-label mt-1 text-[0.75rem] opacity-[0.5] !text-[#8c9097] dark:text-white/50 mb-0">*Description should not exceed 500 letters</label>
                                                  </div>
                                                  <div class="xl:col-span-12 col-span-12 mb-4">
                                                      <label class="form-label">Product Features</label>
                                                      <div id="product-features"></div>
                                                  </div>
                                              </div>
                                          </div>
                                          <div class="xxl:col-span-6 xl:col-span-12 lg:col-span-12 md:col-span-6 col-span-12">
                                              <div class="grid grid-cols-12 gap-4">
                                                  <div class="xl:col-span-4 col-span-12">
                                                      <label for="product-actual-price" class="form-label">Actual Price</label>
                                                      <input type="text" class="form-control w-full !rounded-md" id="product-actual-price" placeholder="Actual Price">
                                                  </div>
                                                  <div class="xl:col-span-4 col-span-12">
                                                      <label for="product-dealer-price" class="form-label">Dealer Price</label>
                                                      <input type="text" class="form-control w-full !rounded-md" id="product-dealer-price" placeholder="Dealer Price">
                                                  </div>
                                                  <div class="xl:col-span-4 col-span-12">
                                                      <label for="product-discount" class="form-label">Discount</label>
                                                      <input type="text" class="form-control w-full !rounded-md" id="product-discount" placeholder="Discount in %">
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="product-type" class="form-label">Product Type</label>
                                                      <input type="text" class="form-control w-full !rounded-md" id="product-type" placeholder="Type">
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="product-discount" class="form-label">Item Weight</label>
                                                      <input type="text" class="form-control w-full !rounded-md" id="product-discount1" placeholder="Weight in gms">
                                                  </div>
                                                  <div class="xl:col-span-12 col-span-12 product-documents-container">
                                                      <p class="font-semibold mb-2 text-[0.875rem]">Product Images :</p>
                                                      <input type="file" class="product-Images w-full" name="filepond" multiple data-allow-reorder="true" data-max-file-size="3MB" data-max-files="6">
                                                  </div>
                                                  <label class="form-label opacity-[0.5] mt-4 xl:col-span-12 col-span-12">Minimum 0f 6 images are need to be uploaded,make sure the image size match the proper background size and all images should be uniformly maintained with width and height to the image container,image size should not exceed 2MB,once uploaded to change the image you need to wait minimum of 24hrs. </label>
                                                  <div class="xl:col-span-12 col-span-12 product-documents-container">
                                                      <p class="font-semibold mb-2 text-[0.875rem]">Warrenty Documents :</p>
                                                      <input type="file" class="product-documents w-full" multiple data-allow-reorder="true" data-max-file-size="3MB" data-max-files="6">
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="publish-date" class="form-label">Publish Date</label>
                                                      <input type="text" class="form-control w-full !rounded-md" id="publish-date" placeholder="Choose date">
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="publish-time" class="form-label">Publish Time</label>
                                                      <input type="text" class="form-control w-full !rounded-md" id="publish-time" placeholder="Choose time">
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="product-status-add" class="form-label">Published Status</label>
                                                      <select class="form-control w-full !rounded-md" data-trigger name="product-status-add" id="product-status-add">
                                                          <option value="">Select</option>
                                                          <option value="Published">Published</option>
                                                          <option value="Scheduled">Scheduled</option>
                                                      </select>
                                                  </div>
                                                  <div class="xl:col-span-6 col-span-12">
                                                      <label for="product-tags" class="form-label">Product Tags</label>
                                                      <select class="form-control w-full !rounded-md" name="product-tags" id="product-tags" multiple>
                                                          <option value="Relaxed" selected>Relaxed</option>
                                                          <option value="Solid">Solid</option>
                                                          <option value="Washed">Washed</option>
                                                          <option value="Plain" selected>Plain</option>
                                                      </select>
                                                  </div>
                                                  <div class="xl:col-span-12 col-span-12">
                                                      <label for="product-status-add1" class="form-label">Availability</label>
                                                      <select class="form-control w-full !rounded-md" data-trigger name="product-status-add1" id="product-status-add1">
                                                          <option value="">Select</option>
                                                          <option value="In Stock">In Stock</option>
                                                          <option value="Out Of Stock">Out Of Stock</option>
                                                      </select>
                                                  </div>
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="px-6 py-4 border-t border-dashed dark:border-defaultborder/10 sm:flex justify-end">
                                      <button type="button" class="ti-btn btn-wave ti-btn-primary !font-medium m-1">Add Product<i class="bi bi-plus-lg ms-2"></i></button>
                                      <button type="button" class="ti-btn btn-wave ti-btn-success !font-medium m-1">Save Product<i class="bi bi-download ms-2"></i></button>
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

        <!-- Add-Products JS -->
        @vite('resources/assets/js/add-products.js')


@endsection