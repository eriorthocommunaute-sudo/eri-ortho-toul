@extends('layouts.app')
@section('content')
<h1>{{ __('app.edit_receipt') }} - {{ $receipt->reference }}</h1>

<form method="post" action="{{ route('receipts.update', $receipt) }}">
    @csrf @method('PUT')
    <div class="grid">
        <label>{{ __('app.member') }}<select name="member_id" required>@foreach($members as $m)<option value="{{ $m->id }}" @selected($m->id == $receipt->member_id)>{{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}</option>@endforeach</select></label>
        <label>{{ __('app.contribution') }}<select name="contribution_id"><option value="">{{ __('app.select_optional') }}</option>@foreach($contributions as $c)<option value="{{ $c->id }}" @selected($c->id == $receipt->contribution_id)>{{ $c->reference }} ({{ $c->month }}/{{ $c->year }})</option>@endforeach</select></label>
        <label>{{ __('app.payment_date') }}<input name="payment_date" type="date" value="{{ old('payment_date', $receipt->payment_date->format('Y-m-d')) }}" required max="{{ now()->format('Y-m-d') }}"></label>
        <label>{{ __('app.amount') }}<input name="amount" type="number" step="0.01" min="0.01" value="{{ old('amount', $receipt->amount) }}" required></label>
        <label>{{ __('app.payment_method') }}<select name="payment_method" required>@foreach(['cash', 'transfer', 'check', 'other'] as $m)<option value="{{ $m }}" @selected($m == $receipt->payment_method)>{{ __('app.'.$m) }}</option>@endforeach</select></label>
    </div>
    <label>{{ __('app.notes') }}<textarea name="notes" rows="3">{{ old('notes', $receipt->notes) }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('receipts.show', $receipt) }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection