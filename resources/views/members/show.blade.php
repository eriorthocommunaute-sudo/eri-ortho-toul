@extends('layouts.app')
@section('content')

{{-- Member Header --}}
<div class="panel member-hero">
    <div class="member-hero-main">
        <div class="member-avatar">
            {{ strtoupper(mb_substr($member->given_name ?? '', 0, 1)) }}{{ strtoupper(mb_substr($member->family_name ?? '', 0, 1)) }}
        </div>
        <div class="member-hero-info">
            <h1>{{ $member->given_name }} {{ $member->family_name }}</h1>
            <div class="member-meta">
                <span class="member-code">{{ __('app.reference') }}: {{ $member->member_code }}</span>
                <span class="status-badge status-{{ $member->status }}">{{ __('app.'.$member->status) }}</span>
                @if($member->joined_at)
                    <span class="muted">{{ __('app.joined_at') }}: {{ $member->joined_at->format('d/m/Y') }}</span>
                @endif
            </div>
        </div>
    </div>
    <div class="member-hero-actions">
        @if(auth()->user()->hasPermission('finance.record'))
            <a class="button" href="{{ route('payments.create', ['member_id' => $member->id]) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                {{ __('app.record_payment') }}
            </a>
        @endif
        @if(auth()->user()->hasPermission('members.update'))
            <a class="button secondary" href="{{ route('members.edit', $member) }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                {{ __('app.edit') }}
            </a>
        @endif
    </div>
</div>

{{-- Contact Information --}}
<div class="panel" style="margin-bottom: 1.25rem;">
    <div class="panelhead">
        <h2>{{ __('app.contact_information') }}</h2>
    </div>
    <div class="contact-grid">
        @if($member->display_name)
            <div class="contact-item">
                <span class="contact-label">{{ __('app.display_name') }}</span>
                <span class="contact-value">{{ $member->display_name }}</span>
            </div>
        @endif
        @if($member->phone)
            <div class="contact-item">
                <span class="contact-label">{{ __('app.phone') }}</span>
                <span class="contact-value">{{ $member->phone }}</span>
            </div>
        @endif
        @if($member->email)
            <div class="contact-item">
                <span class="contact-label">{{ __('app.email') }}</span>
                <span class="contact-value">{{ $member->email }}</span>
            </div>
        @endif
        @if($member->address)
            <div class="contact-item contact-item-full">
                <span class="contact-label">{{ __('app.address') }}</span>
                <span class="contact-value" style="white-space:pre-line">{{ $member->address }}</span>
            </div>
        @endif
    </div>
</div>

