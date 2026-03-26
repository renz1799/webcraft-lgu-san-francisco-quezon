@extends('layouts.master')

@section('content')
<div class="md:flex block items-center justify-between my-[1.5rem] page-header-breadcrumb">
    <div>
        <p class="font-semibold text-[1.125rem] text-defaulttextcolor dark:text-defaulttextcolor/70 !mb-0">
            General Services Office
        </p>
        <p class="font-normal text-[#8c9097] dark:text-white/50 text-[0.813rem]">
            The GSO module now runs inside the shared LGU platform. Wave 1 sections are scaffolded and ready for gradual migration.
        </p>
    </div>
    <div class="btn-list md:mt-0 mt-2">
        <a href="{{ route('modules.index') }}" class="ti-btn ti-btn-outline-secondary btn-wave !font-medium !text-[0.85rem] !rounded-[0.35rem]">
            Switch Module
        </a>
    </div>
</div>

<div class="grid grid-cols-12 gap-6">
    <div class="xl:col-span-8 col-span-12">
        <div class="box">
            <div class="box-header">
                <div class="box-title">Migration Status</div>
            </div>
            <div class="box-body">
                <div class="grid grid-cols-12 gap-4">
                    <div class="md:col-span-6 col-span-12">
                        <div class="p-4 rounded-md bg-primary/5 border border-primary/10 h-full">
                            <p class="font-semibold mb-2">Runtime Foundation Complete</p>
                            <p class="text-[0.8125rem] text-[#8c9097] dark:text-white/50 mb-0">
                                GSO now has canonical `/gso/*` routing and participates in platform-level module selection.
                            </p>
                        </div>
                    </div>
                    <div class="md:col-span-6 col-span-12">
                        <div class="p-4 rounded-md bg-success/5 border border-success/10 h-full">
                            <p class="font-semibold mb-2">Wave 1 Complete, Wave 2 Started</p>
                            <p class="text-[0.8125rem] text-[#8c9097] dark:text-white/50 mb-0">
                                Reference data, inventory, inspection, stock, and the first AIR document register now have canonical platform-native entry points.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="xl:col-span-4 col-span-12">
        <div class="box">
            <div class="box-header">
                <div class="box-title">Next Areas</div>
            </div>
            <div class="box-body">
                <ul class="list-disc ps-5 text-[0.875rem] text-defaulttextcolor dark:text-defaulttextcolor/70">
                    <li class="mb-2">Reference data and inventory foundation</li>
                    <li class="mb-2">AIR items, inspection units, and inventory promotion flows</li>
                    <li class="mb-2">RIS, PAR, ICS, PTR, ITR, and WMR documents</li>
                    <li>Legacy code extraction into module-owned layers</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-span-12">
        <div class="box">
            <div class="box-header">
                <div class="box-title">Wave 1 Module Shell</div>
            </div>
            <div class="box-body">
                <div class="grid grid-cols-12 gap-4">
                    @php
                        $links = [
                            ['label' => 'AIR', 'route' => 'gso.air.index'],
                            ['label' => 'Items', 'route' => 'gso.items.index'],
                            ['label' => 'Inventory Items', 'route' => 'gso.inventory-items.index'],
                            ['label' => 'Inspections', 'route' => 'gso.inspections.index'],
                            ['label' => 'Stocks', 'route' => 'gso.stocks.index'],
                            ['label' => 'Asset Types', 'route' => 'gso.asset-types.index'],
                            ['label' => 'Asset Categories', 'route' => 'gso.asset-categories.index'],
                            ['label' => 'Departments', 'route' => 'gso.departments.index'],
                            ['label' => 'Fund Sources', 'route' => 'gso.fund-sources.index'],
                            ['label' => 'Fund Clusters', 'route' => 'gso.fund-clusters.index'],
                            ['label' => 'Accountable Persons', 'route' => 'gso.accountable-persons.index'],
                        ];
                    @endphp

                    @foreach($links as $link)
                        <div class="md:col-span-4 sm:col-span-6 col-span-12">
                            <a href="{{ route($link['route']) }}" class="ti-btn ti-btn-light !w-full !justify-start">
                                {{ $link['label'] }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
