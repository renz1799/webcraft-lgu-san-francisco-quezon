@extends('layouts.master')

@section('styles')
    <meta data-print-workspace-styles-start="1">
    <x-print.workspace-styles />
    <x-print.workspace-panel-styles />

    @include($paperProfile['styles_view'], [
        'paperProfile' => $paperProfile,
    ])
    <meta data-print-workspace-styles-end="1">
@endsection

@section('content')
    @php
        $appShellOffset = '420px';
        $sidebarWidth = 'clamp(360px, calc(' . ($paperProfile['height'] ?? '210mm') . ' * 0.44), 430px)';
        $previewWidth = 'min('
            . ($paperProfile['preview_width'] ?? ($paperProfile['width'] ?? '297mm'))
            . ', calc(100vw - '
            . $sidebarWidth
            . ' - '
            . $appShellOffset
            . '))';
    @endphp

    <div class="print-workspace-body print-workspace-body--embedded">
        <x-print.workspace
            sidebar-width="{{ $sidebarWidth }}"
            preview-width="{{ $previewWidth }}"
        >
            <x-slot:sidebar>
                @include('gso::reports.property-cards.print.partials.controls', [
                    'report' => $report,
                    'paperProfile' => $paperProfile,
                    'availableFunds' => $availableFunds,
                    'availableDepartments' => $availableDepartments,
                    'availableItems' => $availableItems,
                    'classificationOptions' => $classificationOptions,
                    'custodyOptions' => $custodyOptions,
                    'inventoryStatusOptions' => $inventoryStatusOptions,
                    'recordStatusOptions' => $recordStatusOptions,
                    'filters' => $filters,
                ])
            </x-slot:sidebar>

            @include($paperProfile['pages_view'], [
                'report' => $report,
                'paperProfile' => $paperProfile,
            ])
        </x-print.workspace>
    </div>
@endsection

@push('scripts')
    @vite('resources/modules/gso/js/reports/property-cards-print.js')
@endpush
