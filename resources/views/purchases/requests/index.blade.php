@extends('layouts.app')
@section('content')
<div class="mb-4" style="display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap;">
    <a class="button" href="{{ route('purchase-requests.create') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
        {{ __('app.new_purchase_request') }}
    </a>

    <div style="display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: .5rem .75rem;">
        <span style="font-size: 9.5px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #16817b;">{{ __('app.printing') }} / ምትሕጻብ</span>

        <a class="button outline" href="{{ route('purchase-requests.print', request()->query()) }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7" rx="1"/></svg>
            {{ __('app.print_register_da') }}
        </a>

        <form method="get" action="{{ route('purchase-requests.form-print') }}" style="display: flex; align-items: center; gap: .45rem;">
            <label for="da-form-count" style="margin: 0; font-size: 12px; font-weight: 600; color: #5f7376;">{{ __('app.form_print_da') }}</label>
            <input id="da-form-count" type="number" name="count" value="{{ request('count', 10) }}" min="1" max="200"
                title="{{ __('app.form_print_count_hint') }}"
                style="width: 64px; padding: .5rem .4rem; border: 1px solid #dfe8e9; border-radius: 8px; font-size: 12px; text-align: center;">
            <button type="submit" class="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7" rx="1"/></svg>
                {{ __('app.print') }}
            </button>
        </form>
    </div>
</div>

<form class="grid" method="get">
    <label>{{ __('app.status') }}<select name="status"><option value="">{{ __('app.all') }}</option>@foreach($statuses as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ __('app.'.$s) }}</option>@endforeach</select></label>
    <label>{{ __('app.category') }}<select name="category"><option value="">{{ __('app.all') }}</option>@foreach($categories as $c)<option value="{{ $c }}" @selected(request('category')==$c)>{{ __('app.'.$c) }}</option>@endforeach</select></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.title') }}</th>
            <th>{{ __('app.category') }}</th>
            <th>{{ __('app.estimated_amount') }}</th>
            <th>{{ __('app.priority') }}</th>
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.requested_by') }}</th>
            <th>{{ __('app.approved_by') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($requests as $req)
        <tr>
            <td><a href="{{ route('purchase-requests.show', $req) }}">{{ $req->reference }}</a></td>
            <td>{{ $req->title }}</td>
            <td>{{ $req->category ? __('app.'.$req->category) : '' }}</td>
            <td>{{ number_format($req->estimated_amount, 2) }} €</td>
            <td><span class="status-badge">{{ __('app.'.$req->priority) }}</span></td>
            <td><span class="status-badge status-{{ $req->status }}">{{ __('app.'.$req->status) }}</span></td>
            <td>{{ $req->requester->name ?? '' }}</td>
            <td>{{ $req->approver->name ?? '' }}</td>
        </tr>
        @empty
        <tr><td colspan="8">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $requests->links() }}</p>
@endsection
