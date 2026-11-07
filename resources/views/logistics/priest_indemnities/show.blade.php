@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $indemnity->reference }}</h1>
    <div style="margin-left: auto;">
        @if(auth()->user()->hasPermission('finance.record'))
        <a class="button" href="{{ route('priest-indemnities.edit', $indemnity) }}">{{ __('app.edit') }}</a>
        <form method="post" action="{{ route('priest-indemnities.destroy', $indemnity) }}" style="display: inline;">@csrf @method('DELETE')<button type="submit" class="button" style="background: #c53030;" onclick="return confirm('{{ __('app.confirm_delete') }}')">{{ __('app.delete') }}</button></form>
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $indemnity->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.financial_reference') }}</strong><br>
        {{ $indemnity->financial_reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.priest') }}</strong><br>
        {{ $indemnity->priest?->name ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.service_date') }}</strong><br>
        {{ $indemnity->service_date->format('d/m/Y') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.occasion') }}</strong><br>
        {{ $indemnity->occasion }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.location') }}</strong><br>
        {{ $indemnity->location ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.amount') }}</strong><br>
        {{ number_format($indemnity->amount, 2) }} €
    </div>
</div>

@if($indemnity->notes)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.notes') }}</strong><br>
    {{ $indemnity->notes }}
</div>
@endif
@endsection