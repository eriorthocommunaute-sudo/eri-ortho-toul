@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <a class="button" href="{{ route('contribution-notices.generate', $contribution->id ?? 0) }}" style="background: #7c3aed;">{{ __('app.generate_notices') }}</a>
</div>

<form class="grid" method="get">
    <label>{{ __('app.status') }}<select name="status"><option value="">{{ __('app.all') }}</option>@foreach(['generated', 'sent', 'paid', 'overdue', 'cancelled'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ __('app.'.$s) }}</option>@endforeach</select></label>
    <label>{{ __('app.member') }}<select name="member_id"><option value="">{{ __('app.all') }}</option>@foreach($members as $m)<option value="{{ $m->id }}" @selected(request('member_id')==$m->id)>{{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}</option>@endforeach</select></label>
    <label>{{ __('app.contribution') }}<select name="contribution_id"><option value="">{{ __('app.all') }}</option>@foreach($contributions as $c)<option value="{{ $c->id }}" @selected(request('contribution_id')==$c->id)>{{ $c->reference }}</option>@endforeach</select></label>
    <p><button>{{ __('app.filter') }}</button></p>
</form>

<table>
    <thead>
        <tr>
            <th>{{ __('app.reference') }}</th>
            <th>{{ __('app.member') }}</th>
            <th>{{ __('app.contribution') }}</th>
            <th>{{ __('app.notice_date') }}</th>
            <th>{{ __('app.due_date') }}</th>
            <th>{{ __('app.amount') }}</th>
            <th>{{ __('app.status') }}</th>
            <th>{{ __('app.actions') }}</th>
        </tr>
    </thead>
    <tbody>
        @forelse($notices as $notice)
        <tr>
            <td><a href="{{ route('contribution-notices.show', $notice) }}">{{ $notice->reference }}</a></td>
            <td>{{ $notice->member->member_code }} - {{ $notice->member->given_name }} {{ $notice->member->family_name }}</td>
            <td>{{ $notice->contribution->reference }}</td>
            <td>{{ $notice->notice_date->format('d/m/Y') }}</td>
            <td>{{ $notice->due_date->format('d/m/Y') }}</td>
            <td>{{ number_format($notice->amount, 2) }} €</td>
            <td><span class="status-badge status-{{ $notice->status }}">{{ __('app.'.$notice->status) }}</span></td>
            <td>
                @if($notice->status === 'generated' && auth()->user()->hasPermission('secretary.manage'))
                <form method="post" action="{{ route('contribution-notices.send', $notice) }}" style="display: inline;">@csrf<button type="submit" class="button" style="font-size: .8rem;">{{ __('app.send') }}</button></form>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="8">{{ __('app.no_data') }}</td></tr>
        @endforelse
    </tbody>
</table>
<p>{{ $notices->links() }}</p>
@endsection