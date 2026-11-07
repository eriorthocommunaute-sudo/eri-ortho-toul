@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('offrandes.create') }}">{{ __('app.record_offrande') }}</a>
</div>

<form class="grid" method="get">
    <label>{{ __('app.member') }}<select name="member_id"><option value="">{{ __('app.all') }}</option>@foreach($members as $m)<option value="{{ $m->id }}" @selected(request('member_id') == $m->id)>{{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}</option>@endforeach</select></label>
    <label>{{ __('app.year') }}<select name="year"><option value="">{{ __('app.all') }}</option>@foreach($years as $y)<option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>@endforeach</select></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.offering_date') }}</th>
            <th>{{ __('app.donor') }}</th>
            <th>{{ __('app.occasion') }}</th>
            <th>{{ __('app.amount') }}</th>
            <th>{{ __('app.financial_reference') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($offrandes as $offrande)
        <tr>
            <td><a href="{{ route('offrandes.show', $offrande) }}">{{ $offrande->reference }}</a></td>
            <td>{{ $offrande->offering_date->format('d/m/Y') }}</td>
            <td>{{ $offrande->member ? $offrande->member->member_code . ' - ' . $offrande->member->given_name . ' ' . $offrande->member->family_name : $offrande->donor_name }}</td>
            <td>{{ $offrande->occasion ?? '' }}</td>
            <td>{{ number_format($offrande->amount, 2) }} €</td>
            <td>{{ $offrande->financial_reference }}</td>
        </tr>
        @empty
        <tr><td colspan="6">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $offrandes->links() }}</p>
@endsection