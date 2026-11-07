@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $minute->title }}</h1>
    <div style="margin-left: auto;">
        @if($minute->status === 'draft' && auth()->user()->hasPermission('secretary.manage'))
        <a class="button" href="{{ route('meeting-minutes.edit', $minute) }}">{{ __('app.edit') }}</a>
        <form method="post" action="{{ route('meeting-minutes.approve', $minute) }}" style="display: inline;">@csrf<button type="submit" class="button" style="background: #059669;">{{ __('app.approve') }}</button></form>
        <form method="post" action="{{ route('meeting-minutes.destroy', $minute) }}" style="display: inline;">@csrf @method('DELETE')<button type="submit" class="button" style="background: #c53030;" onclick="return confirm('{{ __('app.confirm_delete') }}')">{{ __('app.delete') }}</button></form>
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $minute->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.type') }}</strong><br>
        {{ __('app.'.$minute->type) }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.status') }}</strong><br>
        <span class="status-badge status-{{ $minute->status }}">{{ __('app.'.$minute->status) }}</span>
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.meeting_date') }}</strong><br>
        {{ $minute->meeting_date->format('d/m/Y') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.starts_at') }}</strong><br>
        {{ $minute->starts_at ? $minute->starts_at->format('d/m/Y H:i') : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.ends_at') }}</strong><br>
        {{ $minute->ends_at ? $minute->ends_at->format('d/m/Y H:i') : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.location') }}</strong><br>
        {{ $minute->location ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.secretary') }}</strong><br>
        {{ $minute->secretary->name ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.president') }}</strong><br>
        {{ $minute->president->name ?? '' }}
    </div>
    @if($minute->approved_at)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.approved_at') }}</strong><br>
        {{ $minute->approved_at->format('d/m/Y H:i') }}
    </div>
    @endif
</div>

@if($minute->agenda)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.agenda') }}</strong><br>
    {{ $minute->agenda }}
</div>
@endif

@if($minute->content)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.content') }}</strong><br>
    {{ $minute->content }}
</div>
@endif

@if($minute->attendees)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.attendees') }}</strong><br>
    @foreach($minute->attendeeMembers as $m)
        {{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}<br>
    @endforeach
</div>
@endif

@if($minute->absentees)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.absentees') }}</strong><br>
    @foreach($minute->absenteeMembers as $m)
        {{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}<br>
    @endforeach
</div>
@endif

@if($minute->decisions)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.decisions') }}</strong><br>
    {{ $minute->decisions }}
</div>
@endif

@if($minute->action_items)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.action_items') }}</strong><br>
    {{ $minute->action_items }}
</div>
@endif
@endsection