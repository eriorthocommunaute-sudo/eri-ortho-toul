@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $event->title }}</h1>
    <div style="margin-left: auto;">
        @if(auth()->user()->hasPermission('events.manage'))
        <a class="button" href="{{ route('events.edit', $event) }}">{{ __('app.edit') }}</a>
        <form method="post" action="{{ route('events.destroy', $event) }}" style="display: inline;">@csrf @method('DELETE')<button type="submit" class="button" style="background: #c53030;" onclick="return confirm('{{ __('app.confirm_delete') }}')">{{ __('app.delete') }}</button></form>
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $event->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.financial_reference') }}</strong><br>
        {{ $event->financial_reference ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.type') }}</strong><br>
        {{ __('app.'.$event->type) }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.status') }}</strong><br>
        <span class="status-badge status-{{ $event->status }}">{{ __('app.'.$event->status) }}</span>
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.starts_at') }}</strong><br>
        {{ $event->starts_at->format('d/m/Y H:i') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.ends_at') }}</strong><br>
        {{ $event->ends_at ? $event->ends_at->format('d/m/Y H:i') : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.location') }}</strong><br>
        {{ $event->location ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.expected_attendees') }}</strong><br>
        {{ $event->expected_attendees ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.actual_attendees') }}</strong><br>
        {{ $event->actual_attendees ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.budget') }}</strong><br>
        {{ $event->budget ? number_format($event->budget, 2) . ' €' : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.actual_cost') }}</strong><br>
        {{ $event->actual_cost ? number_format($event->actual_cost, 2) . ' €' : '' }}
    </div>
</div>

@if($event->description)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.description') }}</strong><br>
    {{ $event->description }}
</div>
@endif
@endsection