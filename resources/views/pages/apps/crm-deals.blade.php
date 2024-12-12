@extends('layouts.master')

@section('styles')
 
        <!-- Dragula CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/dragula/dragula.min.css')}}">

        <!-- Flatpickr CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/flatpickr/flatpickr.min.css')}}">
      
@endsection

@section('content')

                        <!-- Page Header open -->
                        <div class="block justify-between page-header md:flex">
                            <div>
                                <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold"> Deals</h3>
                            </div>
                            <ol class="flex items-center whitespace-nowrap min-w-0">
                                <li class="text-[0.813rem] ps-[0.5rem]">
                                    <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                    CRM
                                    <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                                    </a>
                                </li>
                                <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                                    Deals
                                </li>
                            </ol>
                        </div>
                        <!-- Page Header Close -->

                        <!-- Start::row-1 -->
                        <div class="grid grid-cols-12 gap-x-6">
                            <div class="xl:col-span-12 col-span-12">
                                <div class="box custom-box">
                                    <div class="box-body">
                                        <div class="flex items-center flex-wrap gap-2 justify-between">
                                            <div class="flex items-center">
                                                <span class="font-semibold text-[1rem] me-1">Deals</span><span class="badge bg-light text-default align-middle">16</span>
                                            </div>
                                            <div class="flex flex-wrap gap-2">
                                                <a href="javascript:void(0);" class="hs-dropdown-toggle ti-btn btn-wave ti-btn-primary-full !py-1 !px-2 !text-[0.75rem]" data-hs-overlay="#todo-compose"><i class="ri-add-line font-semibold align-middle"></i>New Deal
                                                </a>
                                                <button type="button" class="ti-btn btn-wave ti-btn-success !py-1 !px-2 !text-[0.75rem]">Export As CSV</button>
                                                <div class="hs-dropdown ti-dropdown">
                                                    <a href="javascript:void(0);" class="ti-btn btn-wave ti-btn-light !py-1 !px-2 !text-[0.75rem]" aria-expanded="false">
                                                        Sort By<i class="ri-arrow-down-s-line align-middle ms-1 inline-block"></i>
                                                    </a>
                                                    <ul class="hs-dropdown-menu ti-dropdown-menu hidden" role="menu">
                                                        <li><a class="ti-dropdown-item" href="javascript:void(0);">Newest</a></li>
                                                        <li><a class="ti-dropdown-item" href="javascript:void(0);">Date Added</a></li>
                                                        <li><a class="ti-dropdown-item" href="javascript:void(0);">A - Z</a></li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End::row-1 -->

                      <!-- Start::row-2 -->
                      <div class="grid grid-cols-12 gap-x-6">
                          <div class="xxxl:col-span-2 md:col-span-4 col-span-12">
                              <div class="box custom-box">
                                  <div class="box-body !p-4">
                                      <div class="flex items-start flex-wrap justify-between">
                                          <div>
                                              <div class="font-semibold text-[.9375rem] lead-discovered">Leads Discovered</div>
                                              <span class=" badge bg-light text-default">24 Leads</span>
                                          </div>
                                          <div>
                                              <span class="text-primary font-semibold">$25,238</span>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-2 md:col-span-4 col-span-12">
                              <div class="box custom-box">
                                  <div class="box-body !p-4">
                                      <div class="flex items-start flex-wrap justify-between">
                                          <div>
                                              <div class="font-semibold text-[.9375rem] lead-qualified">Qualified Leads</div>
                                              <span class=" badge bg-light text-default">17 Leads</span>
                                          </div>
                                          <div>
                                              <span class="text-warning font-semibold">$32,453</span>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-2 md:col-span-4 col-span-12">
                              <div class="box custom-box">
                                  <div class="box-body !p-4">
                                      <div class="flex items-start flex-wrap justify-between">
                                          <div>
                                              <div class="font-semibold text-[.9375rem] contact-initiated">Contact Initiated</div>
                                              <span class=" badge bg-light text-default">5 Leads</span>
                                          </div>
                                          <div>
                                              <span class="text-success font-semibold">$13,756</span>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-2 md:col-span-4 col-span-12">
                              <div class="box custom-box">
                                  <div class="box-body !p-4">
                                      <div class="flex items-start flex-wrap justify-between">
                                          <div>
                                              <div class="font-semibold text-[.9375rem] need-identified">Needs Identified</div>
                                              <span class=" badge bg-light text-default">43 Leads</span>
                                          </div>
                                          <div>
                                              <span class="text-info font-semibold">$47,093</span>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-2 md:col-span-4 col-span-12">
                              <div class="box custom-box">
                                  <div class="box-body !p-4">
                                      <div class="flex items-start flex-wrap justify-between">
                                          <div>
                                              <div class="font-semibold text-[.9375rem] negotiation">Negotiation</div>
                                              <span class=" badge bg-light text-default">15 Leads</span>
                                          </div>
                                          <div>
                                              <span class="text-danger font-semibold">$26,146</span>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-2 md:col-span-4 col-span-12">
                              <div class="box custom-box">
                                  <div class="box-body !p-4">
                                      <div class="flex items-start flex-wrap justify-between">
                                          <div>
                                              <div class="font-semibold text-[.9375rem] deal-finalized">Deal Finalized</div>
                                              <span class=" badge bg-light text-default">127 Deals</span>
                                          </div>
                                          <div>
                                              <span class="text-secondary font-semibold">$1,74,679</span>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <!-- End::row-2 -->

                      <!-- Start::row-3 -->
                      <div class="grid grid-cols-12 gap-x-6">
                          <div class="xxl:col-span-2 col-span-12" id="leads-discovered">
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/12.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Service Upgrade</div>
                                          </div>
                                          <div>$5000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Spruko Technologies</a>
                                          </div>
                                          <div class="text-muted text-xs">24,Jun 2023 - 12:45PM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/5.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Product Demo</div>
                                          </div>
                                          <div>$50,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Acme Corporation LTD</a>
                                          </div>
                                          <div class="text-muted text-xs">18,Apr 2023 - 11:22AM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/15.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Website Redesign</div>
                                          </div>
                                          <div>$20,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Embark Technologies</a>
                                          </div>
                                          <div class="text-muted text-xs">12,Jul 2023 - 10:15AM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/6.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Consulting Services</div>
                                          </div>
                                          <div>$10,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Adam Johnson</a>
                                          </div>
                                          <div class="text-muted text-xs">29,Jul 2023 - 4:45PM</div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-2 col-span-12" id="leads-qualified">
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Event Sponsorship</div>
                                          </div>
                                          <div>$10,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Initech Info</a>
                                          </div>
                                          <div class="text-muted text-xs">21,May 2023 - 10:25AM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/11.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Sales Training</div>
                                          </div>
                                          <div>$6,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Soylent Corp</a>
                                          </div>
                                          <div class="text-muted text-xs">10,May 2023 - 9:20AM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/14.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Content Creation</div>
                                          </div>
                                          <div>$3,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Hooli Technologies</a>
                                          </div>
                                          <div class="text-muted text-xs">25,Aug 2023 - 3:38PM</div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-2 col-span-12" id="contact-initiated">
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/3.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">E-commerce Integration</div>
                                          </div>
                                          <div>$12,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Spice Infotech</a>
                                          </div>
                                          <div class="text-muted text-xs">15,Sep 2023 - 8:32PM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/16.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Ad Campaign</div>
                                          </div>
                                          <div>$5,500</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Umbrella Corp</a>
                                          </div>
                                          <div class="text-muted text-xs">17,Jun 2023 - 10:54AM</div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-2 col-span-12" id="needs-identified">
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Webinar Series</div>
                                          </div>
                                          <div>$9,500</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Massive Dynamic</a>
                                          </div>
                                          <div class="text-muted text-xs">16,May 2023 - 11:22AM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/13.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">SEO Audit</div>
                                          </div>
                                          <div>$3,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Logitech ecostics</a>
                                          </div>
                                          <div class="text-muted text-xs">27,Apr 2023 - 5:15PM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/8.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Loyalty Program</div>
                                          </div>
                                          <div>$12,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">Globex Corp</a>
                                          </div>
                                          <div class="text-muted text-xs">26,Jul 2023 - 5:28AM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">CRM Integration</div>
                                          </div>
                                          <div>$10,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">CrystalClear Consulting</a>
                                          </div>
                                          <div class="text-muted text-xs">14,May 2023 - 11:29PM</div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-2 col-span-12" id="negotiation">
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/16.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Media Analytics</div>
                                          </div>
                                          <div>$9,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">GlobalConnect</a>
                                          </div>
                                          <div class="text-muted text-xs">18,Mar 2023 - 2:32PM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded bg-light">
                                                      <img src="{{asset('build/assets/images/faces/21.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Lead Nurturing Strategy</div>
                                          </div>
                                          <div>$4,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">AlphaTech Solutions</a>
                                          </div>
                                          <div class="text-muted text-xs">16,Jul 2023 - 7:53AM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded text-white">
                                                      PL
                                                  </span>
                                              </div>
                                              <div class="text-sm">Website Maintenance</div>
                                          </div>
                                          <div>$7,500</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">RedRock Industries</a>
                                          </div>
                                          <div class="text-muted text-xs">30,Jul 2023 - 6:30AM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/2.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Newsletter Campaign</div>
                                          </div>
                                          <div>$2,500</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">CoreTech Solutions</a>
                                          </div>
                                          <div class="text-muted text-xs">12,May 2023 - 10:22AM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/17.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Graphic Design</div>
                                          </div>
                                          <div>$5,000</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">TechPro Services</a>
                                          </div>
                                          <div class="text-muted text-xs">10,Jul 2023 - 10:15PM</div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <div class="xxl:col-span-2 col-span-12" id="deal-finalized">
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/1.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">CRM Training</div>
                                          </div>
                                          <div>$4,200</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">BlueSky Industries</a>
                                          </div>
                                          <div class="text-muted text-xs">15,May 2023 - 8:20AM</div>
                                      </div>
                                  </div>
                              </div>
                              <div class="box custom-box">
                                  <div class="box-body">
                                      <div class="flex items-center font-semibold justify-between gap-1 flex-wrap">
                                          <div class="flex items-center gap-2">
                                              <div class="lh-1">
                                                  <span class="avatar avatar-sm avatar-rounded">
                                                      <img src="{{asset('build/assets/images/faces/10.jpg')}}" alt="">
                                                  </span>
                                              </div>
                                              <div class="text-sm">Market Research</div>
                                          </div>
                                          <div>$10,500</div>
                                      </div>
                                      <div class="deal-description">
                                          <div class="my-1">
                                              <a href="javascript:void(0);" class="company-name">BrightStar Solutions</a>
                                          </div>
                                          <div class="text-muted text-xs">28,Jun 2023 - 9:27PM</div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                      <!-- End::row-3 -->

                      <!-- Start:: New Deal -->
                      <div id="todo-compose" class="hs-overlay hidden ti-modal">
                          <div class="hs-overlay-open:mt-7 ti-modal-box mt-0 ease-out">
                            <div class="ti-modal-content">
                              <div class="ti-modal-header">
                                  <h6 class="modal-title text-[1rem] font-semibold text-defaulttextcolor" id="mail-ComposeLabel">New Deal</h6>
                                    <button type="button" class="hs-dropdown-toggle !text-[1rem] !font-semibold !text-defaulttextcolor" data-hs-overlay="#todo-compose">
                                      <span class="sr-only">Close</span>
                                      <i class="ri-close-line"></i>
                                    </button>
                              </div>
                              <div class="ti-modal-body px-4">
                                  <div class="grid grid-cols-12 gap-4">
                                      <div class="xl:col-span-12 col-span-12">
                                          <div class="mb-0 text-center">
                                              <span class="avatar avatar-xxl avatar-rounded">
                                                  <img src="{{asset('build/assets/images/faces/9.jpg')}}" alt="" id="profile-img">
                                                  <span class="badge rounded-full bg-primary avatar-badge">
                                                      <input type="file" name="photo" class="absolute w-full h-full opacity-[0]" id="profile-change">
                                                      <i class="fe fe-camera text-[.625rem] !text-white"></i>
                                                  </span>
                                              </span>
                                          </div>
                                      </div>
                                      <div class="xl:col-span-6 col-span-12">
                                          <label for="deal-name" class="form-label">Contact Name</label>
                                          <input type="text" class="form-control" id="deal-name" placeholder="Contact Name">
                                      </div>
                                      <div class="xl:col-span-6 col-span-12">
                                          <label for="deal-lead-score" class="form-label">Deal Value</label>
                                          <input type="number" class="form-control" id="deal-lead-score" placeholder="Deal Value">
                                      </div>
                                      <div class="xl:col-span-12 col-span-12">
                                          <label for="company-name" class="form-label">Company Name</label>
                                          <input type="text" class="form-control" id="company-name" placeholder="Company Name">
                                      </div>
                                      <div class="xl:col-span-12 col-span-12">
                                          <label class="form-label">Last Contacted</label>
                                          <div class="form-group">
                                              <div class="input-group">
                                                  <div class="input-group-text text-muted"> <i class="ri-calendar-line"></i> </div>
                                                  <input type="text" class="form-control" id="targetDate" placeholder="Choose date and time">
                                              </div>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="ti-modal-footer">
                                  <button type="button"
                                  class="hs-dropdown-toggle ti-btn btn-wave ti-btn-light align-middle"
                                  data-hs-overlay="#todo-compose">
                                  Cancel
                                </button>
                                  <button type="button" class="ti-btn btn-wave bg-primary text-white !font-medium">Create Deal</button>
                              </div>
                            </div>
                          </div>
                      </div>
                      <!-- End:: New Deal -->

@endsection

@section('scripts')

        <!-- Dragula JS -->
        <script src="{{asset('build/assets/libs/dragula/dragula.min.js')}}"></script>

        <!-- Flat Picker JS -->
        <script src="{{asset('build/assets/libs/flatpickr/flatpickr.min.js')}}"></script>

        <!-- CRM Deals JS -->
        @vite('resources/assets/js/crm-deals.js')
        

@endsection