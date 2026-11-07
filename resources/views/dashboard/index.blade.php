@extends('layouts.app')
@section('content')
@php
    $user = auth()->user();
    $roles = $user?->roles ?? collect();
    $permissions = $roles->flatMap(fn ($role) => $role->permissions())->unique();
    $can = fn (string $permission) => $permissions->contains($permission);
    $monthNumber = (int) now()->format('n');
    $monthNames = app()->getLocale() === 'ti'
        ? \App\Services\FinancialRegisterGenerator::MONTHS_TI
        : \App\Services\FinancialRegisterGenerator::MONTHS_FR;
    $monthLabel = $monthNames[$monthNumber] . ' ' . now()->format('Y');
@endphp

<section class="hero">
    <div class="hero-main">
        <p class="eyebrow">{{ __('app.overview_eyebrow') }}</p>
        <h1>{{ __('app.hello_name', ['name' => $user?->name]) }}</h1>
        <p class="hero-sub">{{ __('app.dashboard_welcome') }}</p>

        <div class="hero-meta">
            <div>
                <span>{{ __('app.current_period') }}</span>
                <b>{{ $monthLabel }}</b>
            </div>
            <div>
                <span>{{ __('app.members_active') }}</span>
                <b>{{ number_format($activeMembers, 0, ',', ' ') }}</b>
            </div>
            <div>
                <span>{{ __('app.net_balance') }}</span>
                <b>{{ $totalIncome - $allTimeExpense >= 0 ? '+' : '-' }} {{ number_format(abs($totalIncome - $allTimeExpense), 2, ',', ' ') }} &euro;</b>
            </div>
        </div>
    </div>

    <div class="actions">
        @if ($can('finance.record'))
            <a class="button" href="{{ route('payments.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                {{ __('app.new_operation') }}
            </a>
        @endif
        @if ($can('members.create'))
            <a class="button outline" href="{{ route('members.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6M18 8v6M15 11h6"/></svg>
                {{ __('app.add_member') }}
            </a>
        @endif
    </div>
</section>

@if ($pendingApprovals > 0)
    <div class="pending-banner">
        <span class="pending-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
        </span>
        <span>{{ __('app.pending_banner', ['count' => $pendingApprovals]) }}</span>
        @if ($can('purchases.view'))
            <a href="{{ route('purchase-requests.index') }}">{{ __('app.see_requests') }} &rarr;</a>
        @endif
    </div>
@endif

<section class="stats kpi">
    <article>
        <div class="kpi-head">
            <span class="kpi-ico teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6M18 8v6M15 11h6"/></svg>
            </span>
            <span class="kpi-label">{{ __('app.members_active') }}</span>
        </div>
        <strong>{{ number_format($activeMembers, 0, ',', ' ') }}</strong>
        <small>{{ __('app.members_registered', ['count' => number_format($totalMembers, 0, ',', ' ')]) }}</small>
    </article>

    <article class="kpi-green">
        <div class="kpi-head">
            <span class="kpi-ico green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="2.5" y="6" width="19" height="13" rx="2.5"/><path d="M2.5 10h19M16 15h2"/></svg>
            </span>
            <span class="kpi-label">{{ __('app.contributions_month') }}</span>
        </div>
        <strong>{{ number_format($contributionReceived, 2, ',', ' ') }} <small>&euro;</small></strong>
        <div class="progress"><i style="width: {{ min(100, max(0, $contributionProgress)) }}%"></i></div>
        <small>{{ $contributionProgress }}% {{ __('app.of_target') }} ({{ number_format($contributionExpected, 2, ',', ' ') }} &euro;)</small>
    </article>

    <article class="kpi-orange">
        <div class="kpi-head">
            <span class="kpi-ico orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 3v18M16.5 7.5C16.5 5.6 14.4 4 12 4S7.5 5.6 7.5 7.5 9.6 11 12 11s4.5 1.6 4.5 3.5S14.4 18 12 18s-4.5-1.6-4.5-3.5"/></svg>
            </span>
            <span class="kpi-label">{{ __('app.expenses_month') }}</span>
        </div>
        <strong>{{ number_format($monthlyExpense, 2, ',', ' ') }} <small>&euro;</small></strong>
        <small>{{ __('app.expenses_recorded') }}</small>
    </article>

    <article class="kpi-blue">
        <div class="kpi-head">
            <span class="kpi-ico blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
            </span>
            <span class="kpi-label">{{ __('app.upcoming_events') }}</span>
        </div>
        <strong>{{ number_format($upcomingCount, 0, ',', ' ') }}</strong>
        <small>{{ __('app.next_30_days') }}</small>
    </article>
</section>

