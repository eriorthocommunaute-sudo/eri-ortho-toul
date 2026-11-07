@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $demand->reference }} - {{ $demand->title }}</h1>
    <div style="margin-left: auto;">
        @if(auth()->user()->hasPermission('purchases.manage'))
        @if($demand->status === 'draft')
        <a class="button" href="{{ route('exceptional-demands.edit', $demand) }}">{{ __('app.edit') }}</a>
        <form method="post" action="{{ route('exceptional-demands.submitForReview', $demand) }}" style="display: inline;">@csrf<button type="submit" class="button" style="background: #b45309;">{{ __('app.submit_review') }}</button></form>
        <form method="post" action="{{ route('exceptional-demands.destroy', $demand) }}" style="display: inline;">@csrf @method('DELETE')<button type="submit" class="button" style="background: #c53030;" onclick="return confirm('{{ __('app.confirm_delete') }}')">{{ __('app.delete') }}</button></form>
        @endif
        @if(in_array($demand->status, ['submitted', 'under_review']))
        <form method="post" action="{{ route('exceptional-demands.approve', $demand) }}" style="display: inline;">@csrf<input name="approval_notes" type="text" placeholder="{{ __('app.approval_notes_placeholder') }}" style="margin-right: .5rem;"><button type="submit" class="button" style="background: #059669;">{{ __('app.approve') }}</button></form>
        <form method="post" action="{{ route('exceptional-demands.reject', $demand) }}" style="display: inline;">@csrf<input name="approval_notes" type="text" placeholder="{{ __('app.rejection_reason') }}" style="margin-right: .5rem;"><button type="submit" class="button" style="background: #c53030;">{{ __('app.reject') }}</button></form>
        @endif
        @if($demand->status === 'approved')
        <form method="post" action="{{ route('exceptional-demands.markPaid', $demand) }}" style="display: inline;">@csrf
            <select name="payment_method" required style="margin-right: .5rem;">@foreach(['cash', 'transfer', 'check', 'other'] as $m)<option value="{{ $m }}">{{ __('app.'.$m) }}</option>@endforeach</select>
            <button type="submit" class="button" style="background: #059669;">{{ __('app.mark_paid') }}</button>
        </form>
        @endif
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $demand->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.status') }}</strong><br>
        <span class="status-badge status-{{ $demand->status }}">{{ __('app.'.$demand->status) }}</span>
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.urgency') }}</strong><br>
        <span class="status-badge">{{ __('app.'.$demand->urgency) }}</span>
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.amount') }}</strong><br>
        {{ number_format($demand->amount, 2) }} €
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.category') }}</strong><br>
        {{ $demand->category ? __('app.'.$demand->category) : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.requested_by') }}</strong><br>
        {{ $demand->requester->name ?? '' }}
    </div>
    @if($demand->reviewed_at)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reviewed_by') }}</strong><br>
        {{ $demand->reviewer->name ?? '' }} ({{ $demand->reviewed_at->format('d/m/Y H:i') }})
    </div>
    @endif
    @if($demand->approved_at)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.approved_by') }}</strong><br>
        {{ $demand->approver->name ?? '' }} ({{ $demand->approved_at->format('d/m/Y H:i') }})
    </div>
    @endif
    @if($demand->financial_reference)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.financial_reference') }}</strong><br>
        {{ $demand->financial_reference }}
    </div>
    @endif
</div>

<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.description') }}</strong><br>
    {{ $demand->description }}
</div>

@if($demand->approval_notes)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.approval_notes') }}</strong><br>
    {{ $demand->approval_notes }}
</div>
@endif
@endsection