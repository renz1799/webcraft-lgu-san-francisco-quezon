@extends('layouts.master')

@section('styles')

        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">

        <!-- Tom Select Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/tom-select/css/tom-select.default.min.css')}}">

@endsection

@section('content')
@php($activeTab = request('tab', 'personal-info'))
@php($isAccountTab = $activeTab === 'account-settings')
@php($latestIdentityStatus = $latestIdentityChangeRequest?->status ?? 'none')
@php($identityStatusClasses = [
    'none' => 'bg-light text-defaulttextcolor',
    'pending' => 'bg-warning/10 text-warning',
    'approved' => 'bg-success/10 text-success',
    'rejected' => 'bg-danger/10 text-danger',
])

                  <div class="container">

                    <!-- Page Header -->
                    <div class="block justify-between page-header md:flex">
                        <div>
                            <h3 class="!text-defaulttextcolor dark:!text-defaulttextcolor/70 dark:text-white dark:hover:text-white text-[1.125rem] font-semibold">Profile</h3>
                        </div>
                        <ol class="flex items-center whitespace-nowrap min-w-0">
                            <li class="text-[0.813rem] ps-[0.5rem]">
                              <a class="flex items-center text-primary hover:text-primary dark:text-primary truncate" href="javascript:void(0);">
                                Profile
                                <i class="ti ti-chevrons-right flex-shrink-0 text-[#8c9097] dark:text-white/50 px-[0.5rem] overflow-visible rtl:rotate-180"></i>
                              </a>
                            </li>
                            <li class="text-[0.813rem] text-defaulttextcolor font-semibold hover:text-primary dark:text-[#8c9097] dark:text-white/50 " aria-current="page">
                               Profile
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
        <a href="{{ route('profile.index', ['tab' => 'personal-info']) }}" class="m-1 block w-full cursor-pointer py-2 px-3 flex-grow text-[0.75rem] font-medium rounded-md {{ $isAccountTab ? 'text-defaulttextcolor dark:text-defaulttextcolor/70 hover:text-primary' : 'bg-primary/10 text-primary' }}" id="Personal-item">
            Personal Information
        </a>
        <a href="{{ route('profile.index', ['tab' => 'account-settings']) }}" class="m-1 block w-full cursor-pointer py-2 px-3 text-[0.75rem] flex-grow font-medium rounded-md {{ $isAccountTab ? 'bg-primary/10 text-primary' : 'text-defaulttextcolor dark:text-defaulttextcolor/70 hover:text-primary' }}" id="account-item">
            Account Settings
        </a>
    </nav>
