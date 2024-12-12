@extends('layouts.master')

@section('styles')
 
      
@endsection

@section('content')
 
                      <!-- Page Header -->
                      <div class="block justify-between page-header md:flex">
                          <div>
                              <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Products List</h3>
                          </div>
                          <ol class="flex items-center whitespace-nowrap min-w-0">
                              <li class="text-[0.813rem] ps-[0.5rem]">
                                <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                  Ecommerce
                                  <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                </a>
                              </li>
                              <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                  Products List
                              </li>
                          </ol>
                      </div>
                      <!-- Page Header Close -->

                      <!-- Start::row-1 -->
                      <div class="grid grid-cols-12 gap-6">
                          <div class="xl:col-span-12 col-span-12">
                              <div class="box">
                                  <div class="box-header">
                                      <div class="box-title">
                                          Products List
                                      </div>
                                  </div>
                                  <div class="box-body">
                                      <div class="table-responsive mb-4">
                                          <table class="table whitespace-nowrap table-bordered min-w-full">
                                              <thead>
                                                  <tr>
                                                      <th scope="col" class="!text-start">
                                                          <input class="form-check-input check-all" type="checkbox" id="all-products" value="" aria-label="...">
                                                      </th>
                                                      <th scope="col" class="text-start">Product</th>
                                                      <th scope="col" class="text-start">Category</th>
                                                      <th scope="col" class="text-start">Price</th>
                                                      <th scope="col" class="text-start">Stock</th>
                                                      <th scope="col" class="text-start">Gender</th>
                                                      <th scope="col" class="text-start">Seller</th>
                                                      <th scope="col" class="text-start">Published</th>
                                                      <th scope="col" class="text-start">Action</th>
                                                  </tr>
                                              </thead>
                                              <tbody>
                                                  <tr class="product-list">
                                                      <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product1" value="" aria-label="..."></td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-2">
                                                                  <span class="avatar avatar-md avatar-rounded">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/1.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div class="font-semibold">
                                                                  DapZem &amp; Co Blue Hoodie
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <span class="badge bg-light text-default">Clothing</span>
                                                      </td>
                                                      <td>$1,299</td>
                                                      <td>283</td>
                                                      <td>Male</td>
                                                      <td>Apilla.co.in</td>
                                                      <td>24,Nov 2022 - 04:42PM</td>
                                                      <td>
                                                          <div class="flex flex-row items-center !gap-2 text-[0.9375rem]">
                                                              <a aria-label="anchor" href="{{url('edit-products')}}"
                                                              class="ti-btn btn-wave  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-info/10 text-info hover:bg-info hover:text-white hover:border-info"><i
                                                                class="ri-pencil-line"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                              class="ti-btn btn-wave product-btn !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-danger/10 text-danger hover:bg-danger hover:text-white hover:border-danger"><i
                                                                class="ri-delete-bin-line"></i></a>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="product-list">
                                                      <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product2" value="" aria-label="..."></td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-2">
                                                                  <span class="avatar avatar-md avatar-rounded">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/2.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div class="font-semibold">
                                                                  Leather jacket for men
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <span class="badge bg-light text-default">Clothing</span>
                                                      </td>
                                                      <td>$799</td>
                                                      <td>98</td>
                                                      <td>Male</td>
                                                      <td>Donzo Company</td>
                                                      <td>18,Nov 2022 - 06:53AM</td>
                                                      <td>
                                                          <div class="flex flex-row items-center !gap-2 text-[0.9375rem]">
                                                              <a aria-label="anchor" href="{{url('edit-products')}}"
                                                              class="ti-btn btn-wave  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-info/10 text-info hover:bg-info hover:text-white hover:border-info"><i
                                                                class="ri-pencil-line"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                              class="ti-btn btn-wave product-btn !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-danger/10 text-danger hover:bg-danger hover:text-white hover:border-danger"><i
                                                                class="ri-delete-bin-line"></i></a>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="product-list">
                                                      <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product3" value="" aria-label="..."></td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-2">
                                                                  <span class="avatar avatar-md avatar-rounded">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/15.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div class="font-semibold">
                                                                  Orange Smart Watch
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <span class="badge bg-light text-default">Watches</span>
                                                      </td>
                                                      <td>$349</td>
                                                      <td>1,293</td>
                                                      <td>Male,Female</td>
                                                      <td>SlowTrack Company</td>
                                                      <td>21,Oct 2022 - 11:36AM</td>
                                                      <td>
                                                          <div class="flex flex-row items-center !gap-2 text-[0.9375rem]">
                                                              <a aria-label="anchor" href="{{url('edit-products')}}"
                                                              class="ti-btn btn-wave  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-info/10 text-info hover:bg-info hover:text-white hover:border-info"><i
                                                                class="ri-pencil-line"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                              class="ti-btn btn-wave product-btn !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-danger/10 text-danger hover:bg-danger hover:text-white hover:border-danger"><i
                                                                class="ri-delete-bin-line"></i></a>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="product-list">
                                                      <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product4" value="" aria-label="..."></td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-2">
                                                                  <span class="avatar avatar-md avatar-rounded">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/3.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div class="font-semibold">
                                                                  Winter Coat For Women
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <span class="badge bg-light text-default">Clothing</span>
                                                      </td>
                                                      <td>$189</td>
                                                      <td>322</td>
                                                      <td>Female</td>
                                                      <td>WoodHill.co.in</td>
                                                      <td>16,Oct 2022 - 12:45AM</td>
                                                      <td>
                                                          <div class="flex flex-row items-center !gap-2 text-[0.9375rem]">
                                                              <a aria-label="anchor" href="{{url('edit-products')}}"
                                                              class="ti-btn btn-wave  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-info/10 text-info hover:bg-info hover:text-white hover:border-info"><i
                                                                class="ri-pencil-line"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                              class="ti-btn btn-wave product-btn  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-danger/10 text-danger hover:bg-danger hover:text-white hover:border-danger"><i
                                                                class="ri-delete-bin-line"></i></a>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="product-list">
                                                      <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product5" value="" aria-label="..."></td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-2">
                                                                  <span class="avatar avatar-md avatar-rounded">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/4.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div class="font-semibold">
                                                                  Vintage White Full Sleeve Tee-Shirt
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <span class="badge bg-light text-default">Clothing</span>
                                                      </td>
                                                      <td>$2,499</td>
                                                      <td>194</td>
                                                      <td>Male,Female</td>
                                                      <td>Watches.co.in</td>
                                                      <td>12,Aug 2022 - 11:21AM</td>
                                                      <td>
                                                          <div class="flex flex-row items-center !gap-2 text-[0.9375rem]">
                                                              <a aria-label="anchor" href="{{url('edit-products')}}"
                                                              class="ti-btn btn-wave  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-info/10 text-info hover:bg-info hover:text-white hover:border-info"><i
                                                                class="ri-pencil-line"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                              class="ti-btn btn-wave product-btn !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-danger/10 text-danger hover:bg-danger hover:text-white hover:border-danger"><i
                                                                class="ri-delete-bin-line"></i></a>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="product-list">
                                                      <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product6" value="" aria-label="..."></td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-2">
                                                                  <span class="avatar avatar-md avatar-rounded">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/13.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div class="font-semibold">
                                                                  Orange Watch series (44mm)
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <span class="badge bg-light text-default">Watches</span>
                                                      </td>
                                                      <td>$899</td>
                                                      <td>267</td>
                                                      <td>Male,Female</td>
                                                      <td>Watches.co.in</td>
                                                      <td>05,Sep 2022 - 10:14AM</td>
                                                      <td>
                                                          <div class="flex flex-row items-center !gap-2 text-[0.9375rem]">
                                                              <a aria-label="anchor" href="{{url('edit-products')}}"
                                                              class="ti-btn btn-wave  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-info/10 text-info hover:bg-info hover:text-white hover:border-info"><i
                                                                class="ri-pencil-line"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                              class="ti-btn btn-wave product-btn !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-danger/10 text-danger hover:bg-danger hover:text-white hover:border-danger"><i
                                                                class="ri-delete-bin-line"></i></a>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="product-list">
                                                      <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product7" value="" aria-label="..."></td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-2">
                                                                  <span class="avatar avatar-md avatar-rounded">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/12.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div class="font-semibold">
                                                                  Sweater For Women
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <span class="badge bg-light text-default">Clothing</span>
                                                      </td>
                                                      <td>$499</td>
                                                      <td>143</td>
                                                      <td>Female</td>
                                                      <td>Louie Philippe</td>
                                                      <td>18,Nov 2022 - 14:35PM</td>
                                                      <td>
                                                          <div class="flex flex-row items-center !gap-2 text-[0.9375rem]">
                                                              <a aria-label="anchor" href="{{url('edit-products')}}"
                                                              class="ti-btn btn-wave  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-info/10 text-info hover:bg-info hover:text-white hover:border-info"><i
                                                                class="ri-pencil-line"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                              class="ti-btn btn-wave product-btn !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-danger/10 text-danger hover:bg-danger hover:text-white hover:border-danger"><i
                                                                class="ri-delete-bin-line"></i></a>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="product-list">
                                                      <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product8" value="" aria-label="..."></td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-2">
                                                                  <span class="avatar avatar-md avatar-rounded">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/16.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div class="font-semibold">
                                                                  Ikonic Smart Watch(40mm)
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <span class="badge bg-light text-default">Watches</span>
                                                      </td>
                                                      <td>$999</td>
                                                      <td>365</td>
                                                      <td>Female</td>
                                                      <td>Kohino.zaps.com</td>
                                                      <td>27,Nov 2022 - 05:12AM</td>
                                                      <td>
                                                          <div class="flex flex-row items-center !gap-2 text-[0.9375rem]">
                                                              <a aria-label="anchor" href="{{url('edit-products')}}"
                                                              class="ti-btn btn-wave  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-info/10 text-info hover:bg-info hover:text-white hover:border-info"><i
                                                                class="ri-pencil-line"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                              class="ti-btn btn-wave product-btn !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-danger/10 text-danger hover:bg-danger hover:text-white hover:border-danger"><i
                                                                class="ri-delete-bin-line"></i></a>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="product-list">
                                                      <td class="product-checkbox"><input class="form-check-input" type="checkbox" id="product9" value="" aria-label="..."></td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-2">
                                                                  <span class="avatar avatar-md avatar-rounded">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/23.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div class="font-semibold">
                                                                  Appole Watch Series 5
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <span class="badge bg-light text-default">Watches</span>
                                                      </td>
                                                      <td>$1,499</td>
                                                      <td>257</td>
                                                      <td>Male,Female</td>
                                                      <td>Appole Corporation</td>
                                                      <td>29,Nov 2022 - 16:32PM</td>
                                                      <td>
                                                          <div class="flex flex-row items-center !gap-2 text-[0.9375rem]">
                                                              <a aria-label="anchor" href="{{url('edit-products')}}"
                                                              class="ti-btn btn-wave  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-info/10 text-info hover:bg-info hover:text-white hover:border-info"><i
                                                                class="ri-pencil-line"></i></a>
                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                              class="ti-btn btn-wave product-btn  !gap-0 !m-0 !h-[1.75rem] !w-[1.75rem] text-[0.8rem] bg-danger/10 text-danger hover:bg-danger hover:text-white hover:border-danger"><i
                                                                class="ri-delete-bin-line"></i></a>
                                                          </div>
                                                      </td>
                                                  </tr>
                                              </tbody>
                                          </table>
                                      </div>
                                      <div class="sm:flex items-center justify-between flex-wrap">
                                          <nav aria-label="Page navigation" class="">
                                              <ul class="ti-pagination mb-0 flex flex-row rounded-sm text-[1rem] !ps-0">
                                                  <li class="page-item disabled">
                                                      <a class="page-link !py-[0.375rem] !px-[0.75rem]" href="javascript:void(0);">
                                                          Previous
                                                      </a>
                                                  </li>
                                                  <li class="page-item active"><a class="page-link !py-[0.375rem] !px-[0.75rem]"  href="javascript:void(0);">1</a></li>
                                                  <li class="page-item"><a class="page-link !py-[0.375rem] !px-[0.75rem]" href="javascript:void(0);">2</a></li>
                                                  <li class="page-item sm:block hidden "><a class="page-link !py-[0.375rem] !px-[0.75rem]" href="javascript:void(0);">3</a></li>
                                                  <li class="page-item">
                                                      <a class="page-link !text-primary !py-[0.375rem] !px-[0.75rem]" href="javascript:void(0);">
                                                          next
                                                      </a>
                                                  </li>
                                              </ul>
                                          </nav>
                                          <button type="button" class="ti-btn btn-wave bg-danger text-white !font-medium m-1">Delete All</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <!--End::row-1 -->
                      
@endsection

@section('scripts')

        <!-- Internal Product-Details JS -->
        @vite('resources/assets/js/product-list.js')

        
@endsection