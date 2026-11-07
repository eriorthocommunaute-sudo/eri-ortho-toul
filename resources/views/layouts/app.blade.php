<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.community_short') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('head')
</head>
<body>
@php
    $user = auth()->user();
    $roles = $user?->roles ?? collect();
    $permissions = $roles->flatMap(fn ($role) => $role->permissions())->unique();
    $can = fn (string $permission) => $permissions->contains($permission);
    $roleLabel = $roles->isNotEmpty() ? __('app.role_'.$roles->first()->slug) : __('app.role_user');
    $initials = collect(preg_split('/\s+/', trim($user?->name ?? '')))->filter()->take(2)->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->implode('') ?: 'OC';

    $routeName = request()->route()?->getName() ?? '';
    $section = explode('.', $routeName)[0] ?? '';
    $sectionLabels = [
        'dashboard' => __('app.dashboard'),
        'members' => __('app.members'),
        'contributions' => __('app.contributions'),
        'receipts' => __('app.receipts'),
        'offrandes' => __('app.offrandes'),
        'donations' => __('app.donations'),
        'financial-entries' => __('app.financial_entries'),
        'recurring-expenses' => 'Dépenses fixes',
        'equipment' => __('app.equipment'),
        'priests' => __('app.priests'),
        'priest-transports' => __('app.priest_transports'),
        'priest-indemnities' => __('app.priest_indemnities'),
        'events' => __('app.events'),
        'purchase-requests' => __('app.purchase_requests'),
        'exceptional-demands' => __('app.exceptional_demands'),
        'contribution-notices' => __('app.contribution_notices'),
        'meeting-minutes' => __('app.meeting_minutes'),
        'pdf' => __('app.documents'),
    ];
    $sectionLabel = $sectionLabels[$section] ?? __('app.dashboard');
    if ($routeName === 'financial-entries.report') {
        $sectionLabel = __('app.financial_reports');
    }

    $financesActive = request()->routeIs('contributions.*', 'founding-cotisations.*', 'receipts.*', 'offrandes.*', 'donations.*', 'financial-entries.*', 'priests.*', 'priest-transports.*', 'priest-indemnities.*', 'recurring-expenses.*') && ! request()->routeIs('financial-entries.report');
    $showHeader = str_ends_with($routeName, '.index') && ! in_array($routeName, ['dashboard.index', 'pdf.index'], true);
@endphp

