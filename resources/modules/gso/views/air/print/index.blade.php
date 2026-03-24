@extends('layouts.print-workspace-master')

@section('content')
    <x-print.workspace
        sidebar-width="clamp(320px, calc({{ $paperProfile['height'] ?? '297mm' }} * 0.30), 390px)"
        preview-width="min({{ $paperProfile['preview_width'] ?? ($paperProfile['width'] ?? '210mm') }}, calc(100vw - clamp(320px, calc({{ $paperProfile['height'] ?? '297mm' }} * 0.30), 390px) - 160px))"
    >
        <x-slot:sidebar>
            @include('gso::air.print.partials.controls', [
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
    @vite('resources/modules/gso/js/air/print.js')
@endpush
