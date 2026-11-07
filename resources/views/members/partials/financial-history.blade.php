@php
    $hasPermission = auth()->user()?->hasPermission('members.financial-history.view') ?? false;
    $canViewReceipt = auth()->user()?->hasPermission('finance.view') ?? false;
    $selectedStatus = $selectedStatus ?? null;
@endphp

@if(!$hasPermission)
    <div class="alert alert-warning">
        {{ __('app.no_permission') }}
    </div>
@else
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

    {{-- ========== Cotisation Section ========== --}}
    <div class="fin-section">
        <div class="fin-section-head">
            <div class="fin-section-title">
                <span class="fin-section-icon teal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.monthly_cotisation_history') }}</h2>
                    <p class="muted">{{ __('app.filter') }}: {{ $year ?: __('app.all_years') }} · {{ $selectedStatus ? __('app.'.$selectedStatus) : __('app.all_statuses') }}</p>
                </div>
            </div>
        </div>

        {{-- Cotisation Filter --}}
        <form method="get" class="fin-filters">
            <input type="hidden" name="donation_year" value="{{ $donationYear ?? '' }}">
            <div class="fin-filter-group">
                <label>{{ __('app.year') }}</label>
                <select name="year">
                    <option value="">{{ __('app.all_years') }}</option>
                    @foreach ($cotisationYears as $y)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fin-filter-group">
                <label>{{ __('app.status') }}</label>
                <select name="status">
                    <option value="">{{ __('app.all_statuses') }}</option>
                    <option value="paid" {{ $selectedStatus === 'paid' ? 'selected' : '' }}>{{ __('app.paid') }}</option>
                    <option value="partial" {{ $selectedStatus === 'partial' ? 'selected' : '' }}>{{ __('app.partial') }}</option>
                    <option value="unpaid" {{ $selectedStatus === 'unpaid' ? 'selected' : '' }}>{{ __('app.unpaid') }}</option>
                </select>
            </div>
            <button type="submit" class="button">{{ __('app.filter') }}</button>
            @if($year || $selectedStatus)
                <a href="{{ route('members.financial-history', ['member' => $member, 'donation_year' => $donationYear]) }}" class="link">{{ __('app.all') }}</a>
            @endif
        </form>

        {{-- Cotisation Table --}}
        <div class="scroll">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('app.month') }}</th>
                        <th class="text-right">{{ __('app.expected_contribution') }}</th>
                        <th class="text-right">{{ __('app.paid_contribution') }}</th>
                        <th class="text-right">{{ __('app.total_outstanding') }}</th>
                        <th>{{ __('app.payment_date') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th class="text-right">{{ __('app.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($monthlyHistory as $row)
                        <tr>
                            <td>{{ $row['month'] }} / {{ $row['year'] }}</td>
                            <td class="text-right">{{ number_format($row['expected_amount'], 2, ',', ' ') }} €</td>
                            <td class="text-right">{{ number_format($row['paid_amount'], 2, ',', ' ') }} €</td>
                            <td class="text-right">
                                {{ number_format(max(0, $row['expected_amount'] - $row['paid_amount']), 2, ',', ' ') }} €
                            </td>
                            <td>{{ $row['payment_date']?->format('d/m/Y') ?? '—' }}</td>
                            <td>
                                <span class="status-badge status-{{ $row['status'] }}">{{ $row['status_label'] }}</span>
                            </td>
                            <td class="text-right">
                                <div class="actions" style="justify-content: flex-end; gap: 4px;">
                                    @if($row['latest_receipt'] && $canViewReceipt)
                                        <a href="{{ route('receipts.show', $row['latest_receipt']) }}" class="link" title="{{ __('app.view_receipt') }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty">{{ __('app.no_financial_history') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========== Donation Section ========== --}}
    <div class="fin-section">
        <div class="fin-section-head">
            <div class="fin-section-title">
                <span class="fin-section-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 3v18M16.5 7.5C16.5 5.6 14.4 4 12 4S7.5 5.6 7.5 7.5 9.6 11 12 11s4.5 1.6 4.5 3.5S14.4 18 12 18s-4.5-1.6-4.5-3.5"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.donation_history_year') }}</h2>
                    <p class="muted">{{ __('app.filter') }}: {{ $donationYear ?? now()->year }}</p>
                </div>
            </div>
        </div>

        {{-- Donation Filter --}}
        <form method="get" class="fin-filters">
            <input type="hidden" name="year" value="{{ $year ?? '' }}">
            <input type="hidden" name="status" value="{{ $selectedStatus ?? '' }}">
            <div class="fin-filter-group">
                <label>{{ __('app.year') }}</label>
                <select name="donation_year">
                    <option value="">{{ __('app.all_years') }}</option>
                    @foreach ($donationYears as $y)
                        <option value="{{ $y }}" {{ ($donationYear ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="button">{{ __('app.filter') }}</button>
            @if($donationYear)
                <a href="{{ route('members.financial-history', ['member' => $member, 'year' => $year, 'status' => $selectedStatus]) }}" class="link">{{ __('app.all') }}</a>
            @endif
        </form>

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

        {{-- Donation Table --}}
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
                        <th>{{ __('app.receipt') }}</th>
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
                            <td>
                                @if($donation['financial_reference'])
                                    <a href="{{ route('financial-entries.show', $donation['financial_reference']) }}" class="link" title="{{ __('app.view_receipt') }}" target="_blank">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 3H6.5A1.5 1.5 0 0 0 5 4.5v15A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5V8l-5-5Z"/><path d="M14 3v5h5M8.5 12h7M8.5 16h5"/></svg>
                                    </a>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="actions" style="justify-content: flex-end; gap: 4px;">
                                    @if($donation['type'] === 'offrande' && $canViewReceipt)
                                        <a href="{{ route('offrandes.show', $donation['id']) }}" class="link" title="{{ __('app.view_details') }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    @elseif($donation['type'] === 'donation' && $canViewReceipt)
                                        <a href="{{ route('donations.show', $donation['id']) }}" class="link" title="{{ __('app.view_details') }}">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="empty">{{ __('app.no_donations_year') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Category Breakdown --}}
        @if(!empty($donationSummary['by_category']))
        <div class="fin-breakdown">
            <h4>{{ __('app.donation_by_category') }}</h4>
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('app.donation_category') }}</th>
                            <th class="text-right">{{ __('app.donation_count') }}</th>
                            <th class="text-right">{{ __('app.total_amount') }} (€)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($donationSummary['by_category'] as $category => $data)
                            <tr>
                                <td>{{ $category }}</td>
                                <td class="text-right">{{ $data['count'] }}</td>
                                <td class="text-right">{{ number_format($data['total'], 2, ',', ' ') }} €</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
@endif

<style>
.fin-section {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}
.fin-section:first-of-type {
    margin-top: 1.5rem;
    padding-top: 0;
    border-top: none;
}

.fin-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.fin-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
}

.fin-section-title h2 {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: var(--ink);
}
.fin-section-title .muted {
    margin: 2px 0 0;
    font-size: 11px;
}

.fin-section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    flex-shrink: 0;
}
.fin-section-icon svg { width: 18px; height: 18px; }
.fin-section-icon.teal { background: #e3f4ef; color: #147b78; }
.fin-section-icon.green { background: #e6f7ef; color: #218b67; }

.fin-filters {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 1.25rem;
    padding: 12px 14px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
}

.fin-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.fin-filter-group label {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--muted);
}
.fin-filter-group select {
    padding: 7px 10px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: #fff;
    color: var(--ink);
    font-size: 0.875rem;
    min-width: 130px;
}
.fin-filter-group select:focus {
    outline: none;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(20, 123, 120, 0.12);
}

.fin-breakdown {
    margin-top: 1.5rem;
    padding-top: 1.25rem;
    border-top: 1px dashed var(--border);
}
.fin-breakdown h4 {
    margin: 0 0 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--ink);
}
</style>