</div>
                                <div class="box-body">
                                <div class="tab-content">
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="tab-pane {{ $isAccountTab ? 'hidden' : 'show active' }} dark:border-defaultborder/10" id="personal-info">
            <div class="sm:p-4 p-0">
                <h6 class="font-semibold mb-4 text-[1rem]">Photo:</h6>
                <div class="mb-6 sm:flex items-center">
                <div class="mb-0 me-[3rem]">
                    <span class="avatar avatar-xxl avatar-rounded">
                        <img 
                            src="{{ $user->profile && $user->profile->profile_photo_path 
                                ? asset('storage/' . $user->profile->profile_photo_path) . '?v=' . time() 
                                : asset('build/assets/images/faces/9.jpg') }}" 
                            alt="Profile Photo" 
                            id="profile-img" 
                            class="object-cover"
                        />
                        <a aria-label="anchor" href="javascript:void(0);" class="badge rounded-full bg-primary avatar-badge">
                            <input 
                                type="file" 
                                name="profile_photo" 
                                class="absolute w-full h-full opacity-0" 
                                id="profile-image"
                                onchange="previewImage(event)"
                            >
                            <i class="fe fe-camera !text-[0.65rem] !text-white"></i>
                        </a>
                    </span>
                </div>
                <div class="inline-flex flex-col items-start gap-2">
                    <div class="inline-flex">
                        <label for="profile-image" class="ti-btn btn-wave bg-primary text-white !rounded-e-none !font-medium cursor-pointer">Change</label>
                        <button type="button" class="ti-btn ti-btn-light !font-medium !rounded-s-none" id="reset-profile-preview">Reset Preview</button>
                    </div>
                    <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">
                        Select a new image, then save the profile to apply it.
                    </p>
                </div>
                </div>
                <h6 class="font-semibold mb-4 text-[1rem]">Profile:</h6>
                <div class="mb-6 p-4 rounded-md border border-primary/20 bg-primary/5 dark:border-primary/20 dark:bg-primary/10">
                    <div class="flex items-start justify-between gap-4 flex-wrap">
                        <div>
                            <p class="font-semibold mb-1">Identity fields require approval</p>
                            <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">
                                Changes to first name, last name, middle name, and name extension will be submitted to an administrator for review before your official profile record is updated.
                            </p>
                        </div>
                        <span class="badge {{ $identityStatusClasses[$latestIdentityStatus] ?? 'bg-light text-defaulttextcolor' }}">
                            {{ $latestIdentityStatus === 'none' ? 'No Pending Request' : str($latestIdentityStatus)->replace('_', ' ')->title() }}
                        </span>
                    </div>
                </div>
                <div class="sm:grid grid-cols-12 gap-6 mb-6">
                    <div class="xl:col-span-6 col-span-12">
                        <label for="first-name" class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control w-full !rounded-md" id="first-name" 
                            placeholder="First Name" value="{{ old('first_name', $user->profile->first_name ?? '') }}">
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <label for="last-name" class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control w-full !rounded-md" id="last-name" 
                            placeholder="Last Name" value="{{ old('last_name', $user->profile->last_name ?? '') }}">
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <label for="middle-name" class="form-label">Middle Name</label>
                        <input type="text" name="middle_name" class="form-control w-full !rounded-md" id="middle-name" 
                            placeholder="Middle Name" value="{{ old('middle_name', $user->profile->middle_name ?? '') }}">
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <label for="name-extension" class="form-label">Name Extension</label>
                        <input type="text" name="name_extension" class="form-control w-full !rounded-md" id="name-extension" 
                            placeholder="Name Extension" value="{{ old('name_extension', $user->profile->name_extension ?? '') }}">
                    </div>
                    <div class="xl:col-span-12 col-span-12">
                        <label for="identity-change-reason" class="form-label">Reason for Identity Change Request</label>
                        <textarea
                            name="identity_change_reason"
                            class="form-control w-full !rounded-md"
                            id="identity-change-reason"
                            rows="3"
                            placeholder="Optional reason for the name update request">{{ old('identity_change_reason', $latestIdentityChangeRequest?->isPending() ? $latestIdentityChangeRequest->reason : '') }}</textarea>
                        <p class="mt-2 text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">
                            This note is sent to the administrators who can review your identity change request.
                        </p>
                    </div>
                    <div class="xl:col-span-12 col-span-12">
                        <div class="p-4 rounded-md border border-defaultborder dark:border-defaultborder/10 bg-light/30 dark:bg-bodybg/30">
                            <div class="flex items-start justify-between gap-3 flex-wrap mb-3">
                                <div>
                                    <p class="font-semibold mb-1">Latest Identity Request Status</p>
                                    <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mb-0">
                                        {{ $latestIdentityStatus === 'none'
                                            ? 'You do not have any submitted identity change requests.'
                                            : 'Review progress for your latest submitted identity change request.' }}
                                    </p>
                                </div>
                                <span class="badge {{ $identityStatusClasses[$latestIdentityStatus] ?? 'bg-light text-defaulttextcolor' }}">
                                    {{ $latestIdentityStatus === 'none' ? 'No Pending Request' : str($latestIdentityStatus)->replace('_', ' ')->title() }}
                                </span>
                            </div>

                            @if ($latestIdentityChangeRequest)
                                <div class="grid grid-cols-12 gap-4">
                                    <div class="xl:col-span-6 col-span-12">
                                        <p class="text-[0.75rem] uppercase tracking-wide text-[#8c9097] dark:text-white/50 mb-2">Official Name At Submission</p>
                                        <p class="mb-0">{{ $latestIdentityChangeRequest->currentFullName() ?: 'No official name recorded.' }}</p>
                                    </div>
                                    <div class="xl:col-span-6 col-span-12">
                                        <p class="text-[0.75rem] uppercase tracking-wide text-[#8c9097] dark:text-white/50 mb-2">Requested Name</p>
                                        <p class="mb-0">{{ $latestIdentityChangeRequest->requestedFullName() ?: 'No requested name recorded.' }}</p>
                                    </div>
                                    <div class="xl:col-span-6 col-span-12">
                                        <p class="text-[0.75rem] uppercase tracking-wide text-[#8c9097] dark:text-white/50 mb-2">Submitted</p>
                                        <p class="mb-0">{{ optional($latestIdentityChangeRequest->created_at)->format('M d, Y h:i A') ?: 'Not available' }}</p>
                                    </div>
                                    <div class="xl:col-span-6 col-span-12">
                                        <p class="text-[0.75rem] uppercase tracking-wide text-[#8c9097] dark:text-white/50 mb-2">Reviewed</p>
                                        <p class="mb-0">{{ optional($latestIdentityChangeRequest->reviewed_at)->format('M d, Y h:i A') ?: 'Pending review' }}</p>
                                    </div>
                                    @if ($latestIdentityChangeRequest->reason)
                                        <div class="xl:col-span-12 col-span-12">
                                            <p class="text-[0.75rem] uppercase tracking-wide text-[#8c9097] dark:text-white/50 mb-2">Submitted Reason</p>
                                            <p class="mb-0">{{ $latestIdentityChangeRequest->reason }}</p>
                                        </div>
                                    @endif
                                    @if ($latestIdentityChangeRequest->review_notes)
                                        <div class="xl:col-span-12 col-span-12">
                                            <p class="text-[0.75rem] uppercase tracking-wide text-[#8c9097] dark:text-white/50 mb-2">Review Notes</p>
                                            <p class="mb-0">{{ $latestIdentityChangeRequest->review_notes }}</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <p class="mb-0 text-[0.875rem] text-[#8c9097] dark:text-white/50">
                                    Your official identity fields currently match the profile record shown above.
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="xl:col-span-12 col-span-12">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" name="address" class="form-control w-full !rounded-md" id="address" 
                            placeholder="Address" value="{{ old('address', $user->profile->address ?? '') }}">
                    </div>
                    <div class="xl:col-span-12 col-span-12">
                        <label for="contact-details" class="form-label">Contact Details</label>
                        <input type="text" name="contact_details" class="form-control w-full !rounded-md" id="contact-details" 
                            placeholder="Contact Details" value="{{ old('contact_details', $user->profile->contact_details ?? '') }}">
                    </div>
                </div>

                <h6 class="font-semibold mb-4 text-[1rem]">Credentials Information:</h6>
                <div class="sm:grid grid-cols-12 gap-6 mb-6">
                    <div class="xl:col-span-6 col-span-12">
                        <label for="email-address" class="form-label">Email Address:</label>
                        <input 
                            type="text" 
                            name="email" 
                            class="form-control w-full !rounded-md" 
                            id="email-address" 
                            placeholder="Email Address" 
                            value="{{ old('email', $user->email) }}">
                    </div>
                    <div class="xl:col-span-6 col-span-12">
                        <label for="username" class="form-label">Username:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ri-at-line"></i></span>
                            <input 
                                type="text" 
                                name="username" 
                                class="form-control w-full !rounded-e-md !rounded-s-none" 
                                id="username" 
                                placeholder="Username" 
                                value="{{ old('username', $user->username) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Changes Button for Personal Info -->
            <div class="box-footer">
                <div class="ltr:float-right rtl:float-left">
                    <button type="submit" class="ti-btn btn-wave bg-primary text-white m-1">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </form>


    <div class="tab-pane {{ $isAccountTab ? 'show active' : 'hidden' }} dark:border-defaultborder/10" id="account-settings">
    <div class="grid grid-cols-12 gap-4">
    <!-- Reset Password Section -->
    <div class="xl:col-span-8 lg:col-span-8 col-span-12">
        <div class="box shadow-none mb-0 border dark:border-defaultborder/10">
            <div class="box-body">
                <div class="sm:p-4 p-0">
                    <form method="POST" action="{{ route('profile.updatePassword') }}" id="resetPasswordForm">
                        @csrf
                        @method('PUT')

                        <div class="flex flex-col space-y-4">
                            <div>
                                <p class="text-[0.875rem] mb-1 font-semibold">Reset Password</p>
                                <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50">
                                    Password should be min of <b class="text-success">8 digits<sup>*</sup></b>, at least
                                    <b class="text-success">One Capital letter<sup>*</sup></b> and <b class="text-success">One Special Character<sup>*</sup></b>.
                                </p>
                            </div>

                            <div>
                                <label for="current-password" class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control w-full !rounded-md" id="current-password" placeholder="Current Password" required>
                            </div>
                            <div>
                                <label for="new-password" class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control w-full !rounded-md" id="new-password" placeholder="New Password" required>
                            </div>
                            <div>
                                <label for="confirm-password" class="form-label">Confirm Password</label>
                                <input type="password" name="new_password_confirmation" class="form-control w-full !rounded-md" id="confirm-password" placeholder="Confirm Password" required>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="ti-btn btn-wave bg-primary text-white">Reset Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<!-- Last Login Section -->
<div class="xl:col-span-4 lg:col-span-4 col-span-12">
    <div class="box shadow-none mb-0 border dark:border-defaultborder/10">
        <div class="box-header justify-between items-center sm:flex block">
            <div>
                <div class="box-title">Last Login</div>
                <p class="text-[0.75rem] text-[#8c9097] dark:text-white/50 mt-1 mb-0">
                    Recent sign-ins and device activity tied to your account.
                </p>
            </div>
            <div class="sm:mt-0 mt-2">
                <span class="badge bg-primary/10 text-primary">{{ $loginDetails->count() }} recent</span>
            </div>
        </div>
        <div class="box-body">
            <ul class="list-group">
                @forelse ($loginDetails as $login)
                    <li class="list-group-item">
                        <div class="sm:flex block items-center">
                            <div class="lh-1 sm:mb-0 mb-2">
                                <i class="bi {{ $login->device_icon }} me-2 text-base align-middle text-[#8c9097] dark:text-white/50"></i>
                            </div>
                            <div class="lh-1 flex-grow">
                                <p class="mb-0">
                                    <span class="font-semibold">{{ $login->device_details }}</span>
                                </p>
                                <p class="mb-0">
                                    <span class="text-[#8c9097] dark:text-white/50 text-[0.6875rem]">
                                        {{ $login->address ?? 'Unknown Location' }} <br> {{ $login->created_at->format('M d, h:iA') }} · {{ $login->created_at->diffForHumans() }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item">
                        <div class="text-center text-[#8c9097] dark:text-white/50">
                            No login records found.
                        </div>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

</div>


                  </div>
      

@push('scripts')

        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

        <script>
            window.profileFeedback = {
                success: @json(session('success')),
                errors: @json($errors->all()),
                activeTab: @json(request('tab', 'personal-info')),
            };
        </script>

        <!-- Profile Settings -->
        @vite('resources/assets/js/profile-settings.js')
      

        <script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const img = document.getElementById('profile-img');
            img.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
    
</script>
   
@endpush

@endsection
