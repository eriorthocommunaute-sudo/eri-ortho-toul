@extends('layouts.app')

@section('content')
<div class="flex gap-2 mb-4" style="flex-wrap: wrap;">
    <a class="button" href="{{ route('payments.create') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
        {{ __('app.new_payment') }}
    </a>
    <a class="button outline" href="{{ route('payments.form-print') }}">{{ __('app.form_print') }}</a>
    <a class="button outline" href="{{ route('contributions.matrix') }}">{{ __('app.matrix_membership_contributions') }}</a>
    <a class="button outline" href="{{ route('contributions.index') }}">{{ __('app.back_to_contributions') }}</a>
</div>

<form class="grid" method="get" style="margin-bottom: 1rem;">
    <label>{{ __('app.year') }}
        <select name="year">
            @foreach ($years as $y)
                <option value="{{ $y }}" @selected($y == $year)>{{ $y }}</option>
            @endforeach
        </select>
    </label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<section class="panel tablepanel">
    <div class="panelhead tabletop">
        <div>
            <h2>{{ __('app.payments') }} · {{ $year }}</h2>
            <p class="muted">{{ __('app.payments_received_hint') }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('app.reference') }}</th>
                <th>{{ __('app.member') }}</th>
                <th>{{ __('app.payment_date') }}</th>
                <th>{{ __('app.months_covered') }}</th>
                <th class="text-right">{{ __('app.amount') }}</th>
                <th>{{ __('app.payment_method') }}</th>
                <th>{{ __('app.financial_reference') }}</th>
                <th>{{ __('app.status') }}</th>
                <th>{{ __('app.actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($payments as $payment)
                @php
                    $coveredMonths = ($allocations->get($payment->id) ?? collect())
                        ->map(fn ($a) => $a->memberContribution)
                        ->filter()
                        ->sortBy(fn ($c) => sprintf('%04d-%02d', $c->period_year, $c->period_month))
                        ->values();
                    $receipt = $payment->receipt_reference ? ($receiptsByReference->get($payment->receipt_reference) ?? null) : null;
                @endphp
                <tr>
                    <td><span class="ref"><a href="{{ route('payments.show', $payment) }}">{{ $payment->payment_reference }}</a></span></td>
                    <td>
                        <div class="person">
                            <span>
                                <b>{{ $payment->member->given_name }} {{ $payment->member->family_name }}</b>
                                <small>{{ $payment->member->member_code }}</small>
                            </span>
                        </div>
                    </td>
                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                    <td>
                        <span class="chips">
                            @forelse ($coveredMonths as $c)
                                <span class="status-badge status-{{ $c->status }}" title="{{ __('app.' . $c->status) }} · {{ number_format($c->allocated_amount, 2, ',', ' ') }} €">
                                    {{ \App\Services\FinancialRegisterGenerator::MONTHS_FR[(int) $c->period_month] }} {{ $c->period_year }}
                                </span>
                            @empty
                                <span class="muted">—</span>
                            @endforelse
                        </span>
                    </td>
                    <td class="text-right amount positive">{{ number_format($payment->amount, 2, ',', ' ') }} &euro;</td>
                    <td>{{ __('app.payment_method_' . $payment->payment_method) }}</td>
                    <td>
                        <span class="ref">{{ $financialRefs->get($payment->id)?->reference ?? '—' }}</span>
                    </td>
                    <td><span class="status-badge status-{{ $payment->status }}">{{ __('app.' . $payment->status) }}</span></td>
                    <td>
                        <span class="chips">
                            <a class="button" style="font-size: .78rem; padding: 5px 10px;" href="{{ route('payments.show', $payment) }}">{{ __('app.details') }}</a>
                            @if ($receipt)
                                <a class="button outline" style="font-size: .78rem; padding: 5px 10px;" target="_blank" href="{{ route('receipts.print', $receipt) }}">{{ __('app.print') }}</a>
                            @endif
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="empty">{{ __('app.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="tablefoot">
        <span class="muted">{{ __('app.total_paid') }} ({{ $year }}) : <b class="amount positive">{{ number_format($totalPaid, 2, ',', ' ') }} &euro;</b></span>
        <span class="muted">{{ __('app.total_members_paid') }} : <b>{{ $membersPaid }}</b></span>
    </div>

    <div style="padding: 0 21px 14px;">{{ $payments->links() }}</div>
</section>

<section class="panel tablepanel" style="margin-top: 1.25rem;">
    <div class="panelhead tabletop">
        <div>
            <h2>{{ __('app.monthly_totals') }} · {{ $year }}</h2>
            <p class="muted">{{ __('app.monthly_totals_payment_date_hint') }}</p>
        </div>
    </div>

    <table style="max-width: 640px;">
        <thead>
            <tr>
                <th>{{ __('app.month') }}</th>
                <th class="text-right">{{ __('app.cash_total') }}</th>
                <th class="text-right">{{ __('app.bank_total') }}</th>
                <th class="text-right">{{ __('app.total') }}</th>
            </tr>
        </thead>
        <tbody>
            @for ($m = 1; $m <= 12; $m++)
                @php $t = $monthlyTotals[$m]; @endphp
                <tr>
                    <td>{{ \App\Services\FinancialRegisterGenerator::MONTHS_FR[$m] }}</td>
                    <td class="text-right amount">{{ number_format($t['cash'], 2, ',', ' ') }} &euro;</td>
                    <td class="text-right amount">{{ number_format($t['bank'], 2, ',', ' ') }} &euro;</td>
                    <td class="text-right amount {{ $t['total'] > 0 ? 'positive' : '' }}">{{ number_format($t['total'], 2, ',', ' ') }} &euro;</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <td class="amount"><b>{{ __('app.total') }}</b></td>
                <td class="text-right amount positive"><b>{{ number_format($cashTotal, 2, ',', ' ') }} &euro;</b></td>
                <td class="text-right amount positive"><b>{{ number_format($bankTotal, 2, ',', ' ') }} &euro;</b></td>
                <td class="text-right amount positive"><b>{{ number_format($totalPaid, 2, ',', ' ') }} &euro;</b></td>
            </tr>
        </tfoot>
    </table>
</section>
@endsection
