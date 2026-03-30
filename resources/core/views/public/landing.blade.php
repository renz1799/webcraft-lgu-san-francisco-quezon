@extends('layouts.custom-master')

@section('title', 'LGU San Francisco Information System')
@section('meta_description', 'Public landing page for the LGU San Francisco Information System platform.')

@section('styles')
<style>
    .public-landing {
        min-height: 100vh;
        background:
            radial-gradient(circle at top right, rgba(14, 116, 144, 0.12), transparent 28%),
            radial-gradient(circle at top left, rgba(13, 148, 136, 0.10), transparent 24%),
            linear-gradient(180deg, #f8fafc 0%, #ffffff 56%, #eef5ff 100%);
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
        background: rgba(255, 255, 255, 0.9);
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
        background: rgba(14, 116, 144, 0.10);
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

    .platform-grid,
    .reminders-grid,
    .governance-grid {
        display: grid;
        gap: 1rem;
    }

    .platform-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .governance-grid,
    .reminders-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .platform-card,
    .reminder-card,
    .governance-card {
        border: 1px solid rgba(15, 23, 42, 0.07);
        border-radius: 1.15rem;
        background: #fff;
        padding: 1.2rem;
    }

    .platform-card .module-code {
        display: inline-block;
        padding: 0.25rem 0.55rem;
        border-radius: 999px;
        background: rgba(59, 130, 246, 0.10);
        color: #1d4ed8;
        font-size: 0.78rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.22rem 0.55rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        margin-left: 0.45rem;
    }

    .status-chip.live {
        background: rgba(16, 185, 129, 0.12);
        color: #047857;
    }

    .status-chip.soon {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .status-chip.future {
        background: rgba(99, 102, 241, 0.12);
        color: #4338ca;
    }

    .platform-card h5,
    .reminder-card h5,
    .governance-card h5 {
        margin: 0 0 0.5rem;
        font-size: 1rem;
        font-weight: 700;
        color: #172033;
    }

    .platform-card p,
    .reminder-card p,
    .governance-card p {
        margin: 0;
        color: #64748b;
        line-height: 1.7;
        font-size: 0.92rem;
    }

    .platform-actions {
        margin-top: 1rem;
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .public-footer {
        padding: 1rem 0 2.25rem;
        color: #64748b;
        font-size: 0.9rem;
    }

    @media (max-width: 1100px) {
        .platform-grid,
        .governance-grid,
        .reminders-grid {
            grid-template-columns: 1fr 1fr;
        }

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

        .platform-grid,
        .governance-grid,
        .reminders-grid {
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
    $platformTitle = 'LGU San Francisco Information System';
    $isAuthenticated = auth()->check();
    $loginHref = $isAuthenticated ? route('landing') : route('login');
    $navLoginLabel = $isAuthenticated ? 'Open System' : 'Login';
    $actionLoginLabel = $isAuthenticated ? 'Proceed to system' : 'Internal Login';

    $liveModuleCards = collect(config('modules.registry', []))
        ->map(function (array $module, string $code): array {
            $href = null;
            $status = 'Live';

            if ($code === 'GSO' && \Illuminate\Support\Facades\Route::has('gso.landing')) {
                $href = route('gso.landing');
            }

            return [
                'code' => $code,
                'name' => $module['name'] ?? $code,
                'description' => $module['description'] ?? 'Module information page.',
                'href' => $href,
                'status' => $status,
            ];
        })
        ->filter(fn (array $module): bool => ! in_array($module['code'], ['CORE', 'TASKS'], true))
        ->values();

    $futureModuleCards = collect([
        [
            'code' => 'DTS',
            'name' => 'Document Tracking System',
            'description' => 'Planned office workflow module for routing, receiving, forwarding, and monitoring official document movement.',
            'href' => null,
            'status' => 'Future',
        ],
        [
            'code' => 'PROCUREMENT',
            'name' => 'Procurement Management',
            'description' => 'Planned procurement workspace for BAC-aligned tracking, supplier coordination, and purchasing workflows.',
            'href' => null,
            'status' => 'Future',
        ],
    ]);

    $moduleCards = $liveModuleCards
        ->concat($futureModuleCards)
        ->values();
@endphp

<div class="public-shell">
    <header class="public-topbar">
        <div class="public-brand">
            <img src="{{ asset('build/assets/images/brand-logos/desktop-logo.png') }}" alt="LGU San Francisco logo">
            <div>
                <div class="public-brand-kicker">{{ $entityName }}</div>
                <div class="public-brand-title">{{ $platformTitle }}</div>
            </div>
        </div>
        <nav class="public-nav">
            <a href="#platform-modules">Modules</a>
            <a href="#governance">Platform Coverage</a>
            <a href="#reminders">Reminders</a>
            <a href="{{ $loginHref }}" class="ti-btn ti-btn-primary !bg-primary !text-white !font-medium">{{ $navLoginLabel }}</a>
        </nav>
    </header>

    <section class="hero-grid">
        <div class="hero-panel">
            <div class="hero-kicker">
                <i class="bx bx-network-chart"></i>
                Platform Landing
            </div>
            <h1 class="hero-title">{{ $platformTitle }}</h1>
            <p class="hero-text">
                A shared digital platform for LGU office systems, internal workflows, and operational records. This public page
                gives visitors a clear entry point into the active information systems already running under the LGU platform.
            </p>
            <div class="hero-actions">
                <a href="{{ route('login') }}" class="ti-btn ti-btn-primary !bg-primary !text-white !font-medium">{{ $actionLoginLabel }}</a>
                <a href="#platform-modules" class="ti-btn ti-btn-light !font-medium">View Active Modules</a>
            </div>
            <p class="hero-note">
                Internal workflow actions, approvals, and records creation remain limited to authenticated and authorized users.
            </p>
        </div>

        <aside class="info-panel">
            <div class="info-card">
                <i class="bx bx-grid-alt"></i>
                <h5>One platform, multiple offices</h5>
                <p>Each module keeps its own workflows while reusing shared access, audit, notification, and task standards.</p>
            </div>
            <div class="info-card">
                <i class="bx bx-shield-quarter"></i>
                <h5>Controlled internal access</h5>
                <p>Platform users sign in through one shared system while permissions and roles stay scoped to the proper office context.</p>
            </div>
            <div class="info-card">
                <i class="bx bx-transfer-alt"></i>
                <h5>Scalable by module</h5>
                <p>New office systems can be added without duplicating the platform foundation for identity, access, logging, and UI standards.</p>
            </div>
        </aside>
    </section>

    <div class="section-wrap">
        <section class="section-panel" id="platform-modules">
            <div class="section-head">
                <span class="section-kicker">Platform Modules</span>
                <h2>Active and planned system areas</h2>
                <p>
                    The platform is designed to host multiple office systems under one LGU-wide foundation. Public visitors can use these
                    module entries as public reference pages, while internal work remains behind authenticated access.
                </p>
            </div>
            <div class="platform-grid">
                @foreach ($moduleCards as $module)
                    <div class="platform-card">
                        <span class="module-code">{{ $module['code'] }}</span>
                        <span class="status-chip {{
                            strtolower($module['status']) === 'live'
                                ? 'live'
                                : (strtolower($module['status']) === 'future' ? 'future' : 'soon')
                        }}">{{ $module['status'] }}</span>
                        <h5>{{ $module['name'] }}</h5>
                        <p>{{ $module['description'] }}</p>
                        <div class="platform-actions">
                            @if ($module['href'])
                                <a href="{{ $module['href'] }}" class="ti-btn ti-btn-primary !bg-primary !text-white !font-medium">
                                    Open Public Page
                                </a>
                            @else
                                <span class="ti-btn ti-btn-light !font-medium !cursor-default">
                                    {{ strtolower($module['status']) === 'future' ? 'Planned module' : 'Public page coming soon' }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="section-panel" id="governance">
            <div class="section-head">
                <span class="section-kicker">Platform Coverage</span>
                <h2>Shared capabilities across the system</h2>
                <p>
                    Office modules do not stand alone. They run on top of shared platform capabilities so the LGU can keep identity,
                    access, logging, notifications, and reusable workflow infrastructure aligned.
                </p>
            </div>
            <div class="governance-grid">
                <div class="governance-card">
                    <h5>Identity and access</h5>
                    <p>Users, roles, module access, and permission assignments are managed through a shared platform access model.</p>
                </div>
                <div class="governance-card">
                    <h5>Audit and notifications</h5>
                    <p>Important actions and workflow changes can be traced and surfaced through shared audit and notification capabilities.</p>
                </div>
                <div class="governance-card">
                    <h5>Tasks and approvals</h5>
                    <p>Modules can surface work inside their own shell while still reusing the shared task engine and review standards.</p>
                </div>
            </div>
        </section>

        <section class="section-panel" id="reminders">
            <div class="section-head">
                <span class="section-kicker">Public Reminders</span>
                <h2>Before using internal platform features</h2>
                <p>
                    A few practical reminders for visitors and office users accessing the platform.
                </p>
            </div>
            <div class="reminders-grid">
                <div class="reminder-card">
                    <h5>Public pages are informational</h5>
                    <p>Public landing pages help visitors understand the office systems available, but record creation and approvals remain internal.</p>
                </div>
                <div class="reminder-card">
                    <h5>Office workflows stay module-owned</h5>
                    <p>Each module keeps its own processes, records, and business rules even while using the same platform foundation.</p>
                </div>
                <div class="reminder-card">
                    <h5>Login is for authorized users</h5>
                    <p>Only users with assigned accounts and module access should proceed into internal dashboards and transaction screens.</p>
                </div>
            </div>
        </section>
    </div>

    <footer class="public-footer">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <strong>{{ $entityName }}</strong> &middot; {{ $platformTitle }}
            </div>
            <div>
                <a href="{{ route('login') }}" class="text-primary font-semibold">{{ $actionLoginLabel }}</a>
            </div>
        </div>
    </footer>
</div>
@endsection
