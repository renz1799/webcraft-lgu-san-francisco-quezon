@extends('layouts.master')

@section('styles')

        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">

        <!-- Tom Select Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/tom-select/css/tom-select.default.min.css')}}">

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
                        <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Create NFT</h3>
                    </div>
                    <ol class="flex items-center whitespace-nowrap min-w-0">
                        <li class="text-[0.813rem] ps-[0.5rem]">
                          <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                            NFT
                            <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                          </a>
                        </li>
                        <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                            Create NFT
                        </li>
                    </ol>
                </div>
                <!-- Page Header Close -->

                <!-- Start::row-1 -->
                <div class="grid grid-cols-12 gap-6">
                    <div class="xxl:col-span-9 xl:col-span-8 col-span-12">
                        <div class="box custom-box">
                            <div class="box-header">
                                <div class="box-title">Create NFT</div>
                            </div>
                            <div class="box-body">
                                <div class="grid grid-cols-12 gap-4 justify-between">
                                    <div class="xxl:col-span-4 xl:col-span-12 col-span-12">
                                        <div class="create-nft-item">
                                            <label class="form-label">Upload NFT</label>
                                            <input type="file" class="single-fileupload" name="filepond" accept="image/png, image/jpeg, image/gif">
                                        </div>
                                    </div>
                                    <div class="xxl:col-span-8 xl:col-span-12 col-span-12">
                                        <div class="grid grid-cols-12 sm:gap-x-6 gap-y-6">
                                            <div class="xl:col-span-12 col-span-12">
                                                <label for="input-placeholder" class="form-label">NFT Title</label>
                                                <input type="text" class="form-control" id="input-placeholder" placeholder="eg:Neo-Nebulae">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label for="nft-description" class="form-label">NFT Description</label>
                                                <textarea class="form-control" id="nft-description" rows="3" placeholder="Enter Description"></textarea>
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label for="nft-link" class="form-label">External Link</label>
                                                <input type="text" class="form-control" id="nft-link" placeholder="External Link Here">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="xl:col-span-12 col-span-12">
                                        <div class="grid grdi-cols-12  sm:gap-x-4 gap-y-4">
                                            <div class="xl:col-span-6 col-span-12">
                                                <label for="nft-creator-name" class="form-label">Creator Name</label>
                                                <input type="text" class="form-control" id="nft-creator-name" placeholder="Enter Name">
                                            </div>
                                            <div class="xl:col-span-6 col-span-12">
                                                <label for="nft-price" class="form-label">NFT Price</label>
                                                <input type="text" class="form-control" id="nft-price" placeholder="Enter Price">
                                            </div>
                                            <div class="xl:col-span-4 col-span-12">
                                                <label for="nft-size" class="form-label">NFT Size</label>
                                                <input type="text" class="form-control" id="nft-size" placeholder="Enter Size">
                                            </div>
                                            <div class="xl:col-span-4 col-span-12">
                                                <label for="nft-royality" class="form-label">Royality</label>
                                                <select class="form-control" data-trigger name="nft-royality" id="nft-royality">
                                                    <option value="">Choose Royalities</option>
                                                    <option value="Choice 1">Flat Royalty</option>
                                                    <option value="Choice 2">Graduated Royalty</option>
                                                    <option value="Choice 3">Tiered Royalty</option>
                                                    <option value="Choice 3">Time-Limited Royalty</option>
                                                    <option value="Choice 3">Customized Royalty</option>
                                                </select>
                                            </div>
                                            <div class="xl:col-span-4 col-span-12">
                                                <label for="nft-property" class="form-label">Property</label>
                                                <input type="text" class="form-control" id="nft-property" placeholder="Enter Property">
                                            </div>
                                            <div class="xl:col-span-12 col-span-12">
                                                <label class="form-label block">Method</label>
                                                <div class="btn-group inline-flex" role="group" aria-label="Basic radio toggle button group">
                                                    <input type="radio" class="btn-check" name="strap-material" id="strap1" checked>
                                                    <label class="!mb-0 ti-btn btn-wave !block sm:!inline-flex !py-1 !px-2 ti-btn-primary !bg-primary !text-white z-0 !rounded-e-none !border-e-0" for="strap1"><i class="ti ti-tag me-1 align-middle text-[0.9375rem] inline-block"></i>Fixed Price</label>
                                                    <input type="radio" class="btn-check" name="strap-material" id="strap2">
                                                    <label class="!mb-0 ti-btn btn-wave !block sm:!inline-flex !py-1 !px-2 ti-btn-outline-primary text-default !rounded-none !border-e-0 z-0" for="strap2"><i class="ti ti-users text-[0.9375rem] me-1 align-middle inline-block"></i>Open For Bids</label>
                                                    <input type="radio" class="btn-check" name="strap-material" id="strap3">
                                                    <label class="!mb-0 ti-btn btn-wave !block sm:!inline-flex !py-1 !px-2 ti-btn-outline-primary !rounded-s-none text-default z-0" for="strap3"><i class="ti ti-hourglass-low text-[0.9375rem] me-1 align-middle inline-block"></i>Timed Auction</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box-footer text-end">
                                <a href="javascript:void(0);" class="ti-btn btn-wave ti-btn-primary-full">Create NFT</a>
                            </div>
                        </div>
                    </div>
                    <div class="xxl:col-span-3 xl:col-span-4 col-span-12">
                        <div class="box custom-box">
                            <div class="box-header">
                                <div class="box-title">
                                    NFT Preview Here
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="box custom-box !mb-0 !shadow-none border dark:border-defaultborder/10">
                                    <img src="{{asset('build/assets/images/nft-images/18.png')}}" class="box-img-top" alt="...">
                                    <div class="flex items-center justify-between nft-like-section w-full px-3">
                                        <div class="flex-fill">
                                            <button aria-label="button" type="button" class="ti-btn  btn-wave ti-btn-icon ti-btn-sm bg-success text-white hover:bg-success hover:text-white !rounded-full btn-wave">
                                                <i class="ri-heart-fill"></i>
                                            </button>
                                        </div>
                                        <div>
                                            <span class="badge nft-like-badge text-white"><i class="ri-heart-fill me-1 text-danger align-middle inline-block"></i>0.47k</span>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="flex items-center mb-3">
                                            <div class="me-2 lh-1">
                                                <span class="avatar avatar-rounded avatar-md">
                                                    <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="">
                                                </span>
                                            </div>
                                            <div>
                                                <p class="mb-0 font-semibold">NFTNinja</p>
                                                <p class="text-[0.75rem] text-muted mb-0">@nftninja</p>
                                            </div>
                                        </div>
                                        <p class="mb-0 text-white nft-auction-time">
                                            04hrs : 24m : 38s
                                        </p>
                                        <p class="text-[0.9375rem] font-semibold mb-2"><a href="javascript:void(0);">Digital Dreamscape</a></p>
                                        <div class="flex flex-wrap align-itesm-center justify-between">
                                            <div class="font-semibold">
                                                Highest Bid -
                                            </div>
                                            <div class="flex flex-wrap items-center">
                                                <span class="avatar avatar-xs me-1">
                                                    <img src="{{asset('build/assets/images/crypto-currencies/square-color/Ethereum.svg')}}" alt="">
                                                </span>0.24ETH
                                            </div>
                                        </div>
                                    </div>
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

        <!-- Create NFT JS -->
        @vite('resources/assets/js/nft-create.js')


@endsection