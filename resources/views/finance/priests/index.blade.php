@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('priests.create') }}">{{ __('app.new_priest') }}</a>
</div>

<form class="grid" method="get">
    <label>{{ __('app.search') }}<input name="search" value="{{ request('search') }}"></label>
    <label>{{ __('app.status') }}<select name="status"><option value="">{{ __('app.all') }}</option><option value="active" @selected(request('status')==='active')>{{ __('app.active') }}</option><option value="inactive" @selected(request('status')==='inactive')>{{ __('app.inactive') }}</option></select></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.name') }}</th>
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.notes') }}</th>
            <th>{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($priests as $priest)
        <tr>
            <td>{{ $priest->name }}</td>
            <td><span class="status-badge status-{{ $priest->is_active ? 'active' : 'inactive' }}">{{ $priest->is_active ? __('app.active') : __('app.inactive') }}</span></td>
            <td>{{ $priest->notes ?? '' }}</td>
            <td><a class="link" href="{{ route('priests.edit', $priest) }}">{{ __('app.edit') }}</a></td>
        </tr>
        @empty
        <tr><td colspan="4">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $priests->links() }}</p>
@endsection
