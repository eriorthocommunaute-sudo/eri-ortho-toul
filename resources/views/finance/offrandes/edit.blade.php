@extends('layouts.app')
@section('content')
<h1>{{ __('app.edit_offrande') }} - {{ $offrande->reference }}</h1>

<form method="post" action="{{ route('offrandes.update', $offrande) }}">
    @csrf @method('PUT')
    <div class="grid">
        <label>{{ __('app.offering_date') }}<input name="offering_date" type="date" value="{{ old('offering_date', $offrande->offering_date->format('Y-m-d')) }}" required max="{{ now()->format('Y-m-d') }}"></label>
        <label>{{ __('app.member') }}<select name="member_id"><option value="">{{ __('app.select_optional') }}</option>@foreach($members as $m)<option value="{{ $m->id }}" @selected($m->id == $offrande->member_id)>{{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}</option>@endforeach</select></label>
        <label>{{ __('app.donor_name') }}<input name="donor_name" type="text" value="{{ old('donor_name', $offrande->donor_name) }}"></label>
        <label>{{ __('app.amount') }}<input name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $offrande->amount) }}" required></label>
        <label>{{ __('app.occasion') }}<input name="occasion" type="text" value="{{ old('occasion', $offrande->occasion) }}"></label>
    </div>
    <label>{{ __('app.notes') }}<textarea name="notes" rows="3">{{ old('notes', $offrande->notes) }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('offrandes.show', $offrande) }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection