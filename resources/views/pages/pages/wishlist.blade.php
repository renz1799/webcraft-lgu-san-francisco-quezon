@extends('layouts.master')

@section('styles')
 
        <!-- Sweetalert2 CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/sweetalert2/sweetalert2.min.css')}}">
      
@endsection

@section('content')

                      <!-- Page Header -->
                      <div class="block justify-between page-header md:flex">
                          <div>
                              <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Wishlist</h3>
                          </div>
                          <ol class="flex items-center whitespace-nowrap min-w-0">
                              <li class="text-[0.813rem] ps-[0.5rem]">
                                <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                  Ecommerce
                                  <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                </a>
                              </li>
                              <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                  Wishlist
                              </li>
                          </ol>
                      </div>
                      <!-- Page Header Close -->

                      <!-- Start::row-1 -->
                      <div class="grid grid-cols-12 gap-x-6">
                          <div class="xl:col-span-12 col-span-12">
                              <div class="box">
                                  <div class="box-body sm:flex items-center justify-between">
                                      <div class="text-[.9375rem] mb-0">Total <span class="badge bg-success text-white">12</span> products are wishlisted</div>
                                      <div class="flex" role="search">
                                          <input class="form-control form-control-sm me-2 !rounded-sm" type="search" placeholder="Search" aria-label="Search">
                                          <button class="ti-btn !py-1 !px-2 !text-[0.75rem] !font-medium ti-btn-light" type="submit">Search</button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0)" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/1.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Dapzem &amp; Co<span class="ltr:float-right rtl:float-left text-warning text-xs">4.2<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Branded hoodie ethnic style</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$229<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$1,799</span></span><span class="badge bg-secondary/10 text-secondary ltr:float-right rtl:float-left !text-[.625rem]">72% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $229
                                      </p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/2.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Denim Winjo<span class="ltr:float-right rtl:float-left text-warning text-xs">4.0<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Vintage pure leather Jacket</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$599<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$2,499</span></span><span class="badge bg-secondary/10 text-secondary ltr:float-right rtl:float-left !text-[.625rem]">75% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $599</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/3.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Jimmy Lolfiger<span class="ltr:float-right rtl:float-left text-warning text-xs">4.5<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Unisex jacket for men &amp; women</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$1,199<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$3,299</span></span><span class="badge bg-secondary/10 text-secondary ltr:float-right rtl:float-left !text-[.625rem]">62% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $1,199</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/4.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Bluberry Co.In<span class="ltr:float-right rtl:float-left text-warning text-xs">4.2<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Full sleeve white hoodie</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$349<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$1,299</span></span><span class="badge bg-secondary/10 text-secondary ltr:float-right rtl:float-left !text-[.625rem]">60% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $349</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/5.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Aus Polo Assn<span class="ltr:float-right rtl:float-left text-warning text-xs">4.5<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Snow jacket with low pockets</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$1,899<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$3,799</span></span><span class="badge bg-secondary/10 text-secondary ltr:float-right rtl:float-left !text-[.625rem]">50% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $1,899</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/6.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">BMW<span class="ltr:float-right rtl:float-left text-warning text-xs">4.1<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Ethnic wear jackets form BMW</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$1,499<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$2,499</span></span><span class="badge bg-secondary/10 text-secondary ltr:float-right rtl:float-left !text-[.625rem]">38% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $1,499</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/7.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Denim Corporation<span class="ltr:float-right rtl:float-left text-warning text-xs">4.4<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Flap pockets denim jackets for men</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$299<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$399</span></span><span class="badge bg-secondary/10 text-secondary ltr:float-right rtl:float-left !text-[.625rem]">35% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $299</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/8.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Pufa<span class="ltr:float-right rtl:float-left text-warning text-xs">3.8<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Ergonic designed full sleeve coat</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$2,399<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$5,699</span></span><span class="badge bg-primary/10 text-primary ltr:float-right rtl:float-left !text-[.625rem]">72% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $2,399</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/9.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Louie Phillippe<span class="ltr:float-right rtl:float-left text-warning text-xs">4.0<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Ergonic green colored full sleeve jacket</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$1,899<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$3,299</span></span><span class="badge bg-primary/10 text-primary ltr:float-right rtl:float-left !text-[.625rem]">60% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $1,899</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/10.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Denim Corp<span class="ltr:float-right rtl:float-left text-warning text-xs">4.1<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">beautiful brown colored snow jacket</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$2,499<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$4,999</span></span><span class="badge bg-primary/10 text-primary ltr:float-right rtl:float-left !text-[.625rem]">50% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $2,499</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/11.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Garage &amp; Co<span class="ltr:float-right rtl:float-left text-warning text-xs">4.3<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Full sleeve sweat shirt</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$249<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$1,299</span></span><span class="badge bg-primary/10 text-primary ltr:float-right rtl:float-left !text-[.625rem]">70% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $249</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 xl:col-span-6 lg:col-span-6 md:col-span-6 sm:col-span-12 col-span-12">
                              <div class="box product-card">
                                  <div class="box-body">
                                      <a href="javascript:void(0);" class="product-image">
                                          <img src="{{asset('build/assets/images/ecommerce/png/12.png')}}" class="card-img mb-3 rounded-sm" alt="...">
                                      </a>
                                      <div class="product-icons">
                                          <a aria-label="anchor" href="javascript:void(0);" class="wishlist btn-delete"><i class="ri-close-line"></i></a>
                                      </div>
                                      <p class="product-name font-semibold mb-0 flex items-center justify-between">Blueberry &amp; Co<span class="ltr:float-right rtl:float-left text-warning text-xs">4.0<i class="ri-star-s-fill align-middle ms-1"></i></span></p>
                                      <p class="product-description text-[.6875rem] text-[#8c9097] dark:text-white/50 mb-2">Light colored sweater form blueberry</p>
                                      <p class="mb-1 font-semibold text-[1rem] flex items-center justify-between"><span>$499<span class="text-[#8c9097] dark:text-white/50 line-through ms-1 opacity-[0.6] inline-block">$799</span></span><span class="badge bg-primary/10 text-primary ltr:float-right rtl:float-left !text-[.625rem]">32% off</span></p>
                                      <p class="text-[.6875rem] text-success font-semibold mb-0 flex items-center">
                                          <i class="ti ti-discount-2 text-[1rem] me-1"></i>Offer Price $499</p>
                                  </div>
                                  <div class="box-footer text-center ">
                                      <a href="{{url('cart')}}" class="ti-btn btn-wave ti-btn-primary m-1 !font-medium"><i class="ri-shopping-cart-2-line me-1 align-middle inline-block "></i>Move To Cart</a>
                                      <a href="{{url('products-details')}}" class="ti-btn btn-wave ti-btn-success m-1 !font-medium"><i class="ri-eye-line me-1 align-middle inline-block "></i>View Product</a>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <!--End::row-1 -->

                      <nav aria-label="Page navigation" class="">
                          <ul class="ti-pagination flex ltr:float-right rtl:float-left mb-4 rounded-sm text-[1rem]">
                              <li class="page-item disabled">
                                  <a class="page-link !py-[0.375rem] !px-[0.75rem]" href="javascript:void(0);">
                                      Previous
                                  </a>
                              </li>
                              <li class="page-item"><a class="page-link !py-[0.375rem] !px-[0.75rem]"  href="javascript:void(0);">1</a></li>
                              <li class="page-item "><a class="page-link !py-[0.375rem] !px-[0.75rem]" href="javascript:void(0);">2</a></li>
                              <li class="page-item "><a class="page-link !py-[0.375rem] !px-[0.75rem]" href="javascript:void(0);">3</a></li>
                              <li class="page-item">
                                  <a class="page-link !text-primary !py-[0.375rem] !px-[0.75rem]" href="javascript:void(0);">
                                      next
                                  </a>
                              </li>
                          </ul>
                      </nav>

@endsection

@section('scripts')

        <!-- Sweetalerts JS -->
        <script src="{{asset('build/assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>

        <!-- Wishlist JS -->
        @vite('resources/assets/js/wishlist.js')


@endsection