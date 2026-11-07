@extends('layouts.app')
@section('content')
<h1>{{ __('app.edit_equipment') }} - {{ $equipment->reference }}</h1>

<form method="post" action="{{ route('equipment.update', $equipment) }}">
    @csrf @method('PUT')
    <div class="grid">
        <label>{{ __('app.name') }}<input name="name" type="text" value="{{ old('name', $equipment->name) }}" required></label>
        <label>{{ __('app.category') }}<select name="category"><option value="">{{ __('app.select_optional') }}</option>@foreach(['audio', 'video', 'furniture', 'liturgical', 'IT', 'other'] as $c)<option value="{{ $c }}" @selected($equipment->category==$c)>{{ __('app.'.$c) }}</option>@endforeach</select></label>
        <label>{{ __('app.purchase_date') }}<input name="purchase_date" type="date" value="{{ old('purchase_date', $equipment->purchase_date?->format('Y-m-d')) }}"></label>
        <label>{{ __('app.purchase_price') }}<input name="purchase_price" type="number" step="0.01" min="0" value="{{ old('purchase_price', $equipment->purchase_price) }}"></label>
        <label>{{ __('app.supplier') }}<input name="supplier" type="text" value="{{ old('supplier', $equipment->supplier) }}"></label>
        <label>{{ __('app.location') }}<input name="location" type="text" value="{{ old('location', $equipment->location) }}"></label>
        <label>{{ __('app.condition') }}<select name="condition"><option value="">{{ __('app.select_optional') }}</option>@foreach(['new', 'good', 'fair', 'poor', 'disposed'] as $c)<option value="{{ $c }}" @selected($equipment->condition==$c)>{{ __('app.'.$c) }}</option>@endforeach</select></label>
        <label>{{ __('app.serial_number') }}<input name="serial_number" type="text" value="{{ old('serial_number', $equipment->serial_number) }}"></label>
        <label>{{ __('app.payment_method') }}<select name="payment_method"><option value="">{{ __('app.select_optional') }}</option>@foreach(['cash', 'transfer', 'check', 'other'] as $m)<option value="{{ $m }}" @selected($equipment->payment_method==$m)>{{ __('app.'.$m) }}</option>@endforeach</select></label>
    </div>
    <label>{{ __('app.description') }}<textarea name="description" rows="3">{{ old('description', $equipment->description) }}</textarea></label>
    <label>{{ __('app.notes') }}<textarea name="notes" rows="3">{{ old('notes', $equipment->notes) }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('equipment.show', $equipment) }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection