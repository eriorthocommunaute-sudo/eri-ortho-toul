@extends('layouts.app')
@section('content')
<div class="flex flex-wrap gap-2 mb-4">
    <a class="button" href="{{ route('payments.create') }}">Nouvelle cotisation</a>
    <a class="button" href="{{ route('founding-cotisations.index') }}">Cotisations fondatrices 200€</a>
    <a class="button" href="{{ route('donations.create') }}">Enregistrer un don</a>
    <a class="button" href="{{ route('offrandes.create') }}">Enregistrer une offrande</a>
    <a class="button" href="{{ route('receipts.blank.print') }}" target="_blank">Imprimer un reçu vierge</a>
</div>

<form class="grid" method="get">
    <label>Type de recette
        <select name="receipt_type">
            <option value="">Tous</option>
            @foreach(['cotisation' => 'Cotisation', 'cotisation_fondatrice' => 'Cotisation fondatrice (€200)', 'don' => 'Don', 'offrande' => 'Offrande'] as $key => $label)
                <option value="{{ $key }}" @selected(request('receipt_type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </label>
    <label>{{ __('app.member') }}
        <select name="member_id"><option value="">{{ __('app.all') }}</option>
            @foreach($members as $m)
                <option value="{{ $m->id }}" @selected(request('member_id') == $m->id)>{{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}</option>
            @endforeach
        </select>
    </label>
    <label>{{ __('app.status') }}
        <select name="status"><option value="">{{ __('app.all') }}</option>
            @foreach(['draft', 'confirmed', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('app.'.$status) }}</option>
            @endforeach
        </select>
    </label>
    <label>{{ __('app.year') }}
        <select name="year"><option value="">{{ __('app.all') }}</option>
            @foreach($years as $year)
                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
            @endforeach
        </select>
    </label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead><tr>
        <th>Reçu</th><th>Type</th><th>Payeur / Donateur</th><th>Date</th><th>{{ __('app.amount') }}</th>
        <th>{{ __('app.payment_method') }}</th><th>{{ __('app.financial_reference') }}</th><th>{{ __('app.status') }}</th><th>{{ __('app.actions') }}</th>
    </tr></thead>
    <tbody>
    @forelse($receipts as $receipt)
        <tr>
            <td><a href="{{ route('receipts.show', $receipt) }}">{{ $receipt->reference }}</a></td>
            <td>{{ ['cotisation_fondatrice' => 'Cotisation fondatrice (€200)', 'cotisation' => 'Cotisation', 'don' => 'Don', 'offrande' => 'Offrande'][$receipt->receipt_type ?? 'cotisation'] ?? ucfirst($receipt->receipt_type ?? 'cotisation') }}</td>
            <td>
                @if($receipt->member)
                    {{ $receipt->member->member_code }} - {{ $receipt->member->given_name }} {{ $receipt->member->family_name }}
                @else
                    {{ $receipt->payer_name ?: 'Anonyme' }}
                @endif
            </td>
            <td>{{ $receipt->payment_date->format('d/m/Y') }}</td>
            <td>{{ number_format($receipt->amount, 2, ',', ' ') }} €</td>
            <td>{{ __('app.'.$receipt->payment_method) }}</td>
            <td>{{ $receipt->financial_reference }}</td>
            <td><span class="status-badge status-{{ $receipt->status }}">{{ __('app.'.$receipt->status) }}</span></td>
            <td><a class="button" href="{{ route('receipts.print', $receipt) }}" target="_blank">{{ __('app.print') }}</a></td>
        </tr>
    @empty
        <tr><td colspan="9">{{ __('app.no_data') }}</td></tr>
    @endforelse
    </tbody>
</table>
<p>{{ $receipts->links() }}</p>
@endsection
