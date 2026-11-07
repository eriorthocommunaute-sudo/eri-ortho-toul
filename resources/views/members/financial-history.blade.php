@extends('layouts.app')

@section('content')
<div class="fin-history-page">
    {{-- Page Header --}}
    <div class="fin-history-hero">
        <div class="fin-history-hero-main">
            <a href="{{ route('members.show', $member) }}" class="back-link" title="{{ __('app.back') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1>{{ __('app.financial_history') }}</h1>
                <div class="fin-history-member">
                    <span class="fin-history-name">{{ $member->given_name }} {{ $member->family_name }}</span>
                    <span class="member-code">{{ $member->member_code }}</span>
                    @if($member->joined_at)
                        <span class="muted">{{ __('app.joined_at') }}: {{ $member->joined_at->format('d/m/Y') }}</span>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('members.financial-history.export', ['member' => $member, 'year' => $year, 'status' => $selectedStatus, 'donation_year' => $donationYear]) }}" class="button outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
            {{ __('app.export_csv') }}
        </a>
    </div>

    <div class="fin-history-body">
        @include('members.partials.financial-history', [
            'member' => $member,
            'monthlyHistory' => $monthlyHistory,
            'summary' => $summary,
            'donationHistory' => $donationHistory,
            'donationSummary' => $donationSummary,
            'cotisationYears' => $cotisationYears,
            'donationYears' => $donationYears,
            'year' => $year,
            'selectedStatus' => $selectedStatus,
            'donationYear' => $donationYear
        ])
    </div>
</div>

<style>
.fin-history-page {
    max-width: 1500px;
    margin: 0 auto;
}

.fin-history-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.fin-history-hero-main {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    min-width: 0;
}

.back-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--card);
    color: var(--muted);
    flex-shrink: 0;
    transition: all 0.15s;
}
.back-link:hover {
    background: var(--bg);
    color: var(--ink);
    text-decoration: none;
}
.back-link svg {
    width: 18px;
    height: 18px;
}

.fin-history-hero h1 {
    margin: 0 0 4px;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--ink);
}

.fin-history-member {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    font-size: 0.8125rem;
}

.fin-history-name {
    font-weight: 600;
    color: var(--ink);
}

.member-code {
    font-family: 'JetBrains Mono', monospace;
    font-weight: 600;
    color: var(--teal);
}

.fin-history-body {
    background: #fff;
    border: 1px solid #e8eeee;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 21px;
}
</style>
@endsection
