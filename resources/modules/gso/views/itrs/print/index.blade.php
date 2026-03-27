@extends('layouts.print-workspace-master')

@section('content')
    @php
        $sidebarWidth = 'clamp(360px, calc(' . ($paperProfile['height'] ?? '297mm') . ' * 0.34), 430px)';
        $previewWidth = 'min('
            . ($paperProfile['preview_width'] ?? ($paperProfile['width'] ?? '210mm'))
            . ', calc(100vw - '
            . $sidebarWidth
            . ' - 172px))';
    @endphp

    <x-print.workspace
        sidebar-width="{{ $sidebarWidth }}"
        preview-width="{{ $previewWidth }}"
    >
        <x-slot:sidebar>
            @include('gso::itrs.print.partials.controls', [
                'report' => $report,
                'paperProfile' => $paperProfile,
                'filters' => $filters,
            ])
        </x-slot:sidebar>

        @include($paperProfile['pages_view'], [
            'report' => $report,
            'paperProfile' => $paperProfile,
            'headerImage' => asset($paperProfile['header_image_web']),
            'footerImage' => asset($paperProfile['footer_image_web']),
        ])
    </x-print.workspace>
@endsection

@push('styles')
    <x-print.workspace-styles />
    <x-print.workspace-panel-styles />

    @include($paperProfile['styles_view'], [
        'paperProfile' => $paperProfile,
    ])
@endpush

@push('scripts')
    @vite('resources/modules/gso/js/itrs/print.js')
@endpush
