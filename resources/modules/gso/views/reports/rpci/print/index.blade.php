@extends('layouts.master')

@php
    $resolvedPreviewEngine = app(\App\Core\Services\Infrastructure\HybridPdfGenerator::class)->resolveDriver();
    $previewStylesView = $resolvedPreviewEngine === 'dompdf'
        ? ($paperProfile['dompdf_pdf_styles_view'] ?? $paperProfile['pdf_styles_view'] ?? $paperProfile['styles_view'])
        : $paperProfile['styles_view'];
    $previewPagesView = $resolvedPreviewEngine === 'dompdf'
        ? ($paperProfile['dompdf_pdf_pages_view'] ?? $paperProfile['pages_view'])
        : $paperProfile['pages_view'];
@endphp

@section('styles')
    <meta data-print-workspace-styles-start="1">
    <x-print.workspace-styles />
    <x-print.workspace-panel-styles />

    @include($previewStylesView, [
        'paperProfile' => $paperProfile,
        'pdfEngine' => $resolvedPreviewEngine,
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
                @include('gso::reports.rpci.print.partials.controls', [
                    'report' => $report,
                    'paperProfile' => $paperProfile,
                    'availableFunds' => $availableFunds,
                    'filters' => $filters,
                ])
            </x-slot:sidebar>

            @include($previewPagesView, [
                'report' => $report,
                'paperProfile' => $paperProfile,
                'headerImage' => !empty($paperProfile['header_image_web']) ? asset($paperProfile['header_image_web']) : null,
                'footerImage' => !empty($paperProfile['footer_image_web']) ? asset($paperProfile['footer_image_web']) : null,
                'pdfEngine' => $resolvedPreviewEngine,
            ])
        </x-print.workspace>
    </div>
@endsection

@push('scripts')
    @vite('resources/modules/gso/js/reports/rpci-print.js')
@endpush
