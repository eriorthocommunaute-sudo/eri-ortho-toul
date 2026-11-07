@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('priest-transports.create') }}">{{ __('app.new_transport') }}</a>
</div>

<form class="grid" method="get">
    <label>{{ __('app.year') }}<select name="year"><option value="">{{ __('app.all') }}</option>@foreach($years as $y)<option value="{{ $y }}" @selected(request('year')==$y)>{{ $y }}</option>@endforeach</select></label>
    <label>{{ __('app.month') }}<select name="month"><option value="">{{ __('app.all') }}</option>@foreach($months as $m)<option value="{{ $m }}" @selected(request('month')==$m)>{{ app()->getLocale() === 'ti' ? \App\Services\FinancialRegisterGenerator::MONTHS_TI[$m] : \App\Services\FinancialRegisterGenerator::MONTHS_FR[$m] }}</option>@endforeach</select></label>
    <label>{{ __('app.priest') }}<select name="priest_id"><option value="">{{ __('app.all') }}</option>@foreach($priests as $p)<option value="{{ $p->id }}" @selected(request('priest_id')==$p->id)>{{ $p->name }}</option>@endforeach</select></label>
    <label>{{ __('app.transport_type') }}<select name="transport_type"><option value="">{{ __('app.all') }}</option>@foreach($types as $t)<option value="{{ $t }}" @selected(request('transport_type')==$t)>{{ __('app.'.$t) }}</option>@endforeach</select></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem; margin-bottom: 1rem;">
    <strong>{{ __('app.total_expense') }}:</strong> {{ number_format($totalAmount, 2) }} €
</div>

<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.priest') }}</th>
            <th>{{ __('app.transport_date') }}</th>
            <th>{{ __('app.origin') }}</th>
            <th>{{ __('app.destination') }}</th>
            <th>{{ __('app.occasion') }}</th>
            <th>{{ __('app.amount') }}</th>
            <th>{{ __('app.transport_type') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($transports as $trp)
        <tr>
            <td><a href="{{ route('priest-transports.show', $trp) }}">{{ $trp->reference }}</a></td>
            <td>{{ $trp->priest?->name ?? '' }}</td>
            <td>{{ $trp->transport_date->format('d/m/Y') }}</td>
            <td>{{ $trp->origin }}</td>
            <td>{{ $trp->destination }}</td>
            <td>{{ $trp->occasion ?? '' }}</td>
            <td>{{ number_format($trp->amount, 2) }} €</td>
            <td>{{ __('app.'.$trp->transport_type) }}</td>
        </tr>
        @empty
        <tr><td colspan="8">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $transports->links() }}</p>
@endsection