<div class="columns">
    <section class="panel">
        <div class="panelhead">
            <div>
                <h2>{{ __('app.financial_overview') }}</h2>
                <p class="muted">{{ __('app.financial_overview_sub') }}</p>
            </div>
            @if ($can('finance.reports'))
                <a class="link" href="{{ route('financial-entries.report') }}">{{ __('app.view_report') }} &rarr;</a>
            @endif
        </div>

        <div class="totals">
            <div>{{ __('app.total_income') }}<b class="income">+ {{ number_format($totalIncome, 2, ',', ' ') }} &euro;</b></div>
            <div>{{ __('app.founding_cotisations') }}<b class="income">+ {{ number_format($foundingCotisationsIncome, 2, ',', ' ') }} &euro;</b></div>
            <div>{{ __('app.total_expense') }}<b class="expense">- {{ number_format($allTimeExpense, 2, ',', ' ') }} &euro;</b></div>
            <div>{{ __('app.net_balance') }}<b class="balance">{{ $totalIncome - $allTimeExpense >= 0 ? '+' : '-' }} {{ number_format(abs($totalIncome - $allTimeExpense), 2, ',', ' ') }} &euro;</b></div>
        </div>

        <div class="chart">
            <div class="chart-yaxis">
                <span>{{ number_format($chartMax, 0, ',', ' ') }}</span>
                <span>{{ number_format($chartMax / 2, 0, ',', ' ') }}</span>
                <span>0</span>
            </div>
            <div class="chart-body">
                <div class="chart-grid"><i></i><i></i><i></i></div>
                <div class="chart-bars">
                    @foreach ($chartSeries as $index => $month)
                        @php
                            $incomeHeight = $month['income'] > 0 ? max(3, round(($month['income'] / $chartMax) * 118)) : 2;
                            $expenseHeight = $month['expense'] > 0 ? max(3, round(($month['expense'] / $chartMax) * 118)) : 2;
                            $isCurrent = $index === $chartSeries->count() - 1;
                        @endphp
                        <div class="{{ $isCurrent ? 'now' : '' }}">
                            <i style="height: {{ $incomeHeight }}px" title="{{ __('app.income') }}: {{ number_format($month['income'], 2, ',', ' ') }} &euro;"></i>
                            <em style="height: {{ $expenseHeight }}px" title="{{ __('app.expense') }}: {{ number_format($month['expense'], 2, ',', ' ') }} &euro;"></em>
                            <small>{{ $month['label'] }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="chart-legend">
            <span>{{ __('app.income') }}</span>
            <span class="expense">{{ __('app.expense') }}</span>
        </div>
    </section>

    <section class="panel">
        <div class="panelhead">
            <div>
                <h2>{{ __('app.quick_access') }}</h2>
                <p class="muted">{{ __('app.quick_access_sub') }}</p>
            </div>
        </div>

        <div class="shortcuts">
            @if ($can('members.create'))
                <a class="shortcut" href="{{ route('members.create') }}">
                    <span class="qicon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6M18 8v6M15 11h6"/></svg>
                    </span>
                    <span><b>{{ __('app.add_member') }}</b><small>{{ __('app.add_member_sub') }}</small></span>
                    <span class="chev">&rsaquo;</span>
                </a>
            @endif

            @if ($can('finance.record'))
                <a class="shortcut" href="{{ route('payments.create') }}">
                    <span class="qicon bluebg">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2.5" y="6" width="19" height="13" rx="2.5"/><path d="M2.5 10h19M16 15h2"/></svg>
                    </span>
                    <span><b>{{ __('app.record_payment') }}</b><small>{{ __('app.payment_sub') }}</small></span>
                    <span class="chev">&rsaquo;</span>
                </a>
            @endif

            @if ($can('finance.view'))
                <a class="shortcut" href="{{ route('financial-register.index') }}">
                    <span class="qicon purplebg">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M13 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M13 3v6h6M12 12v5M9.5 14.5 12 17l2.5-2.5"/></svg>
                    </span>
                    <span><b>{{ __('app.financial_register') }}</b><small>{{ __('app.create_doc_sub') }}</small></span>
                    <span class="chev">&rsaquo;</span>
                </a>
            @endif

            @if ($can('events.manage'))
                <a class="shortcut" href="{{ route('events.create') }}">
                    <span class="qicon orangebg">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3 10h18M12 14v4M10 16h4"/></svg>
                    </span>
                    <span><b>{{ __('app.plan_event') }}</b><small>{{ __('app.plan_event_sub') }}</small></span>
                    <span class="chev">&rsaquo;</span>
                </a>
            @endif
        </div>
    </section>
</div>

<section class="panel tablepanel">
    <div class="panelhead tabletop">
        <div>
            <h2>{{ __('app.recent_activity') }}</h2>
            <p class="muted">{{ __('app.recent_activity_sub') }}</p>
        </div>
        <div class="tools">
            <input id="activitySearch" type="search" class="tool-input" placeholder="{{ __('app.search') }}">
            <button type="button" id="exportActivity" class="outline">{{ __('app.export_csv') }}</button>
        </div>
    </div>

    <div class="scroll">
        <table id="activityTable">
            <thead>
                <tr>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.description') }}</th>
                    <th>{{ __('app.category') }}</th>
                    <th>{{ __('app.type') }}</th>
                    <th class="text-right">{{ __('app.amount') }}</th>
                </tr>
            </thead>
            <tbody id="activityBody">
                @forelse ($recentEntries as $entry)
                    <tr data-row>
                        <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                        <td>
                            <div class="desc">{{ $entry->description ?: __('app.no_data') }}</div>
                            <div class="muted">{{ $entry->reference }}</div>
                        </td>
                        <td>{{ __('app.'.$entry->category) }}</td>
                        <td><span class="status-badge status-{{ $entry->type }}">{{ __('app.'.$entry->type) }}</span></td>
                        <td class="text-right">
                            <span class="amount {{ $entry->is_income ? 'positive' : 'negative' }}">{{ $entry->is_income ? '+' : '-' }}{{ number_format($entry->amount, 2, ',', ' ') }} &euro;</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty">{{ __('app.no_data') }}</td></tr>
                @endforelse
                @if ($recentEntries->isNotEmpty())
                    <tr id="activityEmpty" style="display: none"><td colspan="5" class="empty">{{ __('app.no_data') }}</td></tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="tablefoot">
        <span id="activityCount" data-label="{{ __('app.operations') }}">{{ $recentEntries->count() }} {{ __('app.operations') }}</span>
        <a class="link" href="{{ route('financial-entries.index') }}">{{ __('app.view_all') }}</a>
    </div>
</section>
@endsection

@push('scripts')
    <script src="{{ asset('js/dashboard.js') }}" defer></script>
@endpush
