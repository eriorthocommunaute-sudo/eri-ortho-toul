@extends('layouts.app')
@section('content')
@php
    $balance = $summaryIncome - $summaryExpense;
    $activeFilters = collect([
        request('type') ? ['label' => __('app.'.request('type')), 'query' => 'type'] : null,
        request('category') ? ['label' => __('app.'.request('category')), 'query' => 'category'] : null,
        request('date_from') ? ['label' => __('app.date_from').' : '.\Carbon\Carbon::parse(request('date_from'))->format('d/m/Y'), 'query' => 'date_from'] : null,
        request('date_to') ? ['label' => __('app.date_to').' : '.\Carbon\Carbon::parse(request('date_to'))->format('d/m/Y'), 'query' => 'date_to'] : null,
    ])->filter();
@endphp

<div class="pagebar">
    <div>
        <p class="muted">{{ __('app.financial_register_sub') }}</p>

        @if ($activeFilters->isNotEmpty())
            <div class="chips">
                @foreach ($activeFilters as $filter)
                    <span class="chip">
                        {{ $filter['label'] }}
                        <a href="{{ route('financial-entries.index', array_diff(request()->query(), [$filter['query']])) }}" title="{{ __('app.filter') }}">&times;</a>
                    </span>
                @endforeach
                <a class="link" href="{{ route('financial-entries.index') }}">{{ __('app.reset_filters') }}</a>
            </div>
        @endif
    </div>

    <div class="actions">
        @if (auth()->user()?->hasPermission('finance.reports'))
            <a class="button outline" href="{{ route('financial-entries.report') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
                {{ __('app.financial_report') }}
            </a>
        @endif
        @if (auth()->user()?->hasPermission('finance.view'))
            <a class="button" href="{{ route('financial-register.index') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M13 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M13 3v6h6M12 12v5M9.5 14.5 12 17l2.5-2.5"/></svg>
                {{ __('app.financial_register') }}
            </a>
        @endif
    </div>
</div>

<section class="summary">
    <div>
        <span>{{ __('app.total_income') }}</span>
        <b class="positive">+ {{ number_format($summaryIncome, 2, ',', ' ') }} &euro;</b>
        <small>{{ __('app.receipts_of_period') }}</small>
    </div>
    <div>
        <span>{{ __('app.total_expense') }}</span>
        <b class="negative">- {{ number_format($summaryExpense, 2, ',', ' ') }} &euro;</b>
        <small>{{ __('app.expenses_of_period') }}</small>
    </div>
    <div>
        <span>{{ __('app.net_balance') }}</span>
        <b>{{ $balance >= 0 ? '+' : '-' }} {{ number_format(abs($balance), 2, ',', ' ') }} &euro;</b>
        <small>{{ __('app.income_minus_expense') }}</small>
    </div>
    <div>
        <span>{{ __('app.operations_count') }}</span>
        <b>{{ number_format($summaryCount, 0, ',', ' ') }}</b>
        <small>{{ __('app.results_total', ['count' => $entries->total()]) }}</small>
    </div>
</section>

<section class="filterbar">
    <form method="get" action="{{ route('financial-entries.index') }}">
        <label>
            {{ __('app.type') }}
            <select name="type" class="tool-select">
                <option value="">{{ __('app.all') }}</option>
                <option value="income" @selected(request('type') == 'income')>{{ __('app.income') }}</option>
                <option value="expense" @selected(request('type') == 'expense')>{{ __('app.expense') }}</option>
            </select>
        </label>

        <label>
            {{ __('app.category') }}
            <select name="category" class="tool-select">
                <option value="">{{ __('app.all') }}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') == $category)>{{ __('app.'.$category) }}</option>
                @endforeach
            </select>
        </label>

        <label>
            {{ __('app.date_from') }}
            <input type="date" name="date_from" value="{{ request('date_from') }}">
        </label>

        <label>
            {{ __('app.date_to') }}
            <input type="date" name="date_to" value="{{ request('date_to') }}">
        </label>

        <div class="filter-actions">
            <button type="submit" class="button">{{ __('app.filter') }}</button>
            @if ($activeFilters->isNotEmpty())
                <a class="button outline" href="{{ route('financial-entries.index') }}">{{ __('app.reset_filters') }}</a>
            @endif
        </div>
    </form>
</section>

<section class="panel tablepanel">
    <div class="panelhead tabletop">
        <div>
            <h2>{{ __('app.operations_count') }}</h2>
            <p class="muted">{{ __('app.operations_sub') }}</p>
        </div>
    </div>

    <div class="scroll">
        <table class="table-grid">
            <thead>
                <tr>
                    <th>{{ __('app.reference') }}</th>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.type') }}</th>
                    <th>{{ __('app.category') }}</th>
                    <th>{{ __('app.description') }}</th>
                    <th class="text-right">{{ __('app.amount') }}</th>
                    <th>{{ __('app.source') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($entries as $entry)
                    <tr>
                        <td>
    <span class="ref">{{ $entry->reference }}</span>
</td>
                        <td>{{ $entry->entry_date->format('d/m/Y') }}</td>
                        <td><span class="status-badge status-{{ $entry->type }}">{{ __('app.'.$entry->type) }}</span></td>
                        <td>{{ __('app.'.$entry->category) }}</td>
                        <td>
                            <div class="desc">{{ $entry->description ?: __('app.no_data') }}</div>
                            @if ($entry->creator)
                                <div class="muted">{{ $entry->creator->name }}</div>
                            @endif
                        </td>
                        <td class="text-right">
                            <span class="amount {{ $entry->is_income ? 'positive' : 'negative' }}">{{ $entry->is_income ? '+' : '-' }}{{ number_format($entry->amount, 2, ',', ' ') }} &euro;</span>
                        </td>
                        <td><span class="muted">{{ $entry->source_type }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <span class="empty-ico">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9l-6-6Z"/><path d="M14 3v6h6"/><path d="M9 13h6M9 17h4"/></svg>
                                </span>
                                <b>{{ __('app.no_financial_history') }}</b>
                                <small>{{ __('app.financial_register_hint') }}</small>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($entries->hasPages())
        <div class="tablefoot">
            <span>{{ __('app.results_count', ['count' => $entries->total()]) }}</span>
            <div class="pagination">
                {{ $entries->links() }}
            </div>
        </div>
    @endif
</section>
@endsection