{{-- Financial History Section --}}
@if(auth()->user()->hasPermission('members.financial-history.view'))
    <div class="panel" style="margin-bottom: 1.25rem;">
        <div class="panelhead">
            <div>
                <h2>{{ __('app.financial_history') }}</h2>
                <p class="muted">{{ __('app.financial_history_sub') }}</p>
            </div>
        </div>

        {{-- Financial Summary Cards --}}
        <div class="stats stats-3" role="region" aria-label="{{ __('app.financial_summary') }}">
            <article>
                <small>{{ __('app.this_year_contributions_paid') }}</small>
                <strong>{{ number_format($summary['this_year_contributions_paid'], 2, ',', ' ') }} <small>&euro;</small></strong>
            </article>
            <article>
                <small>{{ __('app.this_year_offrandes_total') }}</small>
                <strong>{{ number_format($summary['this_year_offrandes_total'], 2, ',', ' ') }} <small>&euro;</small></strong>
            </article>
            <article class="kpi-green">
                <small>{{ __('app.this_year_total_paid') }}</small>
                <strong>{{ number_format($summary['this_year_total_paid'], 2, ',', ' ') }} <small>&euro;</small></strong>
            </article>
            <article class="kpi-orange">
                <small>{{ __('app.this_year_outstanding') }}</small>
                <strong>{{ number_format($summary['this_year_outstanding'], 2, ',', ' ') }} <small>&euro;</small></strong>
            </article>
            <article>
                <small>{{ __('app.lifetime_total_paid') }}</small>
                <strong>{{ number_format($summary['lifetime_total_paid'], 2, ',', ' ') }} <small>&euro;</small></strong>
            </article>
            <article class="kpi-orange">
                <small>{{ __('app.lifetime_outstanding') }}</small>
                <strong>{{ number_format($summary['lifetime_outstanding'], 2, ',', ' ') }} <small>&euro;</small></strong>
            </article>
        </div>

        {{-- Recent 12 months cotisation table --}}
        <div class="subsection">
            <h3>{{ __('app.monthly_cotisation_history') }}</h3>
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('app.year') }}</th>
                            <th>{{ __('app.month') }}</th>
                            <th class="text-right">{{ __('app.expected_contribution') }}</th>
                            <th class="text-right">{{ __('app.paid_contribution') }}</th>
                            <th class="text-right">{{ __('app.total_outstanding') }}</th>
                            <th>{{ __('app.payment_date') }}</th>
                            <th>{{ __('app.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($monthlyHistory as $row)
                            <tr>
                                <td>{{ $row['year'] }}</td>
                                <td>{{ $row['month'] }}</td>
                                <td class="text-right">{{ number_format($row['expected_amount'], 2, ',', ' ') }} €</td>
                                <td class="text-right">{{ number_format($row['paid_amount'], 2, ',', ' ') }} €</td>
                                <td class="text-right">{{ number_format(max(0, $row['expected_amount'] - $row['paid_amount']), 2, ',', ' ') }} €</td>
                                <td>{{ $row['payment_date']?->format('d/m/Y') ?? '—' }}</td>
                                <td><span class="status-badge status-{{ $row['status'] }}">{{ $row['status_label'] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="empty">{{ __('app.no_financial_history') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Donation History Section --}}
    <div class="panel" style="margin-bottom: 1.25rem;">
        <div class="panelhead">
            <div>
                <h2>{{ __('app.donation_history_year') }}</h2>
            </div>
        </div>

        {{-- Donation Summary Cards --}}
        <div class="stats stats-3" role="region" aria-label="{{ __('app.donation_summary') }}">
            <article class="kpi-green">
                <small>{{ __('app.total_donations_year') }}</small>
                <strong>{{ number_format($donationSummary['total_amount'], 2, ',', ' ') }} <small>&euro;</small></strong>
            </article>
            <article>
                <small>{{ __('app.donation_count') }}</small>
                <strong>{{ $donationSummary['count'] }}</strong>
            </article>
            <article>
                <small>{{ __('app.average_donation') }}</small>
                <strong>{{ number_format($donationSummary['average_amount'], 2, ',', ' ') }} <small>&euro;</small></strong>
            </article>
        </div>

        {{-- Recent donations table (last 10) --}}
        <div class="subsection">
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('app.donation_reference') }}</th>
                            <th>{{ __('app.donation_date') }}</th>
                            <th>{{ __('app.donor') }}</th>
                            <th>{{ __('app.donation_category') }}</th>
                            <th class="text-right">{{ __('app.amount') }} (€)</th>
                            <th>{{ __('app.payment_method') }}</th>
                            <th>{{ __('app.comment') }}</th>
                            <th class="text-right">{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($donationHistory as $donation)
                            <tr>
                                <td><span class="ref">{{ $donation['reference'] }}</span></td>
                                <td>{{ $donation['date']?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $donation['donor'] }}</td>
                                <td>{{ $donation['category'] }}</td>
                                <td class="text-right">{{ number_format($donation['amount'], 2, ',', ' ') }} €</td>
                                <td>{{ $donation['payment_method'] }}</td>
                                <td>{{ $donation['notes'] ?? '—' }}</td>
                                <td class="text-right">
                                    <div class="actions" style="justify-content: flex-end; gap: 4px;">
                                        @if($donation['type'] === 'offrande')
                                            <a href="{{ route('offrandes.show', $donation['id']) }}" class="link" title="{{ __('app.view_details') }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                        @elseif($donation['type'] === 'donation')
                                            <a href="{{ route('donations.show', $donation['id']) }}" class="link" title="{{ __('app.view_details') }}">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty">{{ __('app.no_donations_year') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 1.25rem; text-align: right;">
            <a href="{{ route('members.financial-history', $member) }}" class="button outline">
                {{ __('app.view_all') }}
            </a>
        </div>
    </div>
@endif

<style>
.member-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.25rem;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
}

.member-hero-main {
    display: flex;
    align-items: center;
    gap: 1.125rem;
    min-width: 0;
}

.member-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--teal), var(--teal-dark));
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 700;
    flex-shrink: 0;
}

.member-hero-info h1 {
    margin: 0 0 6px;
    font-size: 1.375rem;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.2;
}

.member-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    font-size: 0.8125rem;
}

.member-code {
    font-family: 'JetBrains Mono', monospace;
    font-weight: 600;
    color: var(--teal);
}

.member-hero-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.contact-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.contact-item-full {
    grid-column: 1 / -1;
}

.contact-label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
}

.contact-value {
    font-size: 0.9375rem;
    color: var(--ink);
}

.subsection {
    margin-top: 1.5rem;
}

.subsection h3 {
    margin: 0 0 0.75rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--ink);
}

@media (max-width: 640px) {
    .member-hero {
        flex-direction: column;
        align-items: flex-start;
    }
    .member-hero-actions {
        width: 100%;
    }
    .member-hero-actions .button {
        flex: 1;
        justify-content: center;
    }
}
</style>
@endsection
