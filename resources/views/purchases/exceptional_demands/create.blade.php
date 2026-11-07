@extends('layouts.app')
@section('content')
<h1>{{ __('app.new_exceptional_demand') }}</h1>

<form method="post" action="{{ route('exceptional-demands.store') }}">
    @csrf
    <div class="grid">
        <label>{{ __('app.title') }}<input name="title" type="text" value="{{ old('title') }}" required></label>
        <label>{{ __('app.category') }}<select name="category"><option value="">{{ __('app.select_optional') }}</option>@foreach(['emergency', 'maintenance', 'legal', 'medical', 'other'] as $c)<option value="{{ $c }}" @selected(old('category')==$c)>{{ __('app.'.$c) }}</option>@endforeach</select></label>
        <label>{{ __('app.amount') }}<input name="amount" type="number" step="0.01" min="0.01" required></label>
        <label>{{ __('app.urgency') }}<select name="urgency" required>@foreach(['normal', 'urgent', 'critical'] as $u)<option value="{{ $u }}" @selected(old('urgency')==$u)>{{ __('app.'.$u) }}</option>@endforeach</select></label>
    </div>
    <label>{{ __('app.description') }}<textarea name="description" rows="4" required>{{ old('description') }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('exceptional-demands.index') }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection