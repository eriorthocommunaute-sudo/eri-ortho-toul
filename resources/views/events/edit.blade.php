@extends('layouts.app')
@section('content')
<h1>{{ __('app.edit_event') }} - {{ $event->reference }}</h1>

<form method="post" action="{{ route('events.update', $event) }}">
    @csrf @method('PUT')
    <div class="grid">
        <label>{{ __('app.title') }}<input name="title" type="text" value="{{ old('title', $event->title) }}" required></label>
        <label>{{ __('app.type') }}<select name="type" required>@foreach(['liturgy', 'meeting', 'celebration', 'formation', 'charity', 'other'] as $t)<option value="{{ $t }}" @selected($event->type==$t)>{{ __('app.'.$t) }}</option>@endforeach</select></label>
        <label>{{ __('app.starts_at') }}<input name="starts_at" type="datetime-local" value="{{ old('starts_at', $event->starts_at->format('Y-m-d\TH:i')) }}" required></label>
        <label>{{ __('app.ends_at') }}<input name="ends_at" type="datetime-local" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}"></label>
        <label>{{ __('app.location') }}<input name="location" type="text" value="{{ old('location', $event->location) }}"></label>
        <label>{{ __('app.expected_attendees') }}<input name="expected_attendees" type="number" min="0" value="{{ old('expected_attendees', $event->expected_attendees) }}"></label>
        <label>{{ __('app.actual_attendees') }}<input name="actual_attendees" type="number" min="0" value="{{ old('actual_attendees', $event->actual_attendees) }}"></label>
        <label>{{ __('app.budget') }}<input name="budget" type="number" step="0.01" min="0" value="{{ old('budget', $event->budget) }}"></label>
        <label>{{ __('app.actual_cost') }}<input name="actual_cost" type="number" step="0.01" min="0" value="{{ old('actual_cost', $event->actual_cost) }}"></label>
        <label>{{ __('app.status') }}<select name="status">@foreach(['planned', 'confirmed', 'completed', 'cancelled'] as $s)<option value="{{ $s }}" @selected($event->status==$s)>{{ __('app.'.$s) }}</option>@endforeach</select></label>
    </div>
    <label>{{ __('app.description') }}<textarea name="description" rows="3">{{ old('description', $event->description) }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('events.show', $event) }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection