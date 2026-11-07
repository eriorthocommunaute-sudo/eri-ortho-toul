@extends('layouts.app')
@section('content')
<h1>{{ __('app.new_minute') }}</h1>

<form method="post" action="{{ route('meeting-minutes.store') }}">
    @csrf
    <div class="grid">
        <label>{{ __('app.title') }}<input name="title" type="text" value="{{ old('title') }}" required></label>
        <label>{{ __('app.meeting_date') }}<input name="meeting_date" type="date" value="{{ old('meeting_date', now()->format('Y-m-d')) }}" required></label>
        <label>{{ __('app.starts_at') }}<input name="starts_at" type="datetime-local" value="{{ old('starts_at') }}"></label>
        <label>{{ __('app.ends_at') }}<input name="ends_at" type="datetime-local" value="{{ old('ends_at') }}"></label>
        <label>{{ __('app.location') }}<input name="location" type="text" value="{{ old('location') }}"></label>
        <label>{{ __('app.type') }}<select name="type" required>@foreach(['board', 'general_assembly', 'bureau', 'committee', 'other'] as $t)<option value="{{ $t }}" @selected(old('type')==$t)>{{ __('app.'.$t) }}</option>@endforeach</select></label>
        <label>{{ __('app.secretary') }}<select name="secretary_id"><option value="">{{ __('app.select_optional') }}</option>@foreach(\App\Models\User::where('is_active', true)->get() as $u)<option value="{{ $u->id }}" @selected(old('secretary_id')==$u->id)>{{ $u->name }}</option>@endforeach</select></label>
        <label>{{ __('app.president') }}<select name="president_id"><option value="">{{ __('app.select_optional') }}</option>@foreach(\App\Models\User::where('is_active', true)->get() as $u)<option value="{{ $u->id }}" @selected(old('president_id')==$u->id)>{{ $u->name }}</option>@endforeach</select></label>
    </div>
    <label>{{ __('app.agenda') }}<textarea name="agenda" rows="3">{{ old('agenda') }}</textarea></label>
    <label>{{ __('app.content') }}<textarea name="content" rows="5">{{ old('content') }}</textarea></label>
    <label>{{ __('app.attendees') }}<select name="attendees[]" multiple style="height: 120px;">@foreach(\App\Models\Member::where('status', 'active')->get() as $m)<option value="{{ $m->id }}" @selected(in_array($m->id, old('attendees', [])))>{{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}</option>@endforeach</select></label>
    <label>{{ __('app.absentees') }}<select name="absentees[]" multiple style="height: 120px;">@foreach(\App\Models\Member::where('status', 'active')->get() as $m)<option value="{{ $m->id }}" @selected(in_array($m->id, old('absentees', [])))>{{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}</option>@endforeach</select></label>
    <label>{{ __('app.decisions') }}<textarea name="decisions" rows="3">{{ old('decisions') }}</textarea></label>
    <label>{{ __('app.action_items') }}<textarea name="action_items" rows="3">{{ old('action_items') }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('meeting-minutes.index') }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection