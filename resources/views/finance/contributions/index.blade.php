@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('contributions.create') }}">{{ __('app.add_contribution') }}</a>
    <a class="button" href="{{ route('contributions.matrix') }}">{{ __('app.matrix_membership_contributions') }}</a>
</div>

<form class="grid" method="get" style="margin-bottom: 1rem;">
    <label>{{ __('app.year') }}<select name="year"><option value="">{{ __('app.all') }}</option>@for($y = date('Y'); $y >= 2020; $y--) <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>@endfor</select></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.period') }}</th>
            <th>{{ __('app.monthly_amount') }}</th>
            <th>{{ __('app.expected') }}</th>
            <th>{{ __('app.received') }}</th>
            <th>{{ __('app.progress') }}</th>
            <th>{{ __('app.status') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($contributions as $contribution)
        <tr>
            <td><a href="{{ route('contributions.show', $contribution) }}">{{ $contribution->reference }}</a></td>
            <td>{{ $contribution->month }}/{{ $contribution->year }}</td>
            <td>{{ number_format($contribution->amount_per_member, 2) }} €</td>
            <td>{{ number_format($contribution->total_expected, 2) }} €</td>
            <td>{{ number_format($contribution->total_received, 2) }} €</td>
            <td>
                <div style="height: 10px; background: #e2e8f0; border-radius: 4px; width: 100px; overflow: hidden;">
                    <div style="width: {{ $contribution->progress_percent }}%; height: 100%; background: #0f6b78;"></div>
                </div>
                {{ $contribution->progress_percent }}%
            </td>
            <td><span class="status-badge status-{{ $contribution->status }}">{{ __('app.'.$contribution->status) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="7">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $contributions->links() }}</p>
@endsection