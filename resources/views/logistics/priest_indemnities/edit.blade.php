@extends('layouts.app')
@section('content')
<h1>{{ __('app.edit_indemnity') }} - {{ $indemnity->reference }}</h1>

<form method="post" action="{{ route('priest-indemnities.update', $indemnity) }}">
    @csrf @method('PUT')
    <div class="grid">
        <label>{{ __('app.priest') }}<select name="priest_id"><option value="">{{ __('app.select_optional') }}</option>@foreach($priests as $p)<option value="{{ $p->id }}" @selected(old('priest_id', $indemnity->priest_id)==$p->id)>{{ $p->name }}</option>@endforeach</select></label>
        <label>{{ __('app.service_date') }}<input name="service_date" type="date" value="{{ old('service_date', $indemnity->service_date->format('Y-m-d')) }}" required max="{{ now()->format('Y-m-d') }}"></label>
        <label>{{ __('app.occasion') }}<input name="occasion" type="text" value="{{ old('occasion', $indemnity->occasion) }}" required></label>
        <label>{{ __('app.location') }}<input name="location" type="text" value="{{ old('location', $indemnity->location) }}"></label>
        <label>{{ __('app.amount') }}<input name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $indemnity->amount) }}" required></label>
        <label>{{ __('app.payment_method') }}<select name="payment_method"><option value="">{{ __('app.select_optional') }}</option>@foreach(['cash', 'transfer', 'check', 'other'] as $m)<option value="{{ $m }}" @selected($indemnity->payment_method==$m)>{{ __('app.'.$m) }}</option>@endforeach</select></label>
    </div>
    <label>{{ __('app.notes') }}<textarea name="notes" rows="3">{{ old('notes', $indemnity->notes) }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('priest-indemnities.show', $indemnity) }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection