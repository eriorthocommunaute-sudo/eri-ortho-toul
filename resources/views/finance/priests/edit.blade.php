@extends('layouts.app')
@section('content')
<h1>{{ __('app.edit_priest') }} - {{ $priest->name }}</h1>

<form method="post" action="{{ route('priests.update', $priest) }}">
    @csrf @method('PUT')
    <div class="grid">
        <label>{{ __('app.name') }}<input name="name" type="text" value="{{ old('name', $priest->name) }}" required></label>
        <label>{{ __('app.status') }}<select name="is_active"><option value="1" @selected(old('is_active', $priest->is_active))>{{ __('app.active') }}</option><option value="0" @selected(old('is_active', $priest->is_active) === false || old('is_active') === '0')>{{ __('app.inactive') }}</option></select></label>
    </div>
    <label>{{ __('app.notes') }}<textarea name="notes" rows="3">{{ old('notes', $priest->notes) }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('priests.index') }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection
