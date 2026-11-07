@extends('layouts.app')
@section('content')
<h1>{{ __('app.new_transport') }}</h1>

<form method="post" action="{{ route('priest-transports.store') }}">
    @csrf
    <div class="grid">
        <label>{{ __('app.priest') }}<select name="priest_id"><option value="">{{ __('app.select_optional') }}</option>@foreach($priests as $p)<option value="{{ $p->id }}" @selected(old('priest_id')==$p->id)>{{ $p->name }}</option>@endforeach</select></label>
        <label>{{ __('app.transport_date') }}<input name="transport_date" type="date" value="{{ old('transport_date', now()->format('Y-m-d')) }}" required max="{{ now()->format('Y-m-d') }}"></label>
        <label>{{ __('app.origin') }}<input name="origin" type="text" value="{{ old('origin') }}" required></label>
        <label>{{ __('app.destination') }}<input name="destination" type="text" value="{{ old('destination') }}" required></label>
        <label>{{ __('app.occasion') }}<input name="occasion" type="text" value="{{ old('occasion') }}"></label>
        <label>{{ __('app.distance') }}<input name="distance_km" type="number" step="0.1" min="0" value="{{ old('distance_km') }}"></label>
        <label>{{ __('app.amount') }}<input name="amount" type="number" step="0.01" min="0.01" required></label>
        <label>{{ __('app.transport_type') }}<select name="transport_type" required>@foreach(['personal_vehicle', 'taxi', 'train', 'bus', 'flight', 'other'] as $t)<option value="{{ $t }}" @selected(old('transport_type')==$t)>{{ __('app.'.$t) }}</option>@endforeach</select></label>
        <label>{{ __('app.driver_name') }}<input name="driver_name" type="text" value="{{ old('driver_name') }}"></label>
        <label>{{ __('app.payment_method') }}<select name="payment_method"><option value="">{{ __('app.select_optional') }}</option>@foreach(['cash', 'transfer', 'check', 'other'] as $m)<option value="{{ $m }}" @selected(old('payment_method')==$m)>{{ __('app.'.$m) }}</option>@endforeach</select></label>
    </div>
    <label>{{ __('app.notes') }}<textarea name="notes" rows="3">{{ old('notes') }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('priest-transports.index') }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection