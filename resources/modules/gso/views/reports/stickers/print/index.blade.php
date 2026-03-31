@extends('layouts.master')

@section('styles')
    <meta data-print-workspace-styles-start="1">
    <x-print.workspace-styles />
    <x-print.workspace-panel-styles />
    @include('gso::reports.stickers.print.partials.styles', [
        'sticker' => $sticker,
    ])
    <meta data-print-workspace-styles-end="1">
@endsection

@section('content')
    @php
        $appShellOffset = '420px';
        $sidebarWidth = 'clamp(360px, calc(297mm * 0.34), 420px)';
        $previewWidth = 'min(210mm, calc(100vw - ' . $sidebarWidth . ' - ' . $appShellOffset . '))';
    @endphp

    <div class="print-workspace-body print-workspace-body--embedded">
        <x-print.workspace
            sidebar-width="{{ $sidebarWidth }}"
            preview-width="{{ $previewWidth }}"
        >
            <x-slot:sidebar>
                @include('gso::reports.stickers.print.partials.controls', [
                    'report' => $report,
                    'selectedInventoryItem' => $selectedInventoryItem,
                    'selectedInventoryItems' => $selectedInventoryItems ?? collect(),
                    'selectedStickers' => $selectedStickers ?? [],
                    'availableInventoryItems' => $availableInventoryItems,
                    'sticker' => $sticker,
                    'controls' => $controls,
                    'sheet' => $sheet,
                    'filters' => $filters,
                ])
            </x-slot:sidebar>

            @include('gso::reports.stickers.print.partials.sheet', [
                'sticker' => $sticker,
                'stickers' => $stickers,
                'controls' => $controls,
                'sheet' => $sheet,
                'stickerBackgroundUrl' => $sticker['template_url'] ?? asset('print/sticker.jpg'),
            ])
        </x-print.workspace>
    </div>
@endsection

@push('scripts')
    @vite('resources/modules/gso/js/reports/stickers-print.js')
@endpush
