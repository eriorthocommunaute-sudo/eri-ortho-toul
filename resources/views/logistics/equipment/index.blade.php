@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('equipment.create') }}">{{ __('app.new_equipment') }}</a>
</div>

<form class="grid" method="get">
    <label>{{ __('app.category') }}<select name="category"><option value="">{{ __('app.all') }}</option>@foreach(['audio', 'video', 'furniture', 'liturgical', 'IT', 'other'] as $c)<option value="{{ $c }}" @selected(request('category')==$c)>{{ __('app.'.$c) }}</option>@endforeach</select></label>
    <label>{{ __('app.condition') }}<select name="condition"><option value="">{{ __('app.all') }}</option>@foreach(['new', 'good', 'fair', 'poor', 'disposed'] as $c)<option value="{{ $c }}" @selected(request('condition')==$c)>{{ __('app.'.$c) }}</option>@endforeach</select></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.name') }}</th>
            <th>{{ __('app.category') }}</th>
            <th>{{ __('app.condition') }}</th>
            <th>{{ __('app.location') }}</th>
            <th>{{ __('app.purchase_price') }}</th>
            <th>{{ __('app.purchase_date') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($equipment as $eq)
        <tr>
            <td><a href="{{ route('equipment.show', $eq) }}">{{ $eq->reference }}</a></td>
            <td>{{ $eq->name }}</td>
            <td>{{ $eq->category ? __('app.'.$eq->category) : '' }}</td>
            <td><span class="status-badge status-{{ $eq->condition }}">{{ $eq->condition ? __('app.'.$eq->condition) : '' }}</span></td>
            <td>{{ $eq->location ?? '' }}</td>
            <td>{{ $eq->purchase_price ? number_format($eq->purchase_price, 2) . ' €' : '' }}</td>
            <td>{{ $eq->purchase_date ? $eq->purchase_date->format('d/m/Y') : '' }}</td>
        </tr>
        @empty
        <tr><td colspan="7">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $equipment->links() }}</p>
@endsection