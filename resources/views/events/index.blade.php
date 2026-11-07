@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('events.create') }}">{{ __('app.new_event') }}</a>
</div>

<form class="grid" method="get">
    <label>{{ __('app.type') }}<select name="type"><option value="">{{ __('app.all') }}</option>@foreach($types as $t)<option value="{{ $t }}" @selected(request('type')==$t)>{{ __('app.'.$t) }}</option>@endforeach</select></label>
    <label>{{ __('app.status') }}<select name="status"><option value="">{{ __('app.all') }}</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ __('app.'.$s) }}</option>@endforeach</select></label>
    <label>{{ __('app.date_from') }}<input name="date_from" type="date" value="{{ request('date_from') }}"></label>
    <label>{{ __('app.date_to') }}<input name="date_to" type="date" value="{{ request('date_to') }}"></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.title') }}</th>
            <th>{{ __('app.starts_at') }}</th>
            <th>{{ __('app.type') }}</th>
            <th>{{ __('app.location') }}</th>
            <th>{{ __('app.status') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($events as $event)
        <tr>
            <td><a href="{{ route('events.show', $event) }}">{{ $event->reference }}</a></td>
            <td>{{ $event->title }}</td>
            <td>{{ $event->starts_at->format('d/m/Y H:i') }}</td>
            <td>{{ __('app.'.$event->type) }}</td>
            <td>{{ $event->location ?? '' }}</td>
            <td><span class="status-badge status-{{ $event->status }}">{{ __('app.'.$event->status) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="6">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $events->links() }}</p>
@endsection