@extends('layouts.app')
@section('content')
<h1>{{ __('app.new_priest') }}</h1>

<form method="post" action="{{ route('priests.store') }}">
    @csrf
    <div class="grid">
        <label>{{ __('app.name') }}<input name="name" type="text" value="{{ old('name') }}" required></label>
    </div>
    <label>{{ __('app.notes') }}<textarea name="notes" rows="3">{{ old('notes') }}</textarea></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('priests.index') }}" style="background: #6b7280;">{{ __('app.cancel') }}</a>
</form>
@endsection
