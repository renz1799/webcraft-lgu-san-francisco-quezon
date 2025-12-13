@extends('layouts.master')

@section('styles')

        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">

        <!-- Tom Select Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/tom-select/css/tom-select.default.min.css')}}">
      
@endsection

@section('content')
 
                  <div class="container">

                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Mail Settings</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                Mail
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                               Mail Settings
                            </li>
                        </ol>
                    </div>
                    <!-- Page Header Close -->

                    <!-- Start::row-1 -->
                    <div class="grid grid-cols-12 gap-6 mb-[3rem]">
                        <div class="xl:col-span-12 col-span-12">
                            <div class="box">
                                <div class="box-header sm:flex block !justify-start">
                                    <nav aria-label="Tabs" class="md:flex block !justify-start whitespace-nowrap" role="tablist">
                                        <a class="m-1 block w-full hs-tab-active:bg-primary/10 hs-tab-active:text-primary cursor-pointer text-defaulttextcolor dark:text-defaulttextcolor/70 py-2 px-3 flex-grow  text-[0.75rem] font-medium rounded-md hover:text-primary active" id="Personal-item" data-hs-tab="#personal-info" aria-controls="personal-info">
                                            Personal Information
                                        </a>
                                        <a class="m-1 block w-full hs-tab-active:bg-primary/10 hs-tab-active:text-primary cursor-pointer text-defaulttextcolor dark:text-defaulttextcolor/70 py-2 px-3 text-[0.75rem] flex-grow font-medium rounded-md hover:text-primary " id="account-item" data-hs-tab="#account-settings" aria-controls="account-settings">
                                            Account Settings
                                        </a>
                                    </nav>
                                </div>
                                <div class="box-body">
                                    <div class="tab-content">
                                        <div class="tab-pane show active dark:border-defaultborder/10" id="personal-info" aria-labelledby="Personal-item" >
                                            <div class="sm:p-4 p-0">
                                                <h6 class="font-semibold mb-4 text-[1rem]">
                                                    Photo :
                                                </h6>
                                                <div class="mb-6 sm:flex items-center">
                                                    <div class="mb-0 me-[3rem]">
                                                        <span class="avatar avatar-xxl avatar-rounded">
                                                            <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="" id="profile-img">
                                                            <a aria-label="anchor" href="javascript:void(0);" class="badge rounded-full bg-primary avatar-badge">
                                                                <input type="file" name="photo" class="absolute w-full h-full opacity-0" id="profile-image">
                                                                <i class="fe fe-camera !text-[0.65rem] !text-white"></i>
                                                            </a>
                                                        </span>
                                                    </div>
                                                    <div class="inline-flex">
                                                        <button type="button" class="ti-btn btn-wave bg-primary text-white !rounded-e-none !font-medium ">Change</button>
                                                        <button type="button" class="ti-btn ti-btn-light !font-medium !rounded-s-none">Remove</button>
                                                    </div>
                                                </div>
                                                <h6 class="font-semibold mb-4 text-[1rem]">
                                                    Profile :
                                                </h6>
                                                <div class="sm:grid grid-cols-12 gap-6 mb-6">
                                                    <div class="xl:col-span-6 col-span-12">
                                                        <label for="first-name" class="form-label">First Name</label>
                                                        <input type="text" class="form-control w-full !rounded-md" id="first-name" placeholder="Firt Name">
                                                    </div>
                                                    <div class="xl:col-span-6 col-span-12">
                                                        <label for="last-name" class="form-label">Last Name</label>
                                                        <input type="text" class="form-control w-full !rounded-md" id="last-name" placeholder="Last Name">
                                                    </div>
                                                    <div class="xl:col-span-12 col-span-12">
                                                        <label class="form-label">User Name</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="basic-addon3">user2413@gmail.com</span>
                                                            <input type="text" class="form-control w-full rounded-md" id="basic-url" aria-describedby="basic-addon3">
                                                        </div>
                                                    </div>
                                                </div>
                                                <h6 class="font-semibold mb-4 text-[1rem]">
                                                    Personal information :
                                                </h6>
                                                <div class="sm:grid grid-cols-12 gap-6 mb-6">
                                                    <div class="xl:col-span-6 col-span-12">
                                                        <label for="email-address" class="form-label">Email Address :</label>
                                                        <input type="text" class="form-control w-full !rounded-md" id="email-address" placeholder="xyz@gmail.com">
                                                    </div>
                                                    <div class="xl:col-span-6 col-span-12">
                                                        <label for="Contact-Details" class="form-label">Contact Details :</label>
                                                        <input type="text" class="form-control w-full !rounded-md" id="Contact-Details" placeholder="contact details">
                                                    </div>
                                                    <div class="xl:col-span-6 col-span-12">
                                                        <label for="language" class="form-label">Language :</label>
                                                        <select class="form-control" name="language" id="language" multiple>
                                                        <option value="Choice 1" selected>English</option>
                                                        <option value="Choice 2">French</option>
                                                        <option value="Choice 3">Arabic</option>
                                                        <option value="Choice 4">Hindi</option>
                                                        </select>
                                                    </div>
                                                    <div class="xl:col-span-6 col-span-12">
                                                        <label class="form-label">Country :</label>
                                                        <select class="form-control w-full !rounded-md" data-trigger name="country-select" id="country-select">
                                                            <option value="Choice 1">Usa</option>
                                                            <option value="Choice 2">Australia</option>
                                                            <option value="Choice 3">Dubai</option>
                                                        </select>
                                                    </div>
                                                    <div class="xl:col-span-12 col-span-12">
                                                        <label for="bio" class="form-label">Bio :</label>
                                                        <textarea class="form-control w-full !rounded-md dark:!text-defaulttextcolor/70" id="bio" rows="5">Lorem ipsum dolor sit amet consectetur adipisicing elit. At sit impedit, officiis non minima saepe voluptates a magnam enim sequi porro veniam ea suscipit dolorum vel mollitia voluptate iste nemo!</textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane dark:border-defaultborder/10 hidden" id="account-settings" aria-labelledby="account-item" role="tabpanel">
                                            <div class="grid grid-cols-12 gap-4">
                                                <div class="xl:col-span-7 col-span-12">
                                                    <div class="box  shadow-none mb-0 border dark:border-defaultborder/10">
                                                        <div class="box-body">
                                                            <div class="flex items-center justify-between">
                                                                <div>
                                                                    <p class="text-[0.875rem] mb-1 font-semibold">Reset Password</p>
                                                                    <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50">Password should be min of <b class="text-success">8 digits<sup>*</sup></b>,atleast <b class="text-success">One Capital letter<sup>*</sup></b> and <b class="text-success">One Special Character<sup>*</sup></b> included.</p>
                                                                    <div class="mb-2">
                                                                        <label for="current-password" class="form-label">Current Password</label>
                                                                        <input type="text" class="form-control w-full !rounded-md" id="current-password" placeholder="Current Password">
                                                                    </div>
                                                                    <div class="mb-2">
                                                                        <label for="new-password" class="form-label">New Password</label>
                                                                        <input type="text" class="form-control w-full !rounded-md" id="new-password" placeholder="New Password">
                                                                    </div>
                                                                    <div class="mb-0">
                                                                        <label for="confirm-password" class="form-label">Confirm Password</label>
                                                                        <input type="text" class="form-control w-full !rounded-md" id="confirm-password" placeholder="Confirm Password">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="xl:col-span-1 col-span-12"></div>
                                                <div class="xl:col-span-4 col-span-12">
                                                    <div class="box shadow-none mb-0 border dark:border-defaultborder/10">
                                                        <div class="box-header justify-between items-center sm:flex block">
                                                            <div class="box-title">Registered Devices</div>
                                                            <div class="sm:mt-0">
                                                                <button type="button" class="ti-btn btn-wave !py-1 !px-2 bg-primary text-white !text-[0.75rem] !font-medium">Signout from all devices</button>
                                                            </div>
                                                        </div>
                                                        <div class="box-body">
                                                            <ul class="list-group">
                                                                <li class="list-group-item">
                                                                    <div class="sm:flex block items-center">
                                                                        <div class="lh-1 sm:mb-0 mb-2"><i class="bi bi-phone me-2 text-base align-middle text-[#8c9097] dark:text-white/50"></i></div>
                                                                        <div class="lh-1 flex-grow">
                                                                            <p class="mb-0">
                                                                                <span class="font-semibold">Mobile-LG-1023</span>
                                                                            </p>
                                                                            <p class="mb-0">
                                                                                <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Manchester, UK-Nov 30, 04:45PM</span>
                                                                            </p>
                                                                        </div>
                                                                        <div class="hs-dropdown ti-dropdown">
                                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                                              class="flex items-center justify-center w-[1.75rem] h-[1.75rem] !text-defaulttextcolor !text-[0.8rem] !py-1 !px-2 rounded-sm bg-light border-light shadow-none !font-medium"
                                                                              aria-expanded="false">
                                                                              <i class="fe fe-more-vertical text-[0.8rem]"></i>
                                                                            </a>
                                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Action</a></li>
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Another action</a></li>
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Something else here</a></li>
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                                <li class="list-group-item">
                                                                    <div class="sm:flex block items-center">
                                                                        <div class="lh-1 sm:mb-0 mb-2"><i class="bi bi-laptop me-2 text-base align-middle text-[#8c9097] dark:text-white/50"></i></div>
                                                                        <div class="lh-1 flex-grow">
                                                                            <p class="mb-0">
                                                                                <span class="font-semibold">Lenovo-1291203</span>
                                                                            </p>
                                                                            <p class="mb-0">
                                                                                <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">England, UK-Aug 12, 12:25PM</span>
                                                                            </p>
                                                                        </div>
                                                                        <div class="hs-dropdown ti-dropdown">
                                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                                              class="flex items-center justify-center w-[1.75rem] h-[1.75rem] !text-defaulttextcolor !text-[0.8rem] !py-1 !px-2 rounded-sm bg-light border-light shadow-none !font-medium"
                                                                              aria-expanded="false">
                                                                              <i class="fe fe-more-vertical text-[0.8rem]"></i>
                                                                            </a>
                                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Action</a></li>
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Another action</a></li>
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Something else here</a></li>
                                                                            </ul>
                                                                          </div>
                                                                    </div>
                                                                </li>
                                                                <li class="list-group-item">
                                                                    <div class="sm:flex block items-center">
                                                                        <div class="lh-1 sm:mb-0 mb-2"><i class="bi bi-laptop me-2 text-base align-middle text-[#8c9097] dark:text-white/50"></i></div>
                                                                        <div class="lh-1 flex-grow">
                                                                            <p class="mb-0">
                                                                                <span class="font-semibold">Macbook-Suzika</span>
                                                                            </p>
                                                                            <p class="mb-0">
                                                                                <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Brightoon, UK-Jul 18, 8:34AM</span>
                                                                            </p>
                                                                        </div>
                                                                        <div class="hs-dropdown ti-dropdown">
                                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                                              class="flex items-center justify-center w-[1.75rem] h-[1.75rem] !text-defaulttextcolor !text-[0.8rem] !py-1 !px-2 rounded-sm bg-light border-light shadow-none !font-medium"
                                                                              aria-expanded="false">
                                                                              <i class="fe fe-more-vertical text-[0.8rem]"></i>
                                                                            </a>
                                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Action</a></li>
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Another action</a></li>
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Something else here</a></li>
                                                                            </ul>
                                                                          </div>
                                                                    </div>
                                                                </li>
                                                                <li class="list-group-item">
                                                                    <div class="sm:flex block items-center">
                                                                        <div class="lh-1 sm:mb-0 mb-2"><i class="bi bi-pc-display-horizontal me-2 text-base align-middle text-[#8c9097] dark:text-white/50"></i></div>
                                                                        <div class="lh-1 flex-grow">
                                                                            <p class="mb-0">
                                                                                <span class="font-semibold">Apple-Desktop</span>
                                                                            </p>
                                                                            <p class="mb-0">
                                                                                <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">Darlington, UK-Jan 14, 11:14AM</span>
                                                                            </p>
                                                                        </div>
                                                                        <div class="hs-dropdown ti-dropdown">
                                                                            <a aria-label="anchor" href="javascript:void(0);"
                                                                              class="flex items-center justify-center w-[1.75rem] h-[1.75rem] !text-defaulttextcolor !text-[0.8rem] !py-1 !px-2 rounded-sm bg-light border-light shadow-none !font-medium"
                                                                              aria-expanded="false">
                                                                              <i class="fe fe-more-vertical text-[0.8rem]"></i>
                                                                            </a>
                                                                            <ul class="hs-dropdown-menu ti-dropdown-menu hidden">
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Action</a></li>
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Another action</a></li>
                                                                              <li><a class="ti-dropdown-item !py-2 !px-[0.9375rem] !text-[0.8125rem] !font-medium block"
                                                                                  href="javascript:void(0);">Something else here</a></li>
                                                                            </ul>
                                                                          </div>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>



                                    </div>
                                </div>
                                <div class="box-footer">
                                    <div class="ltr:float-right rtl:float-left">
                                        <button type="button" class="ti-btn btn-wave ti-btn-light m-1">
                                            Restore Defaults
                                        </button>
                                        <button type="button" class="ti-btn btn-wave bg-primary text-white m-1">
                                            Save Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End::row-1 -->

                  </div>
 

@push('scripts')

        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

        <!-- Mail Settings -->
        @vite('resources/assets/js/mail-settings.js')
@endpush
   
@endsection 