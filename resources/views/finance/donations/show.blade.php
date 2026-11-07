@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $donation->reference }}</h1>
    <div style="margin-left: auto;">
        @if(auth()->user()->hasPermission('finance.record'))
        <a class="button" href="{{ route('donations.edit', $donation) }}">{{ __('app.edit') }}</a>
        <form method="post" action="{{ route('donations.destroy', $donation) }}" style="display: inline;">@csrf @method('DELETE')<button type="submit" class="button" style="background: #c53030;" onclick="return confirm('{{ __('app.confirm_delete') }}')">{{ __('app.delete') }}</button></form>
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $donation->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.financial_reference') }}</strong><br>
        {{ $donation->financial_reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.donation_date') }}</strong><br>
        {{ $donation->donation_date->format('d/m/Y') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.amount') }}</strong><br>
        {{ number_format($donation->amount, 2) }} €
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.donor_name') }}</strong><br>
        {{ $donation->donor_name }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.donor_type') }}</strong><br>
        {{ $donation->donor_type ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.purpose') }}</strong><br>
        {{ $donation->purpose ?? '' }}
    </div>
</div>

@if($donation->notes)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.notes') }}</strong><br>
    {{ $donation->notes }}
</div>
@endif
@endsection