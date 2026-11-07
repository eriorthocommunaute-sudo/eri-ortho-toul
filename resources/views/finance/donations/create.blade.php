@extends('layouts.app')
@section('content')
<h1>{{ __('app.record_donation') }}</h1>

<form method="post" action="{{ route('donations.store') }}">
    @csrf
    <div class="grid">
        <label>{{ __('app.donation_date') }}<input name="donation_date" type="date" value="{{ old('donation_date', now()->format('Y-m-d')) }}" required max="{{ now()->format('Y-m-d') }}"></label>
        <label>{{ __('app.donor_name') }}<input name="donor_name" type="text" value="{{ old('donor_name') }}" required></label>
        <label>{{ __('app.donor_type') }}<select name="donor_type"><option value="">{{ __('app.select_optional') }}</option><option value="association">{{ __('app.association') }}</option><option value="individual">{{ __('app.individual') }}</option><option value="corporate">{{ __('app.corporate') }}</option></select></label>
        <label>{{ __('app.amount') }}<input name="amount" type="number" step="0.01" min="0.01" required></label>
        <label>{{ __('app.purpose') }}<input name="purpose" type="text" value="{{ old('purpose') }}"></label>
    </div>
    <label>{{ __('app.notes') }}<textarea name="notes" rows="3">{{ old('notes') }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('donations.index') }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection