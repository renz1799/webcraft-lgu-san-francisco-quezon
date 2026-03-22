@extends('layouts.master')

@section('content')
<div class="md:flex block items-center justify-between my-[1.5rem] page-header-breadcrumb">
    <div>
        <p class="font-semibold text-[1.125rem] text-defaulttextcolor dark:text-defaulttextcolor/70 !mb-0">
            {{ $page['title'] }}
        </p>
        <p class="font-normal text-[#8c9097] dark:text-white/50 text-[0.813rem]">
            {{ $page['description'] }}
        </p>
    </div>
    <div class="btn-list md:mt-0 mt-2">
        <a href="{{ route('gso.dashboard') }}" class="ti-btn ti-btn-outline-secondary btn-wave !font-medium !text-[0.85rem] !rounded-[0.35rem]">
            Back to GSO
        </a>
    </div>
</div>

<div class="grid grid-cols-12 gap-6">
    <div class="xl:col-span-8 col-span-12">
        <div class="box">
            <div class="box-header">
                <div class="box-title">Module Shell Placeholder</div>
            </div>
            <div class="box-body">
                <p class="text-[0.875rem] text-defaulttextcolor dark:text-defaulttextcolor/70">
                    This route is now reserved inside the platform-owned GSO module. The legacy implementation will be
                    migrated into `app/Modules/GSO` and `resources/modules/gso` in the next wave without changing the
                    canonical module URL.
                </p>
            </div>
        </div>
    </div>

    <div class="xl:col-span-4 col-span-12">
        <div class="box">
            <div class="box-header">
                <div class="box-title">Canonical Route</div>
            </div>
            <div class="box-body">
                <code>{{ request()->path() }}</code>
            </div>
        </div>
    </div>
</div>
@endsection
