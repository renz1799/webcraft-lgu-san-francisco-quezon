@extends('layouts.master')

@section('styles')

        <!-- Sweetalert2 CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/sweetalert2/sweetalert2.min.css')}}">
      
@endsection

@section('content')

                      <!-- Page Header -->
                      <div class="block justify-between page-header md:flex">
                          <div>
                              <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Cart</h3>
                          </div>
                          <ol class="flex items-center whitespace-nowrap min-w-0">
                              <li class="text-[0.813rem] ps-[0.5rem]">
                                <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                  Ecommerce
                                  <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                </a>
                              </li>
                              <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                  Cart
                              </li>
                          </ol>
                      </div>
                      <!-- Page Header Close -->

                      <!-- Start::row-1 -->
                      <div class="grid grid-cols-12 sm:gap-x-6 gap-0">
                          <div class="xxl:col-span-9 col-span-12">
                              <div class="box" id="cart-container-delete">
                                  <div class="box-header">
                                      <div class="box-title">
                                          Cart Items
                                      </div>
                                  </div>
                                  <div class="box-body">
                                      <div class="table-responsive">
                                          <table class="table table-bordered whitespace-nowrap min-w-full">
                                              <thead>
                                                  <tr>
                                                      <th scope="row" class="text-start">
                                                          Product Name
                                                      </th>
                                                      <th scope="row" class="text-start">
                                                          Price
                                                      </th>
                                                      <th scope="row" class="text-start">
                                                          Quantity
                                                      </th>
                                                      <th scope="row" class="text-start">
                                                          Total
                                                      </th>
                                                      <th scope="row" class="text-start">
                                                          Action
                                                      </th>
                                                  </tr>
                                              </thead>
                                              <tbody>
                                                  <tr class="border border-inherit border-solid dark:border-defaultborder/10">
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-4">
                                                                  <span class="avatar avatar-xxl bg-light">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/1.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div>
                                                                  <div class="mb-1 text-[0.875rem] font-semibold">
                                                                      <a href="javascript:void(0);">Hiroma grey Hoodie (Unisex wear)</a>
                                                                  </div>
                                                                  <div class="mb-1 flex items-center align-middle">
                                                                      <span class="me-1">Size:</span><span class="font-semibold text-[#8c9097] dark:text-white/50">Large</span>
                                                                  </div>
                                                                  <div class="mb-1 flex items-center align-middle">
                                                                      <span class="me-1">Color:</span><span class="font-semibold text-[#8c9097] dark:text-white/50">Grey<span class="badge bg-success/10 text-success ms-4">In Offer</span></span>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="font-semibold text-[0.875rem]">
                                                              $459
                                                          </div>
                                                      </td>
                                                      <td class="product-quantity-container">
                                                          <div class="input-group border dark:border-defaultborder/10 rounded-md !flex-nowrap">
                                                              <button aria-label="button" type="button" class="btn btn-icon btn-light input-group-text flex-grow product-quantity-minus !border-0" ><i class="ri-subtract-line"></i></button>
                                                              <input type="text" class="form-control form-control-sm border-0 text-center !w-[50px] !px-0" aria-label="quantity" id="product-quantity" value="2">
                                                              <button aria-label="button" type="button" class="btn btn-icon btn-light input-group-text flex-grow product-quantity-plus !border-0" ><i class="ri-add-line"></i></button>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="text-[0.875rem] font-semibold">
                                                              $918
                                                          </div>
                                                      </td>
                                                      <td>
                                                        <div class="flex items-center">
                                                            <div class="hs-tooltip ti-main-tooltip">
                                                                <a href="{{url('wishlist')}}" type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-icon bg-success text-white !font-medium me-1">
                                                                    <i class="ri-heart-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                        Add To Wishlist
                                                                    </span>
                                                                </a>
                                                            </div>
                                                            <div class="hs-tooltip ti-main-tooltip ltr:[--placement:left] rtl:[--placement:right]">
                                                                <a href="javascript:void(0);" type="button" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-icon bg-danger text-white !font-medium btn-delete">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                    <span
                                                                        class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                        role="tooltip">
                                                                        Remove From cart
                                                                    </span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="border border-inherit border-solid dark:border-defaultborder/10">
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-3">
                                                                  <span class="avatar avatar-xxl bg-light">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/7.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div>
                                                                  <div class="mb-1 text-[0.875rem] font-semibold">
                                                                      <a href="javascript:void(0);">Blue Demin Jacket for Women</a>
                                                                  </div>
                                                                  <div class="mb-1 flex items-center align-middle">
                                                                      <span class="me-1">Size:</span><span class="font-semibold text-[#8c9097] dark:text-white/50">Medium</span>
                                                                  </div>
                                                                  <div class="mb-1 flex items-center align-middle">
                                                                      <span class="me-1">Color:</span><span class="font-semibold text-[#8c9097] dark:text-white/50">Blue<span class="badge bg-secondary text-white ms-4">25% discount</span></span>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="font-semibold text-[0.875rem]">
                                                              $129
                                                          </div>
                                                      </td>
                                                      <td class="product-quantity-container">
                                                          <div class="input-group border dark:border-defaultborder/10 rounded-md !flex-nowrap">
                                                              <button aria-label="button" type="button" class="btn btn-icon btn-light input-group-text flex-grow product-quantity-minus !border-0" ><i class="ri-subtract-line"></i></button>
                                                              <input type="text" class="form-control form-control-sm border-0 text-center !w-[50px] !px-0" aria-label="quantity" id="product-quantity1" value="1">
                                                              <button aria-label="button" type="button" class="btn btn-icon btn-light input-group-text flex-grow product-quantity-plus !border-0" ><i class="ri-add-line"></i></button>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="text-[0.875rem] font-semibold">
                                                              $129
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="hs-tooltip ti-main-tooltip">
                                                                  <a href="{{url('wishlist')}}" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-icon bg-success text-white !font-medium me-1">
                                                                      <i class="ri-heart-line"></i>
                                                                      <span
                                                                          class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                          role="tooltip">
                                                                          Add To Wishlist
                                                                      </span>
                                                                  </a>
                                                              </div>
                                                              <div class="hs-tooltip ti-main-tooltip ltr:[--placement:left] rtl:[--placement:right]">
                                                                  <a href="javascript:void(0);" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-icon bg-danger text-white !font-medium btn-delete">
                                                                      <i class="ri-delete-bin-line"></i>
                                                                      <span
                                                                          class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                          role="tooltip">
                                                                          Remove From cart
                                                                      </span>
                                                                  </a>
                                                              </div>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="border border-inherit border-solid dark:border-defaultborder/10">
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-3">
                                                                  <span class="avatar avatar-xxl bg-light">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/15.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div>
                                                                  <div class="mb-1 text-[0.875rem] font-semibold">
                                                                      <a href="javascript:void(0);">Orange smart watch(44mm dial)</a>
                                                                  </div>
                                                                  <div class="mb-1 flex items-center align-middle">
                                                                      <span class="me-1">Size:</span><span class="font-semibold text-[#8c9097] dark:text-white/50">44mm dial</span>
                                                                  </div>
                                                                  <div class="mb-1 flex items-center align-middle">
                                                                      <span class="me-1">Color:</span><span class="font-semibold text-[#8c9097] dark:text-white/50">Bronze<span class="badge bg-success/10 text-success ms-4">On Offer</span></span>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="font-semibold text-[0.875rem]">
                                                              $249
                                                          </div>
                                                      </td>
                                                      <td class="product-quantity-container">
                                                          <div class="input-group border dark:border-defaultborder/10 rounded-md !flex-nowrap">
                                                              <button aria-label="button" type="button" class="btn btn-icon btn-light input-group-text flex-grow product-quantity-minus !border-0"><i class="ri-subtract-line"></i></button>
                                                              <input type="text" class="form-control form-control-sm border-0 text-center !w-[50px] !px-0" aria-label="quantity" id="product-quantity2" value="2">
                                                              <button aria-label="button" type="button" class="btn btn-icon btn-light input-group-text flex-grow product-quantity-plus !border-0"><i class="ri-add-line"></i></button>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="text-[0.875rem] font-semibold">
                                                              $498
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="hs-tooltip ti-main-tooltip">
                                                                  <a href="{{url('wishlist')}}" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-icon bg-success text-white !font-medium me-1">
                                                                      <i class="ri-heart-line"></i>
                                                                      <span
                                                                          class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                          role="tooltip">
                                                                          Add To Wishlist
                                                                      </span>
                                                                  </a>
                                                              </div>
                                                              <div class="hs-tooltip ti-main-tooltip ltr:[--placement:left] rtl:[--placement:right]">
                                                                  <a href="javascript:void(0);" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-icon bg-danger text-white !font-medium btn-delete">
                                                                      <i class="ri-delete-bin-line"></i>
                                                                      <span
                                                                          class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                          role="tooltip">
                                                                          Remove From cart
                                                                      </span>
                                                                  </a>
                                                              </div>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="border border-inherit border-solid dark:border-defaultborder/10">
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-3">
                                                                  <span class="avatar avatar-xxl bg-light">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/12.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div>
                                                                  <div class="mb-1 text-[0.875rem] font-semibold">
                                                                      <a href="javascript:void(0);">Sweater for winter</a>
                                                                  </div>
                                                                  <div class="mb-1 flex items-center align-middle">
                                                                      <span class="me-1">Size:</span><span class="font-semibold text-[#8c9097] dark:text-white/50">Medium</span>
                                                                  </div>
                                                                  <div class="mb-1 flex items-center align-middle">
                                                                      <span class="me-1">Color:</span><span class="font-semibold text-[#8c9097] dark:text-white/50">Light Pink<span class="badge text-success bg-success/10 ms-4">On Offer</span></span>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="font-semibold text-[0.875rem]">
                                                              $249
                                                          </div>
                                                      </td>
                                                      <td class="product-quantity-container">
                                                          <div class="input-group border dark:border-defaultborder/10 rounded-md !flex-nowrap">
                                                              <button aria-label="button" type="button" class="btn btn-icon btn-light input-group-text flex-grow product-quantity-minus !border-0"><i class="ri-subtract-line"></i></button>
                                                              <input type="text" class="form-control form-control-sm border-0 text-center !w-[50px] !px-0" aria-label="quantity" id="product-quantity3" value="2">
                                                              <button aria-label="button" type="button" class="btn btn-icon btn-light input-group-text flex-grow product-quantity-plus !border-0"><i class="ri-add-line"></i></button>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="text-[0.875rem] font-semibold">
                                                              $498
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="hs-tooltip ti-main-tooltip">
                                                                  <a href="{{url('wishlist')}}" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-icon bg-success text-white !font-medium me-1">
                                                                      <i class="ri-heart-line"></i>
                                                                      <span
                                                                          class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                          role="tooltip">
                                                                          Add To Wishlist
                                                                      </span>
                                                                  </a>
                                                              </div>
                                                              <div class="hs-tooltip ti-main-tooltip ltr:[--placement:left] rtl:[--placement:right]">
                                                                  <a href="javascript:void(0);" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-icon bg-danger text-white !font-medium btn-delete">
                                                                      <i class="ri-delete-bin-line"></i>
                                                                      <span
                                                                          class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                          role="tooltip">
                                                                          Remove From cart
                                                                      </span>
                                                                  </a>
                                                              </div>
                                                          </div>
                                                      </td>
                                                  </tr>
                                                  <tr class="border border-inherit border-solid dark:border-defaultborder/10">
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="me-3">
                                                                  <span class="avatar avatar-xxl bg-light">
                                                                      <img src="{{asset('build/assets/images/ecommerce/png/3.png')}}" alt="">
                                                                  </span>
                                                              </div>
                                                              <div>
                                                                  <div class="mb-1 text-[0.875rem] font-semibold">
                                                                      <a href="javascript:void(0);">Snow coat from demin Corporation</a>
                                                                  </div>
                                                                  <div class="mb-1 flex items-center align-middle">
                                                                      <span class="me-1">Size:</span><span class="font-semibold text-[#8c9097] dark:text-white/50">Large</span>
                                                                  </div>
                                                                  <div class="mb-1 flex items-center align-middle">
                                                                      <span class="me-1">Color:</span><span class="font-semibold text-[#8c9097] dark:text-white/50">Green</span>
                                                                  </div>
                                                              </div>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="font-semibold text-[0.875rem]">
                                                              $1,299
                                                          </div>
                                                      </td>
                                                      <td class="product-quantity-container">
                                                          <div class="input-group border dark:border-defaultborder/10 rounded-md !flex-nowrap">
                                                              <button aria-label="button" type="button" class="btn btn-icon btn-light input-group-text flex-grow product-quantity-minus !border-0"><i class="ri-subtract-line"></i></button>
                                                              <input type="text" class="form-control form-control-sm border-0 text-center !w-[50px] !px-0" aria-label="quantity" id="product-quantity4" value="1">
                                                              <button aria-label="button" type="button" class="btn btn-icon btn-light input-group-text flex-grow product-quantity-plus !border-0"><i class="ri-add-line"></i></button>
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="text-[0.875rem] font-semibold">
                                                              $1,299
                                                          </div>
                                                      </td>
                                                      <td>
                                                          <div class="flex items-center">
                                                              <div class="hs-tooltip ti-main-tooltip">
                                                                  <a href="{{url('wishlist')}}" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-icon bg-success text-white !font-medium me-1">
                                                                      <i class="ri-heart-line"></i>
                                                                      <span
                                                                          class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                          role="tooltip">
                                                                          Add To Wishlist
                                                                      </span>
                                                                  </a>
                                                              </div>
                                                              <div class="hs-tooltip ti-main-tooltip ltr:[--placement:left] rtl:[--placement:right]">
                                                                  <a href="javascript:void(0);" class="hs-tooltip-toggle ti-btn btn-wave ti-btn-icon bg-danger text-white !font-medium btn-delete">
                                                                      <i class="ri-delete-bin-line"></i>
                                                                      <span
                                                                          class="hs-tooltip-content  ti-main-tooltip-content py-1 px-2 !bg-black !text-xs !font-medium !text-white shadow-sm "
                                                                          role="tooltip">
                                                                          Remove From cart
                                                                      </span>
                                                                  </a>
                                                              </div>
                                                          </div>
                                                      </td>
                                                  </tr>
                                              </tbody>
                                          </table>
                                      </div>
                                  </div>
                              </div>
                              <div class="box !hidden" id="cart-empty-cart">
                                  <div class="box-header">
                                      <div class="box-title">
                                          Empty Cart
                                      </div>
                                  </div>
                                  <div class="box-body flex items-center justify-center">
                                      <div class="cart-empty text-center">
                                          <svg xmlns="http://www.w3.org/2000/svg" class="svg-muted" width="24" height="24" viewbox="0 0 24 24"><path d="M18.6 16.5H8.9c-.9 0-1.6-.6-1.9-1.4L4.8 6.7c0-.1 0-.3.1-.4.1-.1.2-.1.4-.1h17.1c.1 0 .3.1.4.2.1.1.1.3.1.4L20.5 15c-.2.8-1 1.5-1.9 1.5zM5.9 7.1 8 14.8c.1.4.5.8 1 .8h9.7c.5 0 .9-.3 1-.8l2.1-7.7H5.9z"/><path d="M6 10.9 3.7 2.5H1.3v-.9H4c.2 0 .4.1.4.3l2.4 8.7-.8.3zM8.1 18.8 6 11l.9-.3L9 18.5z"/><path d="M20.8 20.4h-.9V20c0-.7-.6-1.3-1.3-1.3H8.9c-.7 0-1.3.6-1.3 1.3v.5h-.9V20c0-1.2 1-2.2 2.2-2.2h9.7c1.2 0 2.2 1 2.2 2.2v.4z"/><path d="M8.9 22.2c-1.2 0-2.2-1-2.2-2.2s1-2.2 2.2-2.2c1.2 0 2.2 1 2.2 2.2s-1 2.2-2.2 2.2zm0-3.5c-.7 0-1.3.6-1.3 1.3 0 .7.6 1.3 1.3 1.3.8 0 1.3-.6 1.3-1.3 0-.7-.5-1.3-1.3-1.3zM18.6 22.2c-1.2 0-2.2-1-2.2-2.2s1-2.2 2.2-2.2c1.2 0 2.2 1 2.2 2.2s-.9 2.2-2.2 2.2zm0-3.5c-.8 0-1.3.6-1.3 1.3 0 .7.6 1.3 1.3 1.3.7 0 1.3-.6 1.3-1.3 0-.7-.5-1.3-1.3-1.3z"/></svg>
                                          <h3 class="font-bold mb-1 text-[1.75rem]">Your Cart is Empty</h3>
                                          <h5 class="mb-4 text-[1.25rem]">Add some items to make me happy :)</h5>
                                          <a href="javascript:void(0);" class="ti-btn btn-wave bg-primary text-white !font-medium m-4" data-abc="true">continue shopping <i class="bi bi-arrow-right ms-1"></i></a>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-3 col-span-12">
                              <div class="box">
                                  <div class="p-4 border-b dark:border-defaultborder/10 block">
                                      <div class="alert alert-primary text-center" role="alert">
                                          <span class="text-defaulttextcolor">Sale Ends in</span> <span class="font-semibold text-[0.875rem] text-primary ms-1">18 Hours : 32 Minutes</span>
                                      </div>
                                  </div>
                                  <div class="box-body !p-0">
                                      <div class="p-4 border-b border-dashed dark:border-defaultborder/10">
                                          <p class="mb-2 font-semibold">Delivery:</p>
                                          <div class="inline-flex" role="group" aria-label="Basic radio toggle button group">
                                              <input type="radio" class="btn-check dark:border-defaultborder/10 " name="btnradio" id="btnradio1">
                                              <label class="ti-btn btn-wave ti-btn-outline-light !text-defaulttextcolor dark:hover:!bg-light dark:text-defaulttextcolor/70 !border-e-0 dark:!border-defaultborder/10 !rounded-e-none !font-medium" for="btnradio1">Free Delivery</label>
                                              <input type="radio" class="btn-check active active:bg-light" name="btnradio" id="btnradio2" checked>
                                              <label class="ti-btn btn-wave ti-btn-light dark:!border-defaultborder/10  dark:text-defaulttextcolor/70 dark:hover:!bg-light !font-medium !rounded-s-none" for="btnradio2">Express Delivery</label>
                                          </div>
                                          <p class="mb-0 mt-2 text-[0.75rem] text-[#8c9097] dark:text-white/50">Delivered by 24,Nov 2022</p>
                                      </div>
                                      <div class="p-4 border-b border-dashed dark:border-defaultborder/10">
                                          <div class="input-group">
                                              <input type="text" class="form-control form-control-sm !rounded-s-sm !border-s !border-e-0 dark:border-defaultborder/10" placeholder="Coupon Code" aria-label="coupon-code" aria-describedby="coupons">
                                              <button type="button" class="ti-btn btn-wave !bg-primary !text-white !font-medium !rounded-s-none !mb-0" id="coupons">Apply</button>
                                          </div>
                                          <a href="javascript:void(0);" class="text-[0.75rem] text-success">10% off on first purchase</a>
                                      </div>
                                      <div class="p-4 border-b border-dashed dark:border-defaultborder/10">
                                          <div class="flex items-center justify-between mb-4">
                                              <div class="text-[#8c9097] dark:text-white/50 opacity-[0.7]">Sub Total</div>
                                              <div class="font-semibold text-[0.875rem]">$1,299</div>
                                          </div>
                                          <div class="flex items-center justify-between mb-4">
                                              <div class="text-[#8c9097] dark:text-white/50 opacity-[0.7]">Discount</div>
                                              <div class="font-semibold text-[0.875rem] text-success">10% - $129</div>
                                          </div>
                                          <div class="flex items-center justify-between mb-4">
                                              <div class="text-[#8c9097] dark:text-white/50 opacity-[0.7]">Delivery Charges</div>
                                              <div class="font-semibold text-[0.875rem] text-danger">- $49</div>
                                          </div>
                                          <div class="flex items-center justify-between mb-4">
                                              <div class="text-[#8c9097] dark:text-white/50 opacity-[0.7]">Service Tax (18%)</div>
                                              <div class="font-semibold text-[0.875rem]">- $169</div>
                                          </div>
                                          <div class="flex items-center justify-between">
                                              <div class="text-[#8c9097] dark:text-white/50 opacity-[0.7]">Total :</div>
                                              <div class="font-semibold text-[0.875rem] text-primary"> $1,387</div>
                                          </div>
                                      </div>
                                      <div class="p-4 grid">
                                          <a href="{{url('checkout')}}" class="ti-btn btn-wave bg-primary  text-white !font-medium !mb-2">Proceed To Checkout</a>
                                          <a href="{{url('products')}}" class="ti-btn btn-wave bg-light  !font-medium">Continue Shopping</a>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <!--End::row-1 -->

@endsection

@section('scripts')

        <!-- Sweetalerts JS -->
        <script src="{{asset('build/assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>

        <!-- Internal Cart JS -->
        @vite('resources/assets/js/cart.js')


@endsection