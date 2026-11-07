@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('founding-cotisations.create') }}">{{ __('app.add_founding_cotisation') }}</a>
    <a class="button" href="{{ route('founding-cotisations.register') }}" style="background: #0f6b78;">Registre Cotisations 200€</a>
</div>

<form class="grid" method="get" style="margin-bottom: 1rem;">
    <label>{{ __('app.search') }}<input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('app.search_members') }}"></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.member') }}</th>
            <th>{{ __('app.amount') }}</th>
            <th>{{ __('app.remaining') }}</th>
            <th>{{ __('app.payment_date') }}</th>
            <th>{{ __('app.payment_method') }}</th>
            <th>{{ __('app.notes') }}</th>
            <th>{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($foundingCotisations as $item)
        <tr>
            <td>{{ $item->member->given_name }} {{ $item->member->family_name }} ({{ $item->member->member_code }})</td>
            <td>{{ number_format($item->amount, 0) }} €</td>
            <td>{{ $item->remaining > 0 ? number_format($item->remaining, 0) . ' €' : '—' }}</td>
            <td>{{ $item->payment_date?->format('d/m/Y') ?? '' }}</td>
            <td>{{ $item->payment_method ?? '' }}</td>
            <td>{{ $item->notes ?? '' }}</td>
            <td>
                <a href="{{ route('founding-cotisations.edit', $item) }}">{{ __('app.edit') }}</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $foundingCotisations->links() }}</p>
@endsection
