@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $transport->reference }}</h1>
    <div style="margin-left: auto;">
        @if(auth()->user()->hasPermission('finance.record'))
        <a class="button" href="{{ route('priest-transports.edit', $transport) }}">{{ __('app.edit') }}</a>
        <form method="post" action="{{ route('priest-transports.destroy', $transport) }}" style="display: inline;">@csrf @method('DELETE')<button type="submit" class="button" style="background: #c53030;" onclick="return confirm('{{ __('app.confirm_delete') }}')">{{ __('app.delete') }}</button></form>
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $transport->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.financial_reference') }}</strong><br>
        {{ $transport->financial_reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.priest') }}</strong><br>
        {{ $transport->priest?->name ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.transport_date') }}</strong><br>
        {{ $transport->transport_date->format('d/m/Y') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.origin') }}</strong><br>
        {{ $transport->origin }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.destination') }}</strong><br>
        {{ $transport->destination }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.occasion') }}</strong><br>
        {{ $transport->occasion ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.distance') }}</strong><br>
        {{ $transport->distance_km ? number_format($transport->distance_km, 1) . ' km' : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.amount') }}</strong><br>
        {{ number_format($transport->amount, 2) }} €
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.transport_type') }}</strong><br>
        {{ __('app.'.$transport->transport_type) }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.driver_name') }}</strong><br>
        {{ $transport->driver_name ?? '' }}
    </div>
</div>

@if($transport->notes)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.notes') }}</strong><br>
    {{ $transport->notes }}
</div>
@endif
@endsection