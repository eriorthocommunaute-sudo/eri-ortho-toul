@extends('layouts.app')
@section('content')
<h1>{{ __('app.add_contribution') }}</h1>

@if($existing)
    <div class="alert" style="background: #fef3c7; border: 1px solid #f59e0b; color: #92400e; padding: 1rem; margin-bottom: 1rem;">
        {{ __('app.contribution_exists', ['month' => $nextMonth, 'year' => $nextYear]) }}
        <a href="{{ route('contributions.index') }}" class="button" style="margin-top: .5rem;">{{ __('app.view_list') }}</a>
    </div>
@endif

<form method="post" action="{{ route('contributions.store') }}">
    @csrf
    <div class="form-group">
        <label>{{ __('app.year') }}<input name="year" type="number" value="{{ $nextYear }}" min="2020" max="2030" required></label>
    </div>
    <div class="form-group">
        <label>{{ __('app.month') }}<select name="month" required>@for($m = 1; $m <= 12; $m++)<option value="{{ $m }}" @selected($m == $nextMonth)>{{ $m }} - {{ app()->getLocale() === 'ti' ? \App\Services\FinancialRegisterGenerator::MONTHS_TI[$m] : \App\Services\FinancialRegisterGenerator::MONTHS_FR[$m] }}</option>@endfor</select></label>
    </div>
    <div class="form-group">
        <label>{{ __('app.monthly_amount') }}<input name="amount_per_member" type="number" step="0.01" min="0" value="10.00" required></label>
    </div>
    <p>{{ __('app.expected_members', ['count' => $activeMembers]) }}</p>
    <p>{{ __('app.expected_total', ['amount' => number_format($activeMembers * 10, 2)]) }}</p>
    <div class="form-actions">
        <button type="submit">{{ __('app.save') }}</button>
        <a class="button" href="{{ route('contributions.index') }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
    </div>
</form>
@endsection
