@extends('layouts.app')
@section('content')
@php
    $isIncome = ($entry->type ?? null) === 'income';
    $entryDate = $entry->entry_date ? \Illuminate\Support\Carbon::parse($entry->entry_date)->format('d/m/Y') : '';
    $memberName = '';
    $memberCode = '';
    if ($entry->source_type === 'App\Models\Receipt' && $entry->source) {
        $member = $entry->source->member;
        if ($member) {
            $memberName = $member->display_name ?? '';
            $memberCode = $member->member_code ?? '';
        }
    }
@endphp

<h1>{{ $entry->reference ?? '' }}</h1>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $entry->reference ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.date') }}</strong><br>
        {{ $entryDate }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.type') }}</strong><br>
        @if($entry->type)
            <span class="status-badge status-{{ $entry->type }}">{{ __('app.'.$entry->type) }}</span>
        @endif
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.category') }}</strong><br>
        {{ $entry->category ? __('app.'.$entry->category) : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.amount') }}</strong><br>
        <span style="color: {{ $isIncome ? '#166534' : '#991b1b' }};">{{ $isIncome ? '+' : '-' }}{{ number_format((float) $entry->amount, 2, ',', ' ') }} €</span>
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.payment_method') }}</strong><br>
        {{ $entry->payment_method ? __('app.'.$entry->payment_method) : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.source') }}</strong><br>
        {{ $entry->source_type ? class_basename($entry->source_type) : '' }}
    </div>
</div>

<div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem; margin-bottom: 1rem;">
    <strong>{{ __('app.member') }}</strong><br>
@if($memberName)
        <span>{{ $memberName }}{{$memberCode ? ' (' . $memberCode . ')' : ''}}</span>
@else
        {{ __('app.unknown_member') }}
@endif
</div>

<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.description') }}</strong><br>
    {{ $entry->description ?? '' }}
</div>

@if($entry->notes)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.notes') }}</strong><br>
    {{ $entry->notes }}
</div>
@endif
@endsection