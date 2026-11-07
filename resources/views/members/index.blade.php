@extends('layouts.app')
@section('content')
@php
    $roles = auth()->user()?->roles ?? collect();
    $permissions = $roles->flatMap(fn ($role) => $role->permissions())->unique();
    $can = fn (string $permission) => $permissions->contains($permission);
@endphp

<section class="panel tablepanel">
    <div class="panelhead tabletop">
        <form class="tools" method="get">
            <input class="tool-input" name="q" value="{{ request('q') }}" placeholder="{{ __('app.search') }}">
            <select class="tool-select" name="status">
                <option value="">{{ __('app.all') }}</option>
                @foreach (['active', 'inactive', 'former'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ __('app.'.$status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="outline">{{ __('app.filter') }}</button>
            @if (request()->hasAny(['q', 'status']))
                <a class="link" href="{{ route('members.index') }}">{{ __('app.all') }}</a>
            @endif
        </form>
        <div class="actions">
            @if ($can('members.create'))
                <a class="button" href="{{ route('members.create') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    {{ __('app.add_member') }}
                </a>
            @endif
            <a class="button outline" href="{{ route('members.print', request()->only(['q', 'status'])) }}" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7"/></svg>
                {{ __('app.print') }}
            </a>
        </div>
    </div>

    <div class="scroll">
        <table>
            <thead>
                <tr>
                    <th>{{ __('app.name') }}</th>
                    <th>{{ __('app.reference') }}</th>
                    <th>{{ __('app.phone') }}</th>
                    <th>{{ __('app.joined_at') }}</th>
                    <th>{{ __('app.status') }}</th>
                    <th class="text-right">{{ __('app.actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td>
                            <div class="person">
                                <span class="avatar">{{ mb_strtoupper(mb_substr($member->given_name, 0, 1).mb_substr($member->family_name, 0, 1)) }}</span>
                                <div>
                                    <b>{{ $member->given_name }} {{ $member->family_name }}</b>
                                    <small>{{ $member->display_name ?: $member->email ?: __('app.not_specified') }}</small>
                                </div>
                            </div>
                        </td>
                        <td><span class="ref">{{ $member->member_code }}</span></td>
                        <td>{{ $member->phone ?: '—' }}</td>
                        <td>{{ $member->joined_at?->format('d/m/Y') ?? '—' }}</td>
                        <td><span class="status-badge status-{{ $member->status }}">{{ __('app.'.$member->status) }}</span></td>
                        <td class="text-right">
                            <div class="actions" style="justify-content: flex-end">
                                <a class="link" href="{{ route('members.show', $member) }}">{{ __('app.view') }}</a>
                                @if ($can('members.update'))
                                    <a class="link" href="{{ route('members.edit', $member) }}">{{ __('app.edit') }}</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="empty">{{ __('app.no_members') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="tablefoot">
        <span>{{ __('app.total_members', ['count' => $members->total()]) }}</span>
        <span>{{ $members->firstItem() ?? 0 }}–{{ $members->lastItem() ?? 0 }}</span>
    </div>
</section>

<div class="mt-4">{{ $members->links() }}</div>
@endsection
