@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $contribution->reference }} - {{ $contribution->month }}/{{ $contribution->year }}</h1>
    <div style="margin-left: auto;">
        @if($contribution->status === 'open')
        <form method="post" action="{{ route('contributions.update', $contribution) }}" style="display: inline;">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="closed">
            <button type="submit" class="button" style="background: #b45309;">{{ __('app.close_contribution') }}</button>
        </form>
        @endif
        @if(auth()->user()->hasPermission('finance.record'))
        <a class="button" href="{{ route('payments.create', ['contribution_id' => $contribution->id]) }}">{{ __('app.record_payment') }}</a>
        @endif
        @if(auth()->user()->hasPermission('secretary.manage'))
            <a class="button" href="{{ route('contribution-notices.generate', ['contribution' => $contribution->id]) }}">{{ __('app.generate_notices') }}</a>
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.monthly_amount') }}</strong><br>
        {{ number_format($contribution->amount_per_member, 2) }} €
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.expected') }}</strong><br>
        {{ number_format($contribution->total_expected, 2) }} € ({{ $contribution->expected_count }} {{ __('app.members') }})
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.received') }}</strong><br>
        {{ number_format($contribution->total_received, 2) }} € ({{ $contribution->received_count }} {{ __('app.members') }})
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.progress') }}</strong><br>
        {{ $contribution->progress_percent }}%
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.status') }}</strong><br>
        <span class="status-badge status-{{ $contribution->status }}">{{ __('app.'.$contribution->status) }}</span>
    </div>
</div>

<h2>{{ __('app.receipts') }}</h2>
@if($contribution->receipts->isEmpty())
    <p>{{ __('app.no_receipts') }}</p>
@else
    <table>
        <thead>
            <tr>
                <th>{{ __('app.reference') }}</th>
                <th>{{ __('app.member') }}</th>
                <th>{{ __('app.payment_date') }}</th>
                <th>{{ __('app.amount') }}</th>
                <th>{{ __('app.payment_method') }}</th>
                <th>{{ __('app.status') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($contribution->receipts as $receipt)
            <tr>
                <td><a href="{{ route('receipts.show', $receipt) }}">{{ $receipt->reference }}</a></td>
                <td>{{ $receipt->member->given_name }} {{ $receipt->member->family_name }}</td>
                <td>{{ $receipt->payment_date->format('d/m/Y') }}</td>
                <td>{{ number_format($receipt->amount, 2) }} €</td>
                <td>{{ __('app.'.$receipt->payment_method) }}</td>
                <td><span class="status-badge status-{{ $receipt->status }}">{{ __('app.'.$receipt->status) }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if($unpaidMembers->isNotEmpty())
<h2 style="margin-top: 2rem;">{{ __('app.unpaid_members') }}</h2>
<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.name') }}</th>
            <th>{{ __('app.action') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($unpaidMembers as $member)
        <tr>
            <td>{{ $member->member_code }}</td>
            <td>{{ $member->given_name }} {{ $member->family_name }}</td>
            <td>
                <a class="button" href="{{ route('payments.create', ['member_id' => $member->id, 'contribution_id' => $contribution->id]) }}" style="font-size: .8rem;">{{ __('app.record_payment') }}</a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif
@endsection