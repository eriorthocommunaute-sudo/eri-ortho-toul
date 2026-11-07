@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $notice->reference }}</h1>
    <div style="margin-left: auto;">
        @if($notice->status === 'generated' && auth()->user()->hasPermission('secretary.manage'))
        <form method="post" action="{{ route('contribution-notices.send', $notice) }}" style="display: inline;">@csrf<button type="submit" class="button">{{ __('app.send') }}</button></form>
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $notice->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.member') }}</strong><br>
        {{ $notice->member->member_code }} - {{ $notice->member->given_name }} {{ $notice->member->family_name }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.contribution') }}</strong><br>
        {{ $notice->contribution->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.amount') }}</strong><br>
        {{ number_format($notice->amount, 2) }} €
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.notice_date') }}</strong><br>
        {{ $notice->notice_date->format('d/m/Y') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.due_date') }}</strong><br>
        {{ $notice->due_date->format('d/m/Y') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.status') }}</strong><br>
        <span class="status-badge status-{{ $notice->status }}">{{ __('app.'.$notice->status) }}</span>
    </div>
    @if($notice->sent_date)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.sent_date') }}</strong><br>
        {{ $notice->sent_date->format('d/m/Y') }}
    </div>
    @endif
</div>

@if($notice->notes)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.notes') }}</strong><br>
    {{ $notice->notes }}
</div>
@endif
@endsection