@extends('layouts.custom-master')

@section('title', ($asset['type_label'] ?? 'Asset') . ' Asset Record')

@section('styles')
<style>
    .public-asset-page {
        min-height: 100vh;
        background:
            radial-gradient(circle at top left, rgba(37, 99, 235, 0.10), transparent 22%),
            radial-gradient(circle at top right, rgba(249, 115, 22, 0.10), transparent 24%),
            linear-gradient(180deg, #f8fafc 0%, #ffffff 56%, #eef2ff 100%);
    }

    .public-asset-shell {
        max-width: 1080px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    .public-asset-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .public-asset-brand {
        display: flex;
        align-items: center;
        gap: 0.9rem;
    }

    .public-asset-brand img {
        width: 52px;
        height: 52px;
        object-fit: contain;
    }

    .public-asset-brand-kicker {
        font-size: 0.75rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #0f766e;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .public-asset-brand-title {
        font-size: 1rem;
        font-weight: 800;
        color: #172033;
        line-height: 1.2;
    }

    .asset-card {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(15, 23, 42, 0.07);
        border-radius: 1.5rem;
        box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(8px);
        overflow: hidden;
    }

    .asset-header {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(240px, 0.95fr);
        gap: 1.5rem;
        padding: 2rem;
        border-bottom: 1px solid rgba(15, 23, 42, 0.06);
    }

    .asset-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        font-size: 0.75rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 700;
        color: #0f766e;
        background: rgba(13, 148, 136, 0.1);
        margin-bottom: 0.95rem;
    }

    .asset-title {
        font-size: clamp(1.6rem, 3vw, 2.7rem);
        line-height: 1.08;
        letter-spacing: -0.04em;
        font-weight: 800;
        color: #172033;
        margin: 0 0 0.75rem;
    }

    .asset-subtitle {
        margin: 0;
        color: #5b6b82;
        line-height: 1.75;
        font-size: 0.98rem;
    }

    .asset-reference-panel {
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 1.15rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 1.2rem;
    }

    .asset-reference-label {
        display: block;
        font-size: 0.74rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        font-weight: 700;
        color: #0f766e;
        margin-bottom: 0.35rem;
    }

    .asset-reference-value {
        display: block;
        font-size: 1.2rem;
        font-weight: 800;
        color: #172033;
        word-break: break-word;
        line-height: 1.35;
        margin-bottom: 1rem;
    }

    .asset-reference-meta {
        display: grid;
        gap: 0.75rem;
    }

    .asset-reference-meta div {
        color: #5b6b82;
        font-size: 0.92rem;
        line-height: 1.55;
    }

    .asset-content {
        display: grid;
        grid-template-columns: minmax(300px, 0.9fr) minmax(0, 1.1fr);
        gap: 1.5rem;
        padding: 2rem;
    }

    .asset-image-box,
    .asset-details-box,
    .asset-note-box {
        border: 1px solid rgba(15, 23, 42, 0.06);
        border-radius: 1.15rem;
        background: #fff;
        padding: 1.2rem;
    }

    .asset-image-frame {
        aspect-ratio: 4 / 3;
        border-radius: 1rem;
        border: 1px dashed rgba(148, 163, 184, 0.7);
        background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .asset-image-frame img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .asset-image-empty {
        text-align: center;
        color: #64748b;
        padding: 1rem;
    }

    .asset-image-empty i {
        display: inline-block;
        font-size: 2rem;
        color: #94a3b8;
        margin-bottom: 0.65rem;
    }

    .asset-photo-strip {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(88px, 1fr));
        gap: 0.75rem;
    }

    .asset-photo-thumb {
        display: block;
        aspect-ratio: 1 / 1;
        border-radius: 0.9rem;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.35);
        background: #fff;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
    }

    .asset-photo-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .asset-section-title {
        margin: 0 0 1rem;
        font-size: 1rem;
        font-weight: 800;
        color: #172033;
    }

    .asset-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.95rem 1rem;
    }

    .asset-detail {
        min-height: 72px;
    }

    .asset-detail-label {
        display: block;
        font-size: 0.74rem;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: #0f766e;
        font-weight: 700;
        margin-bottom: 0.3rem;
    }

    .asset-detail-value {
        color: #172033;
        font-size: 0.97rem;
        font-weight: 700;
        line-height: 1.55;
        word-break: break-word;
    }

    .asset-note-box {
        margin-top: 1rem;
        background: linear-gradient(180deg, #fff 0%, #fffbeb 100%);
    }

    .asset-note-box p {
        margin: 0;
        color: #5b6b82;
        line-height: 1.75;
        font-size: 0.93rem;
    }

    @media (max-width: 900px) {
        .asset-header,
        .asset-content {
            grid-template-columns: 1fr;
        }

        .asset-detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('error-body')
<body class="public-asset-page">
@endsection

@section('content')
<div class="public-asset-shell">
    <header class="public-asset-topbar">
        <div class="public-asset-brand">
            <img src="{{ asset('build/assets/images/brand-logos/desktop-logo.png') }}" alt="GSO logo">
            <div>
                <div class="public-asset-brand-kicker">Public Asset Record</div>
                <div class="public-asset-brand-title">General Services Office - San Francisco, Quezon</div>
            </div>
        </div>
        <a href="{{ route('landing') }}" class="ti-btn ti-btn-light !font-medium">Back to Home</a>
    </header>

    <article class="asset-card">
        <section class="asset-header">
            <div>
                <div class="asset-pill">
                    <i class="bx bx-qr-scan"></i>
                    {{ $asset['type_label'] }} Asset Verification
                </div>
                <h1 class="asset-title">{{ $asset['description'] }}</h1>
                <p class="asset-subtitle">
                    This public page is provided for inventory identification and verification.
                    For workflow actions, approvals, and internal records, please coordinate directly with the General Services Office.
                </p>
            </div>

            <div class="asset-reference-panel">
                <span class="asset-reference-label">{{ $asset['reference_label'] }}</span>
                <span class="asset-reference-value">{{ $asset['reference_value'] }}</span>
                <div class="asset-reference-meta">
                    <div><strong>Asset Type:</strong> {{ $asset['type_label'] }}</div>
                    <div><strong>Assigned Office:</strong> {{ $asset['office'] }}</div>
                    <div><strong>Status:</strong> {{ $asset['status'] }}</div>
                </div>
            </div>
        </section>

        <section class="asset-content">
            <div>
                <div class="asset-image-box">
                    <h2 class="asset-section-title">Asset Image</h2>
                    <div class="asset-image-frame">
                        @if($asset['primary_photo_url'])
                            <img src="{{ $asset['primary_photo_url'] }}" alt="{{ $asset['description'] }}">
                        @else
                            <div class="asset-image-empty">
                                <i class="bx bx-image-alt"></i>
                                <div>No public image available for this asset.</div>
                            </div>
                        @endif
                    </div>

                    @if(!empty($asset['photos']))
                        <div class="asset-photo-strip">
                            @foreach($asset['photos'] as $photo)
                                <a href="{{ $photo['url'] }}" target="_blank" rel="noopener" class="asset-photo-thumb" title="{{ $photo['caption'] }}">
                                    <img src="{{ $photo['url'] }}" alt="{{ $photo['caption'] }}">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="asset-note-box">
                    <h2 class="asset-section-title">Public Note</h2>
                    <p>
                        This page is intended for inventory identification and verification only.
                        Sensitive internal details such as cost centers, accountable-person records, workflow history, and task activity are not shown publicly.
                    </p>
                </div>
            </div>

            <div class="asset-details-box">
                <h2 class="asset-section-title">Asset Details</h2>
                <div class="asset-detail-grid">
                    <div class="asset-detail">
                        <span class="asset-detail-label">Description</span>
                        <div class="asset-detail-value">{{ $asset['description'] }}</div>
                    </div>
                    <div class="asset-detail">
                        <span class="asset-detail-label">Brand</span>
                        <div class="asset-detail-value">{{ $asset['brand'] }}</div>
                    </div>
                    <div class="asset-detail">
                        <span class="asset-detail-label">Model</span>
                        <div class="asset-detail-value">{{ $asset['model'] }}</div>
                    </div>
                    <div class="asset-detail">
                        <span class="asset-detail-label">Serial Number</span>
                        <div class="asset-detail-value">{{ $asset['serial_number'] }}</div>
                    </div>
                    <div class="asset-detail">
                        <span class="asset-detail-label">Acquisition Date</span>
                        <div class="asset-detail-value">{{ $asset['acquisition_date'] }}</div>
                    </div>
                    <div class="asset-detail">
                        <span class="asset-detail-label">Office / Department</span>
                        <div class="asset-detail-value">{{ $asset['office'] }}</div>
                    </div>
                    <div class="asset-detail">
                        <span class="asset-detail-label">Current Status</span>
                        <div class="asset-detail-value">{{ $asset['status'] }}</div>
                    </div>
                    <div class="asset-detail">
                        <span class="asset-detail-label">Current Condition</span>
                        <div class="asset-detail-value">{{ $asset['condition'] }}</div>
                    </div>
                </div>
            </div>
        </section>
    </article>
</div>
@endsection
