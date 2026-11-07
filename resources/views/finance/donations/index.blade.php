@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('donations.create') }}">{{ __('app.record_donation') }}</a>
</div>

<form class="grid" method="get">
    <label>{{ __('app.donor_name') }}<input name="donor_name" value="{{ request('donor_name') }}"></label>
    <label>{{ __('app.year') }}<select name="year"><option value="">{{ __('app.all') }}</option>@foreach($years as $y)<option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>@endforeach</select></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.donation_date') }}</th>
            <th>{{ __('app.donor_name') }}</th>
            <th>{{ __('app.donor_type') }}</th>
            <th>{{ __('app.purpose') }}</th>
            <th>{{ __('app.amount') }}</th>
            <th>{{ __('app.financial_reference') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($donations as $donation)
        <tr>
            <td><a href="{{ route('donations.show', $donation) }}">{{ $donation->reference }}</a></td>
            <td>{{ $donation->donation_date->format('d/m/Y') }}</td>
            <td>{{ $donation->donor_name }}</td>
            <td>{{ $donation->donor_type ?? '' }}</td>
            <td>{{ $donation->purpose ?? '' }}</td>
            <td>{{ number_format($donation->amount, 2) }} €</td>
            <td>{{ $donation->financial_reference }}</td>
        </tr>
        @empty
        <tr><td colspan="7">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $donations->links() }}</p>
@endsection