<aside class="sidebar" id="sidebar">
<a class="brand" href="{{ route('dashboard') }}">
        <span class="cross">
            <img src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="" style="width:95%;height:95%;object-fit:contain;display:block;">
        </span>
        <span><b>OCM</b><small>{{ __('app.community_short') }}</small></span>
    </a>

    <p class="community">{{ __('app.community') }}</p>

    <nav>
        <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
            <span>{{ __('app.dashboard') }}</span>
        </a>

        @if ($can('members.view'))
            <a class="nav-item {{ request()->routeIs('members.*') ? 'active' : '' }}" href="{{ route('members.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M16 4.5a3 3 0 0 1 0 6M18 14.5c1.8.8 3 2.6 3 4.5"/></svg>
                <span>{{ __('app.members') }}</span>
            </a>
        @endif

        @if ($can('finance.view'))
            <div class="nav-group {{ $financesActive ? 'active' : '' }}">
                <a class="nav-item {{ $financesActive ? 'active' : '' }}" href="{{ route('contributions.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="2.5" y="6" width="19" height="13" rx="2.5"/><path d="M2.5 10h19M16 15h2"/></svg>
                    <span>{{ __('app.finances') }}</span>
                </a>
                <div class="subnav">
                    <a class="{{ request()->routeIs('contributions.*') ? 'active' : '' }}" href="{{ route('contributions.index') }}">{{ __('app.contributions') }}</a>
                    <a class="{{ request()->routeIs('founding-cotisations.*') ? 'active' : '' }}" href="{{ route('founding-cotisations.index') }}">{{ __('app.founding_cotisations') }}</a>
                    <a class="{{ request()->routeIs('receipts.*') ? 'active' : '' }}" href="{{ route('receipts.index') }}">{{ __('app.receipts') }}</a>
                    <a class="{{ request()->routeIs('offrandes.*') ? 'active' : '' }}" href="{{ route('offrandes.index') }}">{{ __('app.offrandes') }}</a>
                    <a class="{{ request()->routeIs('donations.*') ? 'active' : '' }}" href="{{ route('donations.index') }}">{{ __('app.donations') }}</a>
                    <a class="{{ request()->routeIs('financial-entries.index', 'financial-entries.show') ? 'active' : '' }}" href="{{ route('financial-entries.index') }}">{{ __('app.financial_entries') }}</a>
                    <a class="{{ request()->routeIs('recurring-expenses.*') ? 'active' : '' }}" href="{{ route('recurring-expenses.index') }}">Dépenses fixes</a>
                    <a class="{{ request()->routeIs('priests.*') ? 'active' : '' }}" href="{{ route('priests.index') }}">{{ __('app.priests') }}</a>
                    <a class="{{ request()->routeIs('priest-transports.*') ? 'active' : '' }}" href="{{ route('priest-transports.index') }}">{{ __('app.priest_transports') }}</a>
                    <a class="{{ request()->routeIs('priest-indemnities.*') ? 'active' : '' }}" href="{{ route('priest-indemnities.index') }}">{{ __('app.priest_indemnities') }}</a>
                </div>
            </div>
        @endif

        @if ($can('logistics.view'))
            <div class="nav-group {{ request()->routeIs('equipment.*') ? 'active' : '' }}">
                <a class="nav-item {{ request()->routeIs('equipment.*') ? 'active' : '' }}" href="{{ route('equipment.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 7.5 12 3l9 4.5v9L12 21l-9-4.5v-9Z"/><path d="M3 7.5 12 12l9-4.5M12 12v9"/></svg>
                    <span>{{ __('app.logistics') }}</span>
                </a>
                <div class="subnav">
                    <a class="{{ request()->routeIs('equipment.*') ? 'active' : '' }}" href="{{ route('equipment.index') }}">{{ __('app.equipment') }}</a>
                </div>
            </div>
        @endif

        @if ($can('events.view'))
            <a class="nav-item {{ request()->routeIs('events.*') ? 'active' : '' }}" href="{{ route('events.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
                <span>{{ __('app.events') }}</span>
            </a>
        @endif

        @if ($can('purchases.view'))
            <div class="nav-group {{ request()->routeIs('purchase-requests.*', 'exceptional-demands.*') ? 'active' : '' }}">
                <a class="nav-item {{ request()->routeIs('purchase-requests.*', 'exceptional-demands.*') ? 'active' : '' }}" href="{{ route('purchase-requests.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M3 4h2l2.2 11.2A2 2 0 0 0 9.2 17h8.6a2 2 0 0 0 2-1.6L21 8H6"/><circle cx="10" cy="20" r="1.2"/><circle cx="17.5" cy="20" r="1.2"/></svg>
                    <span>{{ __('app.nav_purchases') }}</span>
                </a>
                <div class="subnav">
                    <a class="{{ request()->routeIs('purchase-requests.*') ? 'active' : '' }}" href="{{ route('purchase-requests.index') }}">{{ __('app.purchase_requests') }}</a>
                    <a class="{{ request()->routeIs('exceptional-demands.*') ? 'active' : '' }}" href="{{ route('exceptional-demands.index') }}">{{ __('app.exceptional_demands') }}</a>
                </div>
            </div>
        @endif

        @if ($can('secretary.view'))
            <div class="nav-group {{ request()->routeIs('contribution-notices.*', 'meeting-minutes.*') ? 'active' : '' }}">
                <a class="nav-item {{ request()->routeIs('contribution-notices.*', 'meeting-minutes.*') ? 'active' : '' }}" href="{{ route('contribution-notices.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M14 3H6.5A1.5 1.5 0 0 0 5 4.5v15A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5V8l-5-5Z"/><path d="M14 3v5h5M8.5 12h7M8.5 16h5"/></svg>
                    <span>{{ __('app.nav_secretary') }}</span>
                </a>
                <div class="subnav">
                    <a class="{{ request()->routeIs('contribution-notices.*') ? 'active' : '' }}" href="{{ route('contribution-notices.index') }}">{{ __('app.contribution_notices') }}</a>
                    <a class="{{ request()->routeIs('meeting-minutes.*') ? 'active' : '' }}" href="{{ route('meeting-minutes.index') }}">{{ __('app.meeting_minutes') }}</a>
                </div>
            </div>
        @endif

        @if ($can('finance.view'))
            <a class="nav-item {{ request()->routeIs('pdf.*') ? 'active' : '' }}" href="{{ route('pdf.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M13 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M13 3v6h6M12 12v5M9.5 14.5 12 17l2.5-2.5"/></svg>
                <span>{{ __('app.documents') }}</span>
            </a>
        @endif

        @if ($can('finance.reports'))
            <a class="nav-item {{ request()->routeIs('financial-entries.report') ? 'active' : '' }}" href="{{ route('financial-entries.report') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
                <span>{{ __('app.reports') }}</span>
            </a>
        @endif
    </nav>

    <div class="side-foot">
        <div class="secure-space">
            <b>{{ __('app.secure_space') }}</b>
            <small>{{ __('app.secure_space_sub') }}</small>
        </div>
        <div class="user">
            <span class="avatar">{{ $initials }}</span>
            <div>
                <b>{{ $user?->name }}</b>
                <small>{{ $roleLabel }}</small>
            </div>
        </div>
    </div>
</aside>

<div class="overlay" id="sidebarOverlay"></div>

<div class="main">
    <header class="topbar">
        <div class="flex items-center gap-2">
            <button type="button" class="menu-btn" id="menuToggle" aria-controls="sidebar" aria-expanded="false" aria-label="Menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
            </button>
            <div class="crumb">
                <span class="crumb-root">{{ __('app.home') }}</span>
                <span class="sep">/</span>
                <b>{{ $sectionLabel }}</b>
            </div>
        </div>
        <div class="headright">
            @foreach (['fr' => 'FR', 'ti' => 'ትግርኛ'] as $locale => $label)
                <form class="lang-form" method="post" action="{{ route('locale.update', $locale) }}">
                    @csrf
                    <button type="submit" class="lang {{ app()->getLocale() === $locale ? 'active' : '' }}">{{ $label }}</button>
                </form>
            @endforeach
            <div class="topbar-user">
                <span class="topbar-user-name">{{ $user?->name }}<small>{{ $roleLabel }}</small></span>
                <span class="avatar">{{ $initials }}</span>
            </div>
            <form class="logout-form" method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="icon-btn" aria-label="{{ __('app.logout') }}" title="{{ __('app.logout') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                </button>
            </form>
        </div>
    </header>

    <main class="content">
        @if (session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        @if ($showHeader)
            <div class="welcome">
                <div>
                    <p class="eyebrow">{{ __('app.management') }}</p>
                    <h1>{{ $sectionLabel }}</h1>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="app-footer">
        <span>{{ __('app.community_short') }} · {{ now()->year }}</span>
        <span>{{ __('app.footer_note') }}</span>
    </footer>
</div>

<script src="{{ asset('js/app.js') }}" defer></script>
@stack('scripts')
</body>
</html>
