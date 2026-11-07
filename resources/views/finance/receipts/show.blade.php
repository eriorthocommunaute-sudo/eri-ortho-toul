@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $receipt->reference }}</h1>
    <p><strong>Type :</strong> {{ ucfirst($receipt->receipt_type ?? 'cotisation') }}</p>
    @if($receipt->description)<p><strong>Motif :</strong> {{ $receipt->description }}</p>@endif
    <div style="margin-left: auto;">
        @if($receipt->status === 'draft' && auth()->user()->hasPermission('finance.record'))
        <a class="button" href="{{ route('receipts.edit', $receipt) }}">{{ __('app.edit') }}</a>
        <form method="post" action="{{ route('receipts.confirm', $receipt) }}" style="display: inline;">@csrf<button type="submit" class="button" style="background: #059669;">{{ __('app.confirm') }}</button></form>
        <form method="post" action="{{ route('receipts.cancel', $receipt) }}" style="display: inline;">@csrf<button type="submit" class="button" style="background: #b45309;">{{ __('app.cancel_receipt') }}</button></form>
        <form method="post" action="{{ route('receipts.destroy', $receipt) }}" style="display: inline;">@csrf @method('DELETE')<button type="submit" class="button" style="background: #c53030;" onclick="return confirm('{{ __('app.confirm_delete') }}')">{{ __('app.delete') }}</button></form>
        @endif
        @if($receipt->status === 'confirmed')
        <a class="button" href="{{ route('receipts.print', $receipt) }}" target="_blank">{{ __('app.print') }}</a>
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>Titre / Nom / Payeur / Donateur</strong><br>
        {{ $receipt->member ? ($receipt->member->member_code . ' - ' . trim(($receipt->member->title ? $receipt->member->title . ' ' : '') . $receipt->member->given_name . ' ' . $receipt->member->family_name)) : ($receipt->payer_name ?: 'Anonyme') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.contribution') }}</strong><br>
        @if($receipt->contribution)
        <a href="{{ route('contributions.show', $receipt->contribution) }}">{{ $receipt->contribution->reference }}</a>
        @else
        {{ __('app.not_linked') }}
        @endif
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.payment_date') }}</strong><br>
        {{ $receipt->payment_date->format('d/m/Y') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.amount') }}</strong><br>
        {{ number_format($receipt->amount, 2) }} €
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.payment_method') }}</strong><br>
        {{ __('app.'.$receipt->payment_method) }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.financial_reference') }}</strong><br>
        {{ $receipt->financial_reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.status') }}</strong><br>
        <span class="status-badge status-{{ $receipt->status }}">{{ __('app.'.$receipt->status) }}</span>
    </div>
    @if($receipt->confirmed_at)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.confirmed_at') }}</strong><br>
        {{ $receipt->confirmed_at->format('d/m/Y H:i') }} par {{ $receipt->confirmer->name ?? '' }}
    </div>
    @endif
</div>

@if($receipt->notes)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.notes') }}</strong><br>
    {{ $receipt->notes }}
</div>
@endif
@endsection
