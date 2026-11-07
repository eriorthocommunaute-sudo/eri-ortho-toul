@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $offrande->reference }}</h1>
    <div style="margin-left: auto;">
        @if(auth()->user()->hasPermission('finance.record'))
        <a class="button" href="{{ route('offrandes.edit', $offrande) }}">{{ __('app.edit') }}</a>
        <form method="post" action="{{ route('offrandes.destroy', $offrande) }}" style="display: inline;">@csrf @method('DELETE')<button type="submit" class="button" style="background: #c53030;" onclick="return confirm('{{ __('app.confirm_delete') }}')">{{ __('app.delete') }}</button></form>
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $offrande->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.financial_reference') }}</strong><br>
        {{ $offrande->financial_reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.offering_date') }}</strong><br>
        {{ $offrande->offering_date->format('d/m/Y') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.amount') }}</strong><br>
        {{ number_format($offrande->amount, 2) }} €
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.occasion') }}</strong><br>
        {{ $offrande->occasion ?? __('app.not_specified') }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.donor') }}</strong><br>
        {{ $offrande->member ? $offrande->member->member_code . ' - ' . $offrande->member->given_name . ' ' . $offrande->member->family_name : $offrande->donor_name }}
    </div>
</div>

@if($offrande->notes)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.notes') }}</strong><br>
    {{ $offrande->notes }}
</div>
@endif
@endsection