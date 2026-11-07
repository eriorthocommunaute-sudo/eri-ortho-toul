@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('priest-indemnities.create') }}">{{ __('app.new_indemnity') }}</a>
</div>

<form class="grid" method="get">
    <label>{{ __('app.year') }}<select name="year"><option value="">{{ __('app.all') }}</option>@foreach($years as $y)<option value="{{ $y }}" @selected(request('year')==$y)>{{ $y }}</option>@endforeach</select></label>
    <label>{{ __('app.month') }}<select name="month"><option value="">{{ __('app.all') }}</option>@foreach($months as $m)<option value="{{ $m }}" @selected(request('month')==$m)>{{ app()->getLocale() === 'ti' ? \App\Services\FinancialRegisterGenerator::MONTHS_TI[$m] : \App\Services\FinancialRegisterGenerator::MONTHS_FR[$m] }}</option>@endforeach</select></label>
    <label>{{ __('app.priest') }}<select name="priest_id"><option value="">{{ __('app.all') }}</option>@foreach($priests as $p)<option value="{{ $p->id }}" @selected(request('priest_id')==$p->id)>{{ $p->name }}</option>@endforeach</select></label>
    <label>{{ __('app.occasion') }}<input name="occasion" value="{{ request('occasion') }}"></label>
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
            <th>{{ __('app.service_date') }}</th>
            <th>{{ __('app.occasion') }}</th>
            <th>{{ __('app.location') }}</th>
            <th>{{ __('app.amount') }}</th>
            <th>{{ __('app.financial_reference') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($indemnities as $pre)
        <tr>
            <td><a href="{{ route('priest-indemnities.show', $pre) }}">{{ $pre->reference }}</a></td>
            <td>{{ $pre->priest?->name ?? '' }}</td>
            <td>{{ $pre->service_date->format('d/m/Y') }}</td>
            <td>{{ $pre->occasion }}</td>
            <td>{{ $pre->location ?? '' }}</td>
            <td>{{ number_format($pre->amount, 2) }} €</td>
            <td>{{ $pre->financial_reference }}</td>
        </tr>
        @empty
        <tr><td colspan="7">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $indemnities->links() }}</p>
@endsection
