@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $equipment->name }}</h1>
    <div style="margin-left: auto;">
        @if(auth()->user()->hasPermission('logistics.manage'))
        <a class="button" href="{{ route('equipment.edit', $equipment) }}">{{ __('app.edit') }}</a>
        <form method="post" action="{{ route('equipment.destroy', $equipment) }}" style="display: inline;">@csrf @method('DELETE')<button type="submit" class="button" style="background: #c53030;" onclick="return confirm('{{ __('app.confirm_delete') }}')">{{ __('app.delete') }}</button></form>
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $equipment->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.financial_reference') }}</strong><br>
        {{ $equipment->financial_reference ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.category') }}</strong><br>
        {{ $equipment->category ? __('app.'.$equipment->category) : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.condition') }}</strong><br>
        <span class="status-badge status-{{ $equipment->condition }}">{{ $equipment->condition ? __('app.'.$equipment->condition) : '' }}</span>
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.location') }}</strong><br>
        {{ $equipment->location ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.serial_number') }}</strong><br>
        {{ $equipment->serial_number ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.purchase_date') }}</strong><br>
        {{ $equipment->purchase_date ? $equipment->purchase_date->format('d/m/Y') : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.purchase_price') }}</strong><br>
        {{ $equipment->purchase_price ? number_format($equipment->purchase_price, 2) . ' €' : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.supplier') }}</strong><br>
        {{ $equipment->supplier ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.purchase_reference') }}</strong><br>
        {{ $equipment->purchase_reference ?? '' }}
    </div>
</div>

@if($equipment->description)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.description') }}</strong><br>
    {{ $equipment->description }}
</div>
@endif

@if($equipment->notes)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.notes') }}</strong><br>
    {{ $equipment->notes }}
</div>
@endif
@endsection