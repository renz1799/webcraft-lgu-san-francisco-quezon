@extends('layouts.custom-master')

@section('title', 'GSO San Francisco Quezon')
@section('meta_description', 'Public information page for the General Services Office Information System of San Francisco, Quezon.')

@section('styles')
<style>
    .public-landing {
        min-height: 100vh;
        background:
            radial-gradient(circle at top right, rgba(13, 148, 136, 0.12), transparent 28%),
            radial-gradient(circle at top left, rgba(59, 130, 246, 0.10), transparent 24%),
            linear-gradient(180deg, #f7f5ef 0%, #ffffff 58%, #eef6f4 100%);
    }

    .public-shell {
        max-width: 1180px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    .public-topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem 0;
    }

    .public-brand {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .public-brand img {
        width: 56px;
        height: 56px;
        object-fit: contain;
    }

    .public-brand-kicker {
        font-size: 0.75rem;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #0f766e;
        font-weight: 700;
        margin-bottom: 0.2rem;
    }

    .public-brand-title {
        font-size: 1.05rem;
        font-weight: 700;
        color: #172033;
        line-height: 1.2;
    }

    .public-nav {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .public-nav a {
        color: #475569;
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
    }

    .hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr);
        gap: 1.5rem;
        align-items: stretch;
        padding: 2.5rem 0 1.5rem;
    }

    .hero-panel,
    .info-panel,
    .section-panel {
        background: rgba(255, 255, 255, 0.88);
        border: 1px solid rgba(15, 23, 42, 0.07);
        box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
        border-radius: 1.5rem;
        backdrop-filter: blur(8px);
    }

    .hero-panel {
        padding: 2.5rem;
    }

    .hero-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.85rem;
        border-radius: 999px;
        background: rgba(13, 148, 136, 0.10);
        color: #0f766e;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 1rem;
    }

    .hero-title {
        font-size: clamp(2rem, 4vw, 3.7rem);
        line-height: 1.05;
        letter-spacing: -0.04em;
        font-weight: 800;
        color: #172033;
        margin-bottom: 1rem;
    }

    .hero-text {
        font-size: 1rem;
        line-height: 1.8;
        color: #52607a;
        max-width: 48rem;
    }

    .hero-actions {
        display: flex;
        gap: 0.9rem;
        flex-wrap: wrap;
        margin-top: 2rem;
    }

    .hero-note {
        margin-top: 1.25rem;
        color: #64748b;
        font-size: 0.92rem;
    }

    .info-panel {
        padding: 1.3rem;
        display: grid;
        gap: 1rem;
    }

    .info-card {
        padding: 1.15rem 1.1rem;
        border-radius: 1.15rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid rgba(15, 23, 42, 0.06);
    }

    .info-card i {
        font-size: 1.4rem;
        color: #0f766e;
        margin-bottom: 0.8rem;
        display: inline-block;
    }

    .info-card h5 {
        margin: 0 0 0.45rem;
        font-size: 1rem;
        font-weight: 700;
        color: #172033;
    }

    .info-card p {
        margin: 0;
        color: #64748b;
        font-size: 0.93rem;
        line-height: 1.65;
    }

    .section-wrap {
        padding: 1rem 0 3rem;
        display: grid;
        gap: 1.5rem;
    }

    .section-panel {
        padding: 2rem;
    }

    .section-head {
        margin-bottom: 1.5rem;
        max-width: 42rem;
    }

    .section-kicker {
        display: inline-block;
        color: #0f766e;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }

    .section-head h2 {
        font-size: clamp(1.5rem, 2.5vw, 2.2rem);
        font-weight: 800;
        color: #172033;
        margin-bottom: 0.75rem;
    }

    .section-head p {
        margin: 0;
        color: #64748b;
        line-height: 1.75;
    }

    .coverage-grid,
    .reminders-grid,
    .office-grid {
        display: grid;
        gap: 1rem;
    }

    .coverage-grid {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }

    .coverage-card,
    .reminder-card,
    .office-card {
        border: 1px solid rgba(15, 23, 42, 0.07);
        border-radius: 1.15rem;
        background: #fff;
        padding: 1.2rem;
    }

    .coverage-card .doc-code {
        display: inline-block;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.10);
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }

    .coverage-card h5,
    .reminder-card h5,
    .office-card h5 {
        margin: 0 0 0.5rem;
        font-size: 1rem;
        font-weight: 700;
        color: #172033;
    }

    .coverage-card p,
    .reminder-card p,
    .office-card p,
    .office-card li {
        margin: 0;
        color: #64748b;
        line-height: 1.7;
        font-size: 0.92rem;
    }

    .reminders-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .office-grid {
        grid-template-columns: 1.1fr 0.9fr;
    }

    .office-card ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .office-card li + li {
        margin-top: 0.7rem;
    }

    .field-label {
        display: block;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #0f766e;
        text-transform: uppercase;
        margin-bottom: 0.18rem;
    }

    .public-footer {
        padding: 1rem 0 2.25rem;
        color: #64748b;
        font-size: 0.9rem;
    }

    @media (max-width: 1100px) {
        .coverage-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .reminders-grid,
        .office-grid,
        .hero-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .public-shell {
            padding: 0 1rem;
        }

        .public-topbar {
            align-items: flex-start;
            flex-direction: column;
        }

        .hero-panel,
        .section-panel {
            padding: 1.35rem;
        }

        .coverage-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('error-body')
<body class="public-landing">
@endsection

@section('content')
@php
    $entityName = config('print.entity_name') ?: 'LGU San Francisco';
    $officeName = config('gso.gso_department_name', 'General Services Office');
    $officeAbbr = config('gso.gso_department_abbr', 'GSO');
    $designateName = config('gso.gso_designate_name', 'KRISTIAN D. EDANO');
    $designateRole = config('gso.gso_designate_designation', 'Supply Officer-Designate');
    $isAuthenticated = auth()->check();
    $loginHref = $isAuthenticated ? route('gso.dashboard') : route('login');
    $navLoginLabel = $isAuthenticated ? 'Dashboard' : 'Login';
    $actionLoginLabel = $isAuthenticated ? 'Open Dashboard' : 'Internal Login';
@endphp

<div class="public-shell">
    <header class="public-topbar">
        <div class="public-brand">
            <img src="{{ asset('build/assets/images/brand-logos/desktop-logo.png') }}" alt="GSO logo">
            <div>
                <div class="public-brand-kicker">{{ $entityName }}</div>
                <div class="public-brand-title">{{ $officeName }} Information System</div>
            </div>
        </div>
        <nav class="public-nav">
            <a href="#coverage">Documents</a>
            <a href="#office-info">Office Information</a>
            <a href="#reminders">Reminders</a>
            <a href="{{ $loginHref }}" class="ti-btn ti-btn-primary !bg-primary !text-white !font-medium">{{ $navLoginLabel }}</a>
        </nav>
    </header>

    <section class="hero-grid">
        <div class="hero-panel">
            <div class="hero-kicker">
                <i class="bx bx-buildings"></i>
                Public Information
            </div>
            <h1 class="hero-title">General Services Office Information System</h1>
            <p class="hero-text">
                A centralized system for documenting inspection, issuance, accountability, and property movement records for the
                {{ $officeName }}. This public page gives visitors a clear view of the office workflows covered by the system.
            </p>
            <div class="hero-actions">
                <a href="{{ $loginHref }}" class="ti-btn ti-btn-primary !bg-primary !text-white !font-medium">{{ $actionLoginLabel }}</a>
                <a href="#coverage" class="ti-btn ti-btn-light !font-medium">View Document Coverage</a>
            </div>
            <p class="hero-note">
                Record creation, approvals, and workflow actions remain limited to authorized personnel.
            </p>
        </div>

        <aside class="info-panel">
            <div class="info-card">
                <i class="bx bx-file"></i>
                <h5>Documented workflows</h5>
                <p>AIR, RIS, PAR, ICS, and PTR records are handled in one connected office system.</p>
            </div>
            <div class="info-card">
                <i class="bx bx-shield-quarter"></i>
                <h5>Controlled access</h5>
                <p>Issuance, accountability, and transfer actions remain protected behind authorized office access.</p>
            </div>
            <div class="info-card">
                <i class="bx bx-user-check"></i>
                <h5>Office accountability</h5>
                <p>Departments, accountable officers, and signatories are tracked across inventory and property workflows.</p>
            </div>
        </aside>
    </section>

    <div class="section-wrap">
        <section class="section-panel" id="coverage">
            <div class="section-head">
                <span class="section-kicker">Document Coverage</span>
                <h2>Core document flows handled by the system</h2>
                <p>
                    The platform supports the office's internal record flows for acceptance, issuance, accountability, and property movement.
                    This page gives public visitors a plain-language guide to the major document types used by the office.
                </p>
            </div>
            <div class="coverage-grid">
                <div class="coverage-card">
                    <span class="doc-code">AIR</span>
                    <h5>Acceptance and Inspection Report</h5>
                    <p>Used for inspecting delivered items and documenting acceptance, quantities, and follow-up handling for incomplete deliveries.</p>
                </div>
                <div class="coverage-card">
                    <span class="doc-code">RIS</span>
                    <h5>Requisition and Issue Slip</h5>
                    <p>Used for recording the release of consumable inventory items requested by offices or authorized end users.</p>
                </div>
                <div class="coverage-card">
                    <span class="doc-code">PAR</span>
                    <h5>Property Acknowledgment Receipt</h5>
                    <p>Used for assigning accountable property items to end users and documenting their custody responsibility.</p>
                </div>
                <div class="coverage-card">
                    <span class="doc-code">ICS</span>
                    <h5>Inventory Custodian Slip</h5>
                    <p>Used for documenting accountability over semi-expendable or inventory-classified custodial items.</p>
                </div>
                <div class="coverage-card">
                    <span class="doc-code">PTR</span>
                    <h5>Property Transfer Report</h5>
                    <p>Used for transferring accountable property items from one responsible office or officer to another.</p>
                </div>
            </div>
        </section>

        <section class="section-panel" id="office-info">
            <div class="section-head">
                <span class="section-kicker">Office Information</span>
                <h2>Public-facing office details</h2>
                <p>
                    This page serves as a simple public entry point. Formal transactions, approvals, and records creation are still processed through the office's authorized workflow.
                </p>
            </div>
            <div class="office-grid">
                <div class="office-card">
                    <ul>
                        <li>
                            <span class="field-label">Entity</span>
                            {{ $entityName }}
                        </li>
                        <li>
                            <span class="field-label">Office</span>
                            {{ $officeName }} ({{ $officeAbbr }})
                        </li>
                        <li>
                            <span class="field-label">Designated Custodian</span>
                            {{ $designateName }}<br>
                            <span class="text-[#64748b]">{{ $designateRole }}</span>
                        </li>
                        <li>
                            <span class="field-label">System Access</span>
                            Internal access is available through authenticated office accounts only.
                        </li>
                    </ul>
                </div>
                <div class="office-card">
                    <ul>
                        <li>
                            <span class="field-label">Transaction Support</span>
                            Coordinate directly with the {{ $officeName }} for inspections, issuance concerns, accountability updates, and property transfer documentation.
                        </li>
                        <li>
                            <span class="field-label">Office Hours</span>
                            During regular office hours, Monday to Friday.
                        </li>
                        <li>
                            <span class="field-label">Login Access</span>
                            <a href="{{ $loginHref }}" class="text-primary font-semibold">
                                {{ $isAuthenticated ? 'Proceed to dashboard' : 'Proceed to internal login' }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="section-panel" id="reminders">
            <div class="section-head">
                <span class="section-kicker">Public Reminders</span>
                <h2>Before coordinating with the office</h2>
                <p>
                    A few practical reminders to keep coordination clear and efficient.
                </p>
            </div>
            <div class="reminders-grid">
                <div class="reminder-card">
                    <h5>Authorized use only</h5>
                    <p>The system's transaction screens are intended for authorized internal users. Public visitors may use this page for reference and office contact direction.</p>
                </div>
                <div class="reminder-card">
                    <h5>Records remain subject to review</h5>
                    <p>Inspection, issuance, accountability, and transfer documents remain subject to office verification, signatory review, and supporting records.</p>
                </div>
                <div class="reminder-card">
                    <h5>Coordinate for account concerns</h5>
                    <p>For account creation, login assistance, or clarification on office records, please coordinate directly with the {{ $officeName }}.</p>
                </div>
            </div>
        </section>
    </div>

    <footer class="public-footer">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <strong>{{ $entityName }}</strong> &middot; {{ $officeName }}
            </div>
            <div>
                <a href="{{ $loginHref }}" class="text-primary font-semibold">{{ $actionLoginLabel }}</a>
            </div>
        </div>
    </footer>
</div>
@endsection
