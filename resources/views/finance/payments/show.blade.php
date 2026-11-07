@extends('layouts.app')

@section('content')
<div class="flex gap-2 mb-4" style="flex-wrap: wrap;">
    <a class="button outline" href="{{ route('payments.index') }}">{{ __('app.back_to_list') }}</a>
    @if ($receipt)
        <a class="button" target="_blank" href="{{ route('receipts.print', $receipt) }}">{{ __('app.print_receipt') }}</a>
    @endif
</div>

<section class="panel">
    <div class="panelhead">
        <div>
            <h2>{{ $payment->payment_reference }}</h2>
            <p class="muted">{{ __('app.payment_sub') }}</p>
        </div>
        <span class="status-badge status-{{ $payment->status }}">{{ __('app.' . $payment->status) }}</span>
    </div>

    <div style="padding: 21px; display: grid; gap: 18px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div>
            <small class="muted">{{ __('app.member') }}</small>
            <div class="person" style="margin-top: 4px;">
                <span>
                    <b>{{ $payment->member->given_name }} {{ $payment->member->family_name }}</b>
                    <small>{{ $payment->member->member_code }}</small>
                </span>
            </div>
        </div>
        <div>
            <small class="muted">{{ __('app.payment_date') }}</small>
            <div class="amount" style="margin-top: 4px;">{{ $payment->payment_date->format('d/m/Y') }}</div>
        </div>
        <div>
            <small class="muted">{{ __('app.amount') }}</small>
            <div class="amount positive" style="margin-top: 4px; font-size: 1.25rem;">{{ number_format($payment->amount, 2, ',', ' ') }} &euro;</div>
        </div>
        <div>
            <small class="muted">{{ __('app.payment_method') }}</small>
            <div class="amount" style="margin-top: 4px;">{{ __('app.payment_method_' . $payment->payment_method) }}</div>
        </div>
        <div>
            <small class="muted">{{ __('app.financial_reference') }}</small>
            <div class="ref" style="margin-top: 4px;">{{ $financialEntry?->reference ?? '—' }}</div>
        </div>
        <div>
            <small class="muted">{{ __('app.receipt') }}</small>
            <div class="ref" style="margin-top: 4px;">{{ $payment->receipt_reference }}</div>
        </div>
    </div>

    @if ($payment->notes)
        <div style="padding: 0 21px 21px;">
            <small class="muted">{{ __('app.notes') }}</small>
            <p style="margin: 4px 0 0;">{{ $payment->notes }}</p>
        </div>
    @endif
    @if ($payment->status === 'confirmed' && auth()->user()?->hasPermission('finance.record'))
        <div style="padding: 0 21px 21px;">
            <form method="post" action="{{ route('payments.cancel', $payment) }}" onsubmit="return confirm('Confirmer l’annulation de ce paiement ? Une écriture de contrepassation sera créée.');">
                @csrf
                <label for="cancellation_reason" class="muted">Motif obligatoire de l’annulation</label>
                <textarea id="cancellation_reason" name="cancellation_reason" required minlength="5" maxlength="1000" rows="2" style="width:100%; margin:6px 0 10px;"></textarea>
                <button type="submit" class="button danger">Annuler le paiement</button>
            </form>
        </div>
    @elseif ($payment->status === 'cancelled')
        <div style="padding: 0 21px 21px;">
            <strong>Paiement annulé</strong>
            <p class="muted">{{ $payment->cancelled_at?->format('d/m/Y H:i') }} · {{ $payment->cancellation_reason }}</p>
        </div>
    @endif
</section>

<section class="panel tablepanel" style="margin-top: 1.25rem;">
    <div class="panelhead tabletop">
        <div>
            <h2>{{ __('app.covered_months') }}</h2>
            <p class="muted">{{ __('app.covered_months_hint') }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('app.period') }}</th>
                <th class="text-right">{{ __('app.expected') }}</th>
                <th class="text-right">{{ __('app.allocated') }}</th>
                <th>{{ __('app.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($allocations->sortBy(fn ($a) => sprintf('%04d-%02d', $a->memberContribution->period_year, $a->memberContribution->period_month)) as $allocation)
                @php $c = $allocation->memberContribution; @endphp
                <tr>
                    <td>{{ \App\Services\FinancialRegisterGenerator::MONTHS_FR[(int) $c->period_month] }} {{ $c->period_year }}</td>
                    <td class="text-right amount">{{ number_format($c->expected_amount, 2, ',', ' ') }} &euro;</td>
                    <td class="text-right amount positive">{{ number_format($allocation->allocated_amount, 2, ',', ' ') }} &euro;</td>
                    <td><span class="status-badge status-{{ $c->status }}">{{ __('app.' . $c->status) }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty">{{ __('app.no_data') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($financialEntry)
        <div class="tablefoot">
            <span class="muted">{{ __('app.financial_entry') }} : <span class="ref">{{ $financialEntry->reference }}</span></span>
            <span class="muted">{{ $financialEntry->entry_date->format('d/m/Y') }} · {{ number_format($financialEntry->amount, 2, ',', ' ') }} &euro;</span>
        </div>
    @endif
</section>
@endsection
