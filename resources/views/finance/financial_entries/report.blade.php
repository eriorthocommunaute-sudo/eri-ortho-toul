@extends('layouts.app')
@section('content')
<div class="welcome">
    <div>
        <p class="eyebrow">{{ __('app.management') }}</p>
        <h1>{{ __('app.financial_report') }}</h1>
        <p class="muted">{{ $dateFrom->format('d/m/Y') }} &ndash; {{ $dateTo->format('d/m/Y') }}</p>
    </div>
</div>

<section class="panel" style="margin-bottom: 19px; padding-bottom: 4px;">
    <form class="grid" method="get">
        <label>{{ __('app.date_from') }}<input name="date_from" type="date" value="{{ $dateFrom->format('Y-m-d') }}"></label>
        <label>{{ __('app.date_to') }}<input name="date_to" type="date" value="{{ $dateTo->format('Y-m-d') }}"></label>
        <p><button type="submit">{{ __('app.generate') }}</button></p>
    </form>
</section>

<section class="stats stats-3">
    <article>
        {{ __('app.total_income') }}
        <strong class="positive">+ {{ number_format($totalIncome, 2, ',', ' ') }} <small>&euro;</small></strong>
        <small>{{ __('app.income_by_category') }}</small>
    </article>
    <article>
        {{ __('app.total_expense') }}
        <strong class="negative">- {{ number_format($totalExpense, 2, ',', ' ') }} <small>&euro;</small></strong>
        <small>{{ __('app.expense_by_category') }}</small>
    </article>
    <article>
        {{ __('app.balance') }}
        <strong class="{{ $totalIncome >= $totalExpense ? 'positive' : 'negative' }}">{{ $totalIncome >= $totalExpense ? '+' : '-' }} {{ number_format(abs($totalIncome - $totalExpense), 2, ',', ' ') }} <small>&euro;</small></strong>
        <small>{{ __('app.net_balance') }}</small>
    </article>
</section>

<div class="columns">
    <section class="panel">
        <div class="panelhead">
            <div>
                <h2>{{ __('app.income_by_category') }}</h2>
                <p class="muted">{{ __('app.total_income') }}</p>
            </div>
        </div>

        @if ($incomeByCategory->isEmpty())
            <p class="muted">{{ __('app.no_data') }}</p>
        @else
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('app.category') }}</th>
                            <th class="text-right">{{ __('app.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($incomeByCategory as $category => $amount)
                            <tr>
                                <td>{{ __('app.'.$category) }}</td>
                                <td class="text-right"><span class="amount positive">+ {{ number_format($amount, 2, ',', ' ') }} &euro;</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="panel">
        <div class="panelhead">
            <div>
                <h2>{{ __('app.expense_by_category') }}</h2>
                <p class="muted">{{ __('app.total_expense') }}</p>
            </div>
        </div>

        @if ($expenseByCategory->isEmpty())
            <p class="muted">{{ __('app.no_data') }}</p>
        @else
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('app.category') }}</th>
                            <th class="text-right">{{ __('app.amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($expenseByCategory as $category => $amount)
                            <tr>
                                <td>{{ __('app.'.$category) }}</td>
                                <td class="text-right"><span class="amount negative">- {{ number_format($amount, 2, ',', ' ') }} &euro;</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</div>

<section class="panel tablepanel">
    <div class="panelhead">
        <div>
            <h2>{{ __('app.monthly_summary') }}</h2>
            <p class="muted">{{ __('app.period') }}</p>
        </div>
    </div>

    <div class="scroll">
        <table>
            <thead>
                <tr>
                    <th>{{ __('app.month') }}</th>
                    <th class="text-right">{{ __('app.income') }}</th>
                    <th class="text-right">{{ __('app.expense') }}</th>
                    <th class="text-right">{{ __('app.balance') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($monthly as $row)
                    @php $balance = $row->income - $row->expense; @endphp
                    <tr>
                        <td><span class="desc">{{ __('app.months_short.'.($row->month - 1)) }} {{ $row->year }}</span></td>
                        <td class="text-right"><span class="amount positive">+ {{ number_format($row->income, 2, ',', ' ') }} &euro;</span></td>
                        <td class="text-right"><span class="amount negative">- {{ number_format($row->expense, 2, ',', ' ') }} &euro;</span></td>
                        <td class="text-right"><span class="amount {{ $balance >= 0 ? 'positive' : 'negative' }}">{{ $balance >= 0 ? '+' : '-' }} {{ number_format(abs($balance), 2, ',', ' ') }} &euro;</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">{{ __('app.no_data') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
