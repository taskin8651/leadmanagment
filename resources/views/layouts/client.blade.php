<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#4f46e5">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'Dashboard' }} — LeadFlow CRM</title>
<link href="{{ asset('assets/vendor/fonts/inter/inter.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('assets/vendor/bootstrap-icons/font/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
@stack('styles')
</head>
<body class="no-overflow">
@php($client = auth()->user()->client)
<div class="app-shell">
    <div class="sidebar-backdrop" data-close="sidebar-mobile"></div>

    <aside class="sidebar">
        <div class="sidebar-brand">
            <span class="mark"><i class="bi bi-diagram-3"></i></span>
            <span class="name">LeadFlow <small>{{ \Illuminate\Support\Str::limit($client->company_name ?? 'Admin', 16) }}</small></span>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-group-label">Overview</div>
            <a class="side-link {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" href="{{ route('client.dashboard') }}">
                <i class="bi bi-grid-1x2"></i><span class="label">Dashboard</span>
            </a>

            <div class="nav-group-label">CRM</div>
            <a class="side-link {{ request()->routeIs('client.leads.*') ? 'active' : '' }}" href="{{ route('client.leads.index') }}">
                <i class="bi bi-person-lines-fill"></i><span class="label">Leads</span>
            </a>
            <a class="side-link {{ request()->routeIs('client.follow-ups.*') ? 'active' : '' }}" href="{{ route('client.follow-ups.index') }}">
                <i class="bi bi-calendar-check"></i><span class="label">Follow-ups</span>
            </a>
            <a class="side-link {{ request()->routeIs('client.attendances.*') ? 'active' : '' }}" href="{{ route('client.attendances.index') }}">
                <i class="bi bi-geo-alt"></i><span class="label">Attendance</span>
            </a>
            <a class="side-link {{ request()->routeIs('client.invoices.*') ? 'active' : '' }}" href="{{ route('client.invoices.index') }}">
                <i class="bi bi-receipt"></i><span class="label">Invoices</span>
            </a>

            @role('Admin')
            <div class="nav-group-label">Team</div>
            <a class="side-link {{ request()->routeIs('client.staff.*') ? 'active' : '' }}" href="{{ route('client.staff.index') }}">
                <i class="bi bi-people"></i><span class="label">Staff</span>
            </a>
            <a class="side-link {{ request()->routeIs('client.reports.*') ? 'active' : '' }}" href="{{ route('client.reports.index') }}">
                <i class="bi bi-bar-chart"></i><span class="label">Reports</span>
            </a>
            <a class="side-link {{ request()->routeIs('client.lead-form.*') ? 'active' : '' }}" href="{{ route('client.lead-form.index') }}">
                <i class="bi bi-link-45deg"></i><span class="label">Lead Capture Form</span>
            </a>
            <a class="side-link {{ request()->routeIs('client.company.*') ? 'active' : '' }}" href="{{ route('client.company.edit') }}">
                <i class="bi bi-building"></i><span class="label">Company Details</span>
            </a>
            <a class="side-link {{ request()->routeIs('client.api-tokens.*') ? 'active' : '' }}" href="{{ route('client.api-tokens.index') }}">
                <i class="bi bi-key"></i><span class="label">API Tokens</span>
            </a>
            <a class="side-link {{ request()->routeIs('client.custom-fields.*') ? 'active' : '' }}" href="{{ route('client.custom-fields.index') }}">
                <i class="bi bi-input-cursor-text"></i><span class="label">Custom Fields</span>
            </a>
            <a class="side-link {{ request()->routeIs('client.audit-logs.*') ? 'active' : '' }}" href="{{ route('client.audit-logs.index') }}">
                <i class="bi bi-shield-lock"></i><span class="label">Audit Log</span>
            </a>
            @endrole
        </nav>
        <div class="sidebar-foot">
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="side-link border-0 bg-transparent w-100 text-start" type="submit"><i class="bi bi-box-arrow-right"></i><span class="label">Logout</span></button>
            </form>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <div class="d-flex align-items-center gap-2">
                <button class="icon-btn d-none d-lg-grid" data-toggle="sidebar-collapse" aria-label="Toggle sidebar"><i class="bi bi-layout-sidebar"></i></button>
                <div class="d-none d-md-block ms-2">
                    <div class="breadcrumb-mini">{{ $client->company_name ?? '' }} <i class="bi bi-chevron-right mx-1" style="font-size:9px"></i> <span class="current">{{ $title ?? 'Dashboard' }}</span></div>
                </div>
            </div>

            <div class="global-search d-none d-md-flex" data-toggle="global-search" role="button" tabindex="0" aria-label="Open global search">
                <i class="bi bi-search"></i><span>Search your leads…</span><span class="kbd">Ctrl K</span>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button class="icon-btn d-md-none" data-toggle="global-search" aria-label="Search"><i class="bi bi-search"></i></button>
                @include('partials.notification-bell')
                <div class="dropdown">
                    <div class="user-chip" data-bs-toggle="dropdown" aria-expanded="false">
                        <x-avatar :name="auth()->user()->name" size="sm" />
                        <span class="meta d-none d-sm-block">
                            <span class="uname d-block">{{ auth()->user()->name }}</span>
                            <span class="urole">{{ auth()->user()->getRoleNames()->first() ?? 'Admin' }}</span>
                        </span>
                        <i class="bi bi-chevron-down muted d-none d-sm-inline" style="font-size:11px"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-2" style="border-radius:14px;padding:8px">
                        <li><span class="dropdown-item-text small muted">Signed in as<br><strong style="color:var(--text)">{{ auth()->user()->email }}</strong></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="dropdown-item rounded-3" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content">
            @unless(auth()->user()->hasVerifiedEmail())
                <div class="card fade-up mb-4 p-3 d-flex flex-row align-items-center gap-3 flex-wrap" style="background:var(--info-soft);border-color:#bfe6ee">
                    <div class="icon-wrap" style="background:#fff;color:var(--info);width:38px;height:38px;margin:0"><i class="bi bi-envelope-exclamation"></i></div>
                    <div class="flex-grow-1">
                        <div class="fw-bold" style="font-size:13.5px">Please verify your email address</div>
                        <div class="muted" style="font-size:12.3px">Check your inbox for a verification link.</div>
                    </div>
                    <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="btn btn-light btn-sm">Resend link</button></form>
                </div>
            @endunless
            <div id="flash-data" data-success="{{ session('success') }}" data-error="{{ $errors->any() ? $errors->first() : '' }}"></div>
            @yield('content')
        </main>
    </div>
