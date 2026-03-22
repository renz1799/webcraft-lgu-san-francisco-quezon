@extends('layouts.custom-master')

@section('styles')
@endsection

@section('error-body')
<body>
@endsection

@section('content')
<div class="container">
    <div class="flex justify-center authentication authentication-basic items-center h-full text-defaultsize text-defaulttextcolor">
        <div class="grid grid-cols-12 w-full">
            <div class="xxl:col-span-2 xl:col-span-2 lg:col-span-1 md:col-span-1 sm:col-span-0"></div>
            <div class="xxl:col-span-8 xl:col-span-8 lg:col-span-10 md:col-span-10 sm:col-span-12 col-span-12">
                <div class="my-[2.5rem] flex justify-center">
                    <a href="{{ route('modules.index') }}">
                        <img src="{{ asset('build/assets/images/brand-logos/desktop-logo.png') }}" alt="logo" class="desktop-logo">
                        <img src="{{ asset('build/assets/images/brand-logos/desktop-dark.png') }}" alt="logo" class="desktop-dark">
                    </a>
                </div>

                <div class="box">
                    <div class="box-body !p-[2rem] md:!p-[3rem]">
                        <p class="h5 font-semibold mb-2 text-center">Choose a Context</p>
                        <p class="mb-6 text-[#8c9097] dark:text-white/50 opacity-[0.8] font-normal text-center">
                            Select the platform context you want to work in. Core Platform handles global administration, while business modules stay scoped to their own workflows.
                        </p>

                        <div class="grid grid-cols-12 gap-4">
                            @forelse($modules as $module)
                                <div class="xl:col-span-6 col-span-12">
                                    <a
                                        href="{{ route('modules.open', ['moduleCode' => strtolower((string) $module->code)]) }}"
                                        class="block rounded-md border border-defaultborder dark:border-defaultborder/10 p-5 hover:border-primary hover:bg-primary/5 transition"
                                    >
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-[1rem] font-semibold mb-1">{{ $module->name }}</p>
                                                <p class="text-[0.8125rem] text-[#8c9097] dark:text-white/50 mb-0">
                                                    {{ $module->description ?: 'Platform module access is enabled for this account.' }}
                                                </p>
                                            </div>
                                            <div class="flex flex-col items-end gap-2">
                                                <span class="badge bg-primary/10 text-primary">{{ $module->code }}</span>
                                                <span class="badge bg-secondary/10 text-secondary">{{ $module->typeLabel() }}</span>
                                            </div>
                                        </div>
                                        @if($module->defaultDepartment)
                                            <p class="mt-4 mb-0 text-[0.75rem] text-[#8c9097] dark:text-white/50">
                                                Default department: {{ $module->defaultDepartment->name }}
                                            </p>
                                        @endif
                                    </a>
                                </div>
                            @empty
                                <div class="col-span-12">
                                    <div class="alert alert-danger !mb-0">
                                        No active module access is configured for this account yet.
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="xxl:col-span-2 xl:col-span-2 lg:col-span-1 md:col-span-1 sm:col-span-0"></div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@endsection
