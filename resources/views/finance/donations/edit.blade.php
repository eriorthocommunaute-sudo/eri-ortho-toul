@extends('layouts.app')
@section('content')
<h1>{{ __('app.edit_donation') }} - {{ $donation->reference }}</h1>

<form method="post" action="{{ route('donations.update', $donation) }}">
    @csrf @method('PUT')
    <div class="grid">
        <label>{{ __('app.donation_date') }}<input name="donation_date" type="date" value="{{ old('donation_date', $donation->donation_date->format('Y-m-d')) }}" required max="{{ now()->format('Y-m-d') }}"></label>
        <label>{{ __('app.donor_name') }}<input name="donor_name" type="text" value="{{ old('donor_name', $donation->donor_name) }}" required></label>
        <label>{{ __('app.donor_type') }}<select name="donor_type"><option value="">{{ __('app.select_optional') }}</option><option value="association" @selected($donation->donor_type == 'association')>{{ __('app.association') }}</option><option value="individual" @selected($donation->donor_type == 'individual')>{{ __('app.individual') }}</option><option value="corporate" @selected($donation->donor_type == 'corporate')>{{ __('app.corporate') }}</option></select></label>
        <label>{{ __('app.amount') }}<input name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $donation->amount) }}" required></label>
        <label>{{ __('app.purpose') }}<input name="purpose" type="text" value="{{ old('purpose', $donation->purpose) }}"></label>
    </div>
    <label>{{ __('app.notes') }}<textarea name="notes" rows="3">{{ old('notes', $donation->notes) }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('donations.show', $donation) }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection