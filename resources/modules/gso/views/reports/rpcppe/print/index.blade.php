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
        $sidebarWidth = 'clamp(360px, calc(' . ($paperProfile['height'] ?? '210mm') . ' * 0.48), 430px)';
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
                @include('gso::reports.rpcppe.print.partials.controls', [
                    'report' => $report,
                    'paperProfile' => $paperProfile,
                    'availableFunds' => $availableFunds,
                    'availableDepartments' => $availableDepartments,
                    'availableAccountableOfficers' => $availableAccountableOfficers,
                    'filters' => $filters,
                ])
            </x-slot:sidebar>

            @include($paperProfile['pages_view'], [
                'report' => $report,
                'paperProfile' => $paperProfile,
                'headerImage' => !empty($paperProfile['header_image_web']) ? asset($paperProfile['header_image_web']) : null,
                'footerImage' => !empty($paperProfile['footer_image_web']) ? asset($paperProfile['footer_image_web']) : null,
            ])
        </x-print.workspace>
    </div>
@endsection

@push('scripts')
    @vite('resources/modules/gso/js/reports/rpcppe-print.js')
@endpush
