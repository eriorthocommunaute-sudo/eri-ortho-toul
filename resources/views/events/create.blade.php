@extends('layouts.app')
@section('content')
<h1>{{ __('app.new_event') }}</h1>

<form method="post" action="{{ route('events.store') }}">
    @csrf
    <div class="grid">
        <label>{{ __('app.title') }}<input name="title" type="text" value="{{ old('title') }}" required></label>
        <label>{{ __('app.type') }}<select name="type" required>@foreach(['liturgy', 'meeting', 'celebration', 'formation', 'charity', 'other'] as $t)<option value="{{ $t }}" @selected(old('type')==$t)>{{ __('app.'.$t) }}</option>@endforeach</select></label>
        <label>{{ __('app.starts_at') }}<input name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" required></label>
        <label>{{ __('app.ends_at') }}<input name="ends_at" type="datetime-local" value="{{ old('ends_at') }}"></label>
        <label>{{ __('app.location') }}<input name="location" type="text" value="{{ old('location') }}"></label>
        <label>{{ __('app.expected_attendees') }}<input name="expected_attendees" type="number" min="0" value="{{ old('expected_attendees') }}"></label>
        <label>{{ __('app.budget') }}<input name="budget" type="number" step="0.01" min="0" value="{{ old('budget') }}"></label>
        <label>{{ __('app.status') }}<select name="status">@foreach(['planned', 'confirmed', 'completed', 'cancelled'] as $s)<option value="{{ $s }}" @selected(old('status', 'planned')==$s)>{{ __('app.'.$s) }}</option>@endforeach</select></label>
    </div>
    <label>{{ __('app.description') }}<textarea name="description" rows="3">{{ old('description') }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('events.index') }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection