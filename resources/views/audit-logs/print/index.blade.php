@extends('layouts.print-workspace-master')

@section('content')
    <x-print.workspace
        sidebar-width="clamp(320px, calc(297mm * 0.30), 390px)"
        preview-width="min(210mm, calc(100vw - clamp(320px, calc(297mm * 0.30), 390px) - 160px))"
    >
        <x-slot:sidebar>
            @include('audit-logs.print.partials.controls', [
                'filters' => $filters,
            ])
        </x-slot:sidebar>

        @include('audit-logs.print.partials.pages', [
            'report' => $report,
            'headerImage' => asset('headers/a4_header_template_dark_2480x300.png'),
            'footerImage' => asset('headers/a4_footer_template_dark_2480x250.png'),
        ])
    </x-print.workspace>
@endsection

@push('styles')
    <x-print.workspace-styles />
    <x-print.workspace-panel-styles />
    @include('audit-logs.print.partials.styles')
@endpush