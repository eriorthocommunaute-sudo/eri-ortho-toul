@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('meeting-minutes.create') }}">{{ __('app.new_minute') }}</a>
</div>

<form class="grid" method="get">
    <label>{{ __('app.type') }}<select name="type"><option value="">{{ __('app.all') }}</option>@foreach($types as $t)<option value="{{ $t }}" @selected(request('type')==$t)>{{ __('app.'.$t) }}</option>@endforeach</select></label>
    <label>{{ __('app.status') }}<select name="status"><option value="">{{ __('app.all') }}</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ __('app.'.$s) }}</option>@endforeach</select></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.title') }}</th>
            <th>{{ __('app.meeting_date') }}</th>
            <th>{{ __('app.type') }}</th>
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.secretary') }}</th>
            <th>{{ __('app.president') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($minutes as $minute)
        <tr>
            <td><a href="{{ route('meeting-minutes.show', $minute) }}">{{ $minute->reference }}</a></td>
            <td>{{ $minute->title }}</td>
            <td>{{ $minute->meeting_date->format('d/m/Y') }}</td>
            <td>{{ __('app.'.$minute->type) }}</td>
            <td><span class="status-badge status-{{ $minute->status }}">{{ __('app.'.$minute->status) }}</span></td>
            <td>{{ $minute->secretary->name ?? '' }}</td>
            <td>{{ $minute->president->name ?? '' }}</td>
        </tr>
        @empty
        <tr><td colspan="7">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $minutes->links() }}</p>
@endsection