</div>

<nav class="bottom-tabbar d-lg-none">
    <a class="bt-item {{ request()->routeIs('client.dashboard') ? 'active' : '' }}" href="{{ route('client.dashboard') }}">
        <i class="bi bi-grid-1x2"></i><span>Home</span>
    </a>
    <a class="bt-item {{ request()->routeIs('client.leads.*') ? 'active' : '' }}" href="{{ route('client.leads.index') }}">
        <i class="bi bi-person-lines-fill"></i><span>Leads</span>
    </a>
    <a class="bt-item {{ request()->routeIs('client.follow-ups.*') ? 'active' : '' }}" href="{{ route('client.follow-ups.index') }}">
        <i class="bi bi-calendar-check"></i><span>Follow-ups</span>
    </a>
    <button type="button" class="bt-item" data-toggle="global-search" aria-label="Search">
        <i class="bi bi-search"></i><span>Search</span>
    </button>
    <button type="button" class="bt-item" data-toggle="sidebar-mobile" aria-label="More">
        <i class="bi bi-grid-3x3-gap"></i><span>More</span>
    </button>
</nav>

<div class="toast-stack"></div>
@include('partials.confirm-delete-modal')
@include('partials.global-search-modal', ['searchEndpoint' => route('client.search')])

<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
@stack('scripts')
</body>
</html>
