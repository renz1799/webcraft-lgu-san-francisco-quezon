@extends('layouts.master')

@php
    $resolvedPreviewEngine = app(\App\Core\Services\Infrastructure\HybridPdfGenerator::class)->resolveDriver();
    $previewStylesView = $resolvedPreviewEngine === 'dompdf'
        ? ($paperProfile['dompdf_pdf_styles_view'] ?? $paperProfile['pdf_styles_view'] ?? $paperProfile['styles_view'])
        : $paperProfile['styles_view'];
    $previewPagesView = $resolvedPreviewEngine === 'dompdf'
        ? ($paperProfile['dompdf_pdf_pages_view'] ?? $paperProfile['pages_view'])
        : $paperProfile['pages_view'];
    $pdfPreviewFragment = $paperProfile['pdf_preview_fragment'] ?? 'toolbar=0&navpanes=0&scrollbar=0&zoom=100';
    $pdfPreviewUrl = route('gso.reports.regspi.print.pdf', array_merge($filters ?? [], ['inline' => 1]))
        . '#' . $pdfPreviewFragment;
@endphp

@section('styles')
    <meta data-print-workspace-styles-start="1">
    <x-print.workspace-styles />
    <x-print.workspace-panel-styles />

    @include($previewStylesView, [
        'paperProfile' => $paperProfile,
        'pdfEngine' => $resolvedPreviewEngine,
    ])
    @if ($resolvedPreviewEngine === 'dompdf')
        <style>
            .gso-report-pdf-preview-shell {
                width: {{ $paperProfile['width'] ?? '297mm' }};
                max-width: 100%;
                margin: 0 auto;
                background: #fff;
                box-shadow: 0 24px 46px rgba(15, 23, 42, 0.14);
                overflow: hidden;
            }

            .gso-report-pdf-preview-frame {
                display: block;
                width: 100%;
                height: calc({{ $paperProfile['height'] ?? '210mm' }} + 8mm);
                border: 0;
                background: #fff;
            }
        </style>
    @endif
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
                @include('gso::reports.regspi.print.partials.controls', [
                    'report' => $report,
                    'paperProfile' => $paperProfile,
                    'availableFunds' => $availableFunds,
                    'availableDepartments' => $availableDepartments,
                    'availableAccountableOfficers' => $availableAccountableOfficers,
                    'filters' => $filters,
                ])
            </x-slot:sidebar>

            @if ($resolvedPreviewEngine === 'dompdf')
                <div class="gso-report-pdf-preview-shell">
                    <iframe
                        class="gso-report-pdf-preview-frame"
                        src="{{ $pdfPreviewUrl }}"
                        title="REGSPI PDF Preview"
                    ></iframe>
                </div>
            @else
                @include($previewPagesView, [
                    'report' => $report,
                    'paperProfile' => $paperProfile,
                    'headerImage' => !empty($paperProfile['header_image_web']) ? asset($paperProfile['header_image_web']) : null,
                    'footerImage' => !empty($paperProfile['footer_image_web']) ? asset($paperProfile['footer_image_web']) : null,
                    'pdfEngine' => $resolvedPreviewEngine,
                ])
            @endif
        </x-print.workspace>
    </div>
@endsection

@push('scripts')
    @vite('resources/modules/gso/js/reports/regspi-print.js')
@endpush
