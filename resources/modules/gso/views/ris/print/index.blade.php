@extends('layouts.print-workspace-master')

@php
    $ris = $report['ris'] ?? [];
    $resolvedPreviewEngine = app(\App\Core\Services\Infrastructure\HybridPdfGenerator::class)->resolveDriver();
    $previewStylesView = $resolvedPreviewEngine === 'dompdf'
        ? ($paperProfile['dompdf_pdf_styles_view'] ?? $paperProfile['pdf_styles_view'] ?? $paperProfile['styles_view'])
        : $paperProfile['styles_view'];
    $pdfPreviewUrl = route('gso.ris.print.pdf', array_merge(['ris' => $ris['id'] ?? ''], $filters ?? [], ['inline' => 1]))
        . '#toolbar=0&navpanes=0&scrollbar=0&view=FitH';
@endphp

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
            @include('gso::ris.print.partials.controls', [
                'report' => $report,
                'paperProfile' => $paperProfile,
                'filters' => $filters,
            ])
        </x-slot:sidebar>

        @if ($resolvedPreviewEngine === 'dompdf')
            <div class="gso-report-pdf-preview-shell">
                <iframe
                    class="gso-report-pdf-preview-frame"
                    src="{{ $pdfPreviewUrl }}"
                    title="RIS PDF Preview"
                ></iframe>
            </div>
        @else
            @include($paperProfile['pages_view'], [
                'report' => $report,
                'paperProfile' => $paperProfile,
                'headerImage' => asset($paperProfile['header_image_web']),
                'footerImage' => asset($paperProfile['footer_image_web']),
                'pdfEngine' => $resolvedPreviewEngine,
            ])
        @endif
    </x-print.workspace>
@endsection

@push('styles')
    <x-print.workspace-styles />
    <x-print.workspace-panel-styles />

    @include($previewStylesView, [
        'paperProfile' => $paperProfile,
        'pdfEngine' => $resolvedPreviewEngine,
    ])
    @if ($resolvedPreviewEngine === 'dompdf')
        <style>
            .gso-report-pdf-preview-shell {
                width: {{ $paperProfile['width'] ?? '210mm' }};
                max-width: 100%;
                margin: 0 auto;
                background: #fff;
                box-shadow: 0 24px 46px rgba(15, 23, 42, 0.14);
                overflow: hidden;
            }

            .gso-report-pdf-preview-frame {
                display: block;
                width: 100%;
                height: calc({{ $paperProfile['height'] ?? '297mm' }} + 8mm);
                border: 0;
                background: #fff;
            }
        </style>
    @endif
@endpush

@push('scripts')
    @vite('resources/modules/gso/js/ris/print.js')
@endpush
