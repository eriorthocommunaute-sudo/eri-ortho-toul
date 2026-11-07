@extends('layouts.app')
@section('content')
<h1>{{ __('app.edit_contribution') }} - {{ $contribution->reference }}</h1>

<form method="post" action="{{ route('contributions.update', $contribution) }}">
    @csrf @method('PUT')
    <div class="grid">
        <label>{{ __('app.year') }}<input name="year" type="number" value="{{ $contribution->year }}" min="2020" max="2030" required></label>
        <label>{{ __('app.month') }}<select name="month" required>@for($m = 1; $m <= 12; $m++)<option value="{{ $m }}" @selected($m == $contribution->month)>{{ $m }} - {{ app()->getLocale() === 'ti' ? \App\Services\FinancialRegisterGenerator::MONTHS_TI[$m] : \App\Services\FinancialRegisterGenerator::MONTHS_FR[$m] }}</option>@endfor</select></label>
        <label>{{ __('app.monthly_amount') }}<input name="amount_per_member" type="number" step="0.01" min="0" value="{{ $contribution->amount_per_member }}" required></label>
        <label>{{ __('app.status') }}<select name="status">@foreach(['open', 'closed'] as $s)<option value="{{ $s }}" @selected($s == $contribution->status)>{{ __('app.'.$s) }}</option>@endforeach</select></label>
    </div>
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('contributions.show', $contribution) }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection
