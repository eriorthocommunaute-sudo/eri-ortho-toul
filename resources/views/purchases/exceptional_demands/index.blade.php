@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('exceptional-demands.create') }}">{{ __('app.new_exceptional_demand') }}</a>
</div>

<form class="grid" method="get">
    <label>{{ __('app.status') }}<select name="status"><option value="">{{ __('app.all') }}</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ __('app.'.$s) }}</option>@endforeach</select></label>
    <label>{{ __('app.urgency') }}<select name="urgency"><option value="">{{ __('app.all') }}</option>@foreach($urgencies as $u)<option value="{{ $u }}" @selected(request('urgency')==$u)>{{ __('app.'.$u) }}</option>@endforeach</select></label>
    <label>{{ __('app.category') }}<select name="category"><option value="">{{ __('app.all') }}</option>@foreach($categories as $c)<option value="{{ $c }}" @selected(request('category')==$c)>{{ __('app.'.$c) }}</option>@endforeach</select></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.title') }}</th>
            <th>{{ __('app.category') }}</th>
            <th>{{ __('app.amount') }}</th>
            <th>{{ __('app.urgency') }}</th>
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.requested_by') }}</th>
            <th>{{ __('app.approved_by') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($demands as $demand)
        <tr>
            <td><a href="{{ route('exceptional-demands.show', $demand) }}">{{ $demand->reference }}</a></td>
            <td>{{ $demand->title }}</td>
            <td>{{ $demand->category ? __('app.'.$demand->category) : '' }}</td>
            <td>{{ number_format($demand->amount, 2) }} €</td>
            <td><span class="status-badge">{{ __('app.'.$demand->urgency) }}</span></td>
            <td><span class="status-badge status-{{ $demand->status }}">{{ __('app.'.$demand->status) }}</span></td>
            <td>{{ $demand->requester->name ?? '' }}</td>
            <td>{{ $demand->approver->name ?? '' }}</td>
        </tr>
        @empty
        <tr><td colspan="8">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $demands->links() }}</p>
@endsection