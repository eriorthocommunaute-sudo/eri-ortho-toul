@extends('layouts.app')

@section('content')
@php
    $months = \App\Services\FinancialRegisterGenerator::MONTHS_FR;
    $memberCount = $members->count();
    $expectedTotal = 0.0;
    $memberExpectedTotals = [];

    $collected = 0.0;
    $monthCollected = array_fill(1, 12, 0.0);
    $monthPaid = array_fill(1, 12, 0);
    $statusCount = ['paid' => 0, 'partially_paid' => 0, 'unpaid' => 0, 'covered_in_advance' => 0];
    $memberTotals = [];
    $settledMembers = 0;

    foreach ($matrix as $memberId => $memberMatrix) {
        $total = 0.0;
        $settled = true;
        $memberExpected = 0.0;
        foreach ($memberMatrix as $m => $cell) {
            $memberExpected += $cell['expected_amount'] ?? 10.00;
            $total += $cell['allocated_amount'];
            $collected += $cell['allocated_amount'];
            $monthCollected[$m] += $cell['allocated_amount'];
            if (in_array($cell['status'], ['paid', 'covered_in_advance'], true)) {
                $monthPaid[$m]++;
            } elseif (in_array($cell['status'], ['partially_paid', 'unpaid'], true)) {
                $settled = false;
            }
            if (isset($statusCount[$cell['status']])) {
                $statusCount[$cell['status']]++;
            }
        }
        $memberTotals[$memberId] = $total;
        $memberExpectedTotals[$memberId] = $memberExpected;
        $expectedTotal += $memberExpected;
        if ($settled) {
            $settledMembers++;
        }
    }

    $outstanding = max(0, $expectedTotal - $collected);
    $progress = $expectedTotal > 0 ? min(100, round(($collected / $expectedTotal) * 100)) : 0;

    $legendStatuses = [
        ['key' => 'payment', 'icon' => '€', 'label' => 'Montant reçu'],
        ['key' => 'covered_in_advance', 'icon' => 'PA', 'label' => 'Payé à l’avance'],
        ['key' => 'partially_paid', 'icon' => '½', 'label' => __('app.partially_paid_label')],
        ['key' => 'zero', 'icon' => '0', 'label' => 'Aucun paiement'],
    ];
@endphp

<div class="mx-print-header">
    <img class="cross-img" src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="Cross">
    <div class="l1">Communauté Orthodoxe Érythréenne de Toulouse et de ses environs</div>
    <div class="l2">ኦርቶዶክስ ተዋሕዶ ቤተ ክርስቲያን ኤርትራ ባእታ ማርያም ቱሉዝ ፈረንሳ</div>
    <div class="l3">
        @if ($autoPrint ?? false)
            {{ __('app.matrix_membership_contributions') }}, {{ \App\Services\FinancialRegisterGenerator::MONTHS_FR[$printMonthNumber] }} {{ $year }} / ወርሓዊ ክፍሊት {{ \App\Services\FinancialRegisterGenerator::MONTHS_TI[$printMonthNumber] }} {{ $year }}
        @else
            {{ __('app.matrix_membership_contributions') }}, {{ $year }}
        @endif
    </div>
    <div class="l4">{{ $memberCount }} membres actifs · DOCUMENT ASSOCIATIF / ሰነድ ማሕበር — {{ now()->format('d/m/Y') }}</div>
</div>

<div class="pagebar mx-hide-print">
    <div>
        <p class="eyebrow">{{ __('app.contributions') }} · {{ $year }}</p>
        <h1 style="margin: 0 0 7px; font-size: 23px; letter-spacing: -.5px;">{{ __('app.matrix_membership_contributions') }}</h1>
        <p class="muted">{{ __('app.matrix_subtitle') }}</p>
    </div>
    <div class="actions">
        @if (auth()->user()?->hasPermission('finance.record'))
            <a class="button" href="{{ route('payments.create') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                {{ __('app.record_payment') }}
            </a>
        @endif
        <a class="button outline" href="{{ route('contributions.matrix.export.pdf', request()->query()) }}">PDF</a>
        <a class="button outline" href="{{ route('contributions.matrix.export.excel', request()->query()) }}">Excel</a>
        <button class="button outline" type="button" onclick="window.print()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4v-6h16v6h-2M8 14h8v7H8z"/></svg>
            {{ __('app.print') }}
        </button>
        <a class="button outline" href="{{ route('contributions.index') }}">{{ __('app.back_to_contributions') }}</a>
    </div>
</div>

<section class="stats kpi mx-hide-print">
    <article>
        <div class="kpi-head">
            <span class="kpi-ico teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6M18 8v6M15 11h6"/></svg>
            </span>
            <span class="kpi-label">{{ __('app.members_active') }}</span>
        </div>
        <strong>{{ number_format($memberCount, 0, ',', ' ') }}</strong>
        <small>{{ __('app.total_members', ['count' => $memberCount]) }}</small>
    </article>

    <article class="kpi-green">
        <div class="kpi-head">
            <span class="kpi-ico green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="2.5" y="6" width="19" height="13" rx="2.5"/><path d="M2.5 10h19M16 15h2"/></svg>
            </span>
            <span class="kpi-label">{{ __('app.contributions_covered_year') }}</span>
        </div>
        <strong>{{ number_format($collected, 2, ',', ' ') }} <small>&euro;</small></strong>
        <div class="progress"><i style="width: {{ $progress }}%"></i></div>
        <small>{{ $progress }}% {{ __('app.of_target') }} ({{ number_format($expectedTotal, 2, ',', ' ') }} &euro;)</small>
    </article>

    <article class="kpi-orange">
        <div class="kpi-head">
            <span class="kpi-ico orange">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 3v18M16.5 7.5C16.5 5.6 14.4 4 12 4S7.5 5.6 7.5 7.5 9.6 11 12 11s4.5 1.6 4.5 3.5S14.4 18 12 18s-4.5-1.6-4.5-3.5"/></svg>
            </span>
            <span class="kpi-label">{{ __('app.outstanding') }}</span>
        </div>
        <strong>{{ number_format($outstanding, 2, ',', ' ') }} <small>&euro;</small></strong>
        <small>{{ __('app.months') }} : {{ number_format($statusCount['paid'] + $statusCount['partially_paid'] + $statusCount['covered_in_advance'], 0) }} / {{ number_format($memberCount * 12, 0) }}</small>
    </article>

    <article class="kpi-blue">
        <div class="kpi-head">
            <span class="kpi-ico blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
            </span>
            <span class="kpi-label">{{ __('app.members_settled') }}</span>
        </div>
        <strong>{{ number_format($settledMembers, 0, ',', ' ') }}</strong>
        <small>{{ __('app.of_target') }} {{ number_format($memberCount, 0, ',', ' ') }}</small>
    </article>
</section>

<section class="filterbar mx-hide-print">
    <form method="get" action="{{ route('contributions.matrix') }}">
        <label style="flex: 1 1 240px;">
            {{ __('app.search') }}
            <input type="search" id="mxSearch" placeholder="{{ __('app.search_placeholder') }}" autocomplete="off">
        </label>
        <label>
            {{ __('app.year') }}
            <select name="year" class="tool-select" onchange="this.form.submit()">
                @foreach ($yearOptions as $y)
                    <option value="{{ $y }}" @selected($y === (int) $year)>{{ $y }}</option>
                @endforeach
            </select>
        </label>
        <label>
            {{ __('app.month') }}
            <select name="month" class="tool-select">
                @foreach ($monthOptions as $monthNumber => $monthName)
                    <option value="{{ $monthNumber }}" @selected($monthNumber === (int) request('month', now()->format('n')))>{{ $monthName }}</option>
                @endforeach
            </select>
        </label>
        <div class="filter-actions">
            <button type="submit" class="button">{{ __('app.filter') }}</button>
            <button type="submit" name="print_month" value="1" formtarget="_blank" class="button outline">{{ __('app.print_monthly_matrix_with_data') }}</button>
            <button type="submit" name="blank" value="1" formtarget="_blank" class="button outline">{{ __('app.print_blank_monthly_matrix') }}</button>
            <a class="button outline" href="{{ route('contributions.index') }}">{{ __('app.back_to_list') }}</a>
        </div>
    </form>
</section>

<section class="panel tablepanel">
    <div class="scroll mx-wrap">
        <table class="table-grid mx-table" id="mxTable">
            <thead>
                <tr>
                    <th class="mx-c-code mx-c-sticky">{{ __('app.member_id') }}</th>
                    <th class="mx-c-name mx-c-sticky">{{ __('app.member_name') }}</th>
                    @foreach (range(1, 12) as $m)
                        <th title="{{ $months[$m] }} · {{ $monthPaid[$m] }}/{{ $memberCount }}">
                            {{ mb_substr($months[$m], 0, 3) }}<small class="mx-th-count">{{ $monthPaid[$m] }}/{{ $memberCount }}</small>
                        </th>
                    @endforeach
                    <th class="text-right mx-c-total">Total versé (€)</th>
                    <th class="text-right mx-c-remaining">Reste à payer</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    @php
                        $memberMatrix = $matrix[$member->id] ?? [];
                        $total = $memberTotals[$member->id] ?? 0.0;
                        $initials = mb_strtoupper(mb_substr($member->given_name, 0, 1) . mb_substr($member->family_name, 0, 1));
                        $searchText = mb_strtolower($member->member_code . ' ' . $member->given_name . ' ' . $member->family_name);
                        $memberExpected = $memberExpectedTotals[$member->id] ?? 0;
                        $memberProgress = $memberExpected > 0 ? min(100, round(($total / $memberExpected) * 100)) : 0;
                    @endphp
                    <tr data-search="{{ $searchText }}">
                        <td class="mx-c-code mx-c-sticky"><span class="ref">{{ $member->member_code }}</span></td>
                        <td class="mx-c-name mx-c-sticky">
                            <div class="person">
                                <span class="mx-avatar">{{ $initials }}</span>
                                <span>
                                    <b>{{ $member->given_name }} {{ $member->family_name }}</b>
                                    <span class="mx-mini-progress"><i style="width: {{ $memberProgress }}%"></i></span>
                                </span>
                            </div>
                        </td>
                        @foreach (range(1, 12) as $m)
                            @php
                                $cell = $memberMatrix[$m] ?? ['status' => 'unpaid', 'allocated_amount' => 0, 'display' => '0', 'payment_id' => null];
                            @endphp
                            <td class="mx-cell text-center" data-s="{{ $cell['status'] }}" title="{{ $months[$m] }}">
                                @if (!empty($cell['payment_id']))
                                    <a class="mx-chip mx-payment-link" href="{{ route('payments.show', $cell['payment_id']) }}" title="{{ $cell['payment_reference'] ?? 'Détails du paiement' }}">{{ $cell['display'] }}</a>
                                @else
                                    <span class="mx-chip">{{ $cell['display'] ?? '0' }}</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-right amount positive mx-c-total">{{ number_format($total, 2, ',', ' ') }} &euro;</td>
                        <td class="text-right amount {{ $total >= $memberExpected ? 'positive' : 'negative' }} mx-c-remaining">{{ number_format(max(0, $memberExpected - $total), 2, ',', ' ') }} &euro;</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="16" class="empty">{{ __('app.no_members_data') }}</td>
                    </tr>
                @endforelse
                @for ($n = $memberCount + 1; $n <= 75; $n++)
                    <tr class="mx-blank-row">
                        <td class="mx-c-code mx-c-sticky"><span class="ref">MEM-{{ $year }}-{{ str_pad((string) $n, 3, '0', STR_PAD_LEFT) }}</span></td>
                        <td class="mx-c-name mx-c-sticky"></td>
                        @foreach (range(1, 12) as $m)
                            <td class="mx-cell text-center"></td>
                        @endforeach
                        <td class="text-right amount mx-c-total"></td>
                        <td class="text-right amount mx-c-remaining"></td>
                    </tr>
                @endfor
            </tbody>
            @if ($memberCount > 0)
                <tfoot>
                    <tr>
                        <td class="mx-c-code mx-c-sticky mx-foot">{{ __('app.total_collected') }}</td>
                        <td class="mx-c-name mx-c-sticky mx-foot">{{ __('app.months') }}</td>
                        @foreach (range(1, 12) as $m)
                            <td class="text-center mx-foot">
                                <b>{{ number_format($monthCollected[$m], 0, ',', ' ') }}</b>
                                <small class="mx-th-count">{{ $monthPaid[$m] }}/{{ $memberCount }}</small>
                            </td>
                        @endforeach
                        <td class="text-right mx-foot amount positive"><b>{{ number_format($collected, 2, ',', ' ') }} &euro;</b></td>
                        <td class="text-right mx-foot amount negative"><b>{{ number_format($outstanding, 2, ',', ' ') }} &euro;</b></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    <div class="tablefoot mx-print-legend">
        <div class="chips mx-legend">
            <span class="mx-legend-title">{{ __('app.legend') }} :</span>
            @foreach ($legendStatuses as $legend)
                <span class="mx-chip mx-chip-{{ $legend['key'] }}">
                    <i>{{ $legend['icon'] }}</i> {{ $legend['label'] }}
                </span>
            @endforeach
        </div>
    </div>
</section>

<style>
    @page { size: landscape; margin: 10mm; }

    .mx-print-header { display: none; }

    .mx-blank-row { display: none; }

    .mx-payment-link { text-decoration: none; cursor: pointer; }
    .mx-payment-link:hover { text-decoration: underline; }

    .mx-wrap {
        max-height: calc(100vh - 330px);
        min-height: 300px;
        overflow: auto;
        border-top: 1px solid var(--line);
        border-bottom: 1px solid var(--line);
        background: #fff;
        scrollbar-width: thin;
        scrollbar-color: #cddedd transparent;
    }

    .mx-table { min-width: 1150px; font-size: 11.5px; }

    .mx-table thead th {
        position: sticky;
        top: 0;
        z-index: 3;
        padding: 9px 7px;
        background: #f2f8f7;
        border-bottom: 2px solid #dcebe9;
        color: #4d6a6c;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        text-align: center;
        white-space: nowrap;
    }

    .mx-th-count {
        display: block;
        margin-top: 2px;
        color: #9aabab;
        font-size: 8.5px;
        font-weight: 600;
        letter-spacing: 0;
    }

    .mx-c-sticky { position: sticky; }
    .mx-table thead .mx-c-sticky { z-index: 5; text-align: left; }
    .mx-table thead .mx-c-code,
    .mx-table td.mx-c-code,
    .mx-table tfoot .mx-c-code { left: 0; min-width: 86px; width: 86px; }
    .mx-table thead .mx-c-name,
    .mx-table td.mx-c-name,
    .mx-table tfoot .mx-c-name { left: 86px; min-width: 185px; width: 185px; }

    .mx-table td.mx-c-code,
    .mx-table td.mx-c-name,
    .mx-table tfoot td.mx-c-code,
    .mx-table tfoot td.mx-c-name {
        z-index: 2;
        background: #fff;
        border-right: 1px solid var(--line);
        box-shadow: 7px 0 10px -8px rgba(24, 56, 59, .3);
    }

    .mx-table thead .mx-c-code,
    .mx-table thead .mx-c-name { background: #f2f8f7; }

    .mx-table td { padding: 6px 7px; border-bottom: 1px solid #f1f6f6; vertical-align: middle; }

    .mx-table tbody tr:hover td,
    .mx-table tbody tr:hover td.mx-c-code,
    .mx-table tbody tr:hover td.mx-c-name { background: #f7fbfa; }

    .mx-avatar {
        display: grid;
        place-items: center;
        flex: none;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #e4f1ef;
        color: #147b78;
        font-size: 9.5px;
        font-weight: 700;
    }

    .person { display: flex; align-items: center; gap: 8px; }
    .person b { display: block; font-size: 11.5px; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }

    .mx-mini-progress {
        display: block;
        width: 100%;
        max-width: 120px;
        height: 3px;
        margin-top: 3px;
        background: #e6f1ee;
        border-radius: 9px;
        overflow: hidden;
    }
    .mx-mini-progress i { display: block; height: 100%; background: var(--green); border-radius: 9px; }

    .mx-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 3px;
        min-width: 44px;
        padding: 3px 6px;
        border-radius: 6px;
        background: #f2f5f5;
        color: #93a3a4;
        font-size: 10px;
        font-weight: 700;
        line-height: 1.3;
    }
    .mx-chip i { font-style: normal; font-size: 9px; opacity: .85; }

    .mx-table td[data-s="paid"] .mx-chip { background: #e6f7ef; color: #218b67; }
    .mx-table td[data-s="partially_paid"] .mx-chip { background: #fff5e5; color: #bd8434; }
    .mx-table td[data-s="covered_in_advance"] .mx-chip { background: #edf2fc; color: #5b7fbd; }
    .mx-table td[data-s="unpaid"] .mx-chip { background: #f7f9f9; color: #b9c6c6; border: 1px dashed #e2eaea; }

    .mx-chip-paid { background: #e6f7ef; color: #218b67; }
    .mx-chip-partially_paid { background: #fff5e5; color: #bd8434; }
    .mx-chip-unpaid { background: #f7f9f9; color: #93a3a4; }
    .mx-chip-covered_in_advance { background: #edf2fc; color: #5b7fbd; }

    .mx-table .mx-cell { text-align: center; }

    .mx-table tfoot td {
        position: sticky;
        bottom: 0;
        z-index: 3;
        background: #f6faf9;
        border-top: 2px solid #dcebe9;
        border-bottom: 0;
        color: #304e51;
        font-size: 10.5px;
        text-align: center !important;
        vertical-align: middle;
    }
    .mx-table tfoot td.mx-c-code,
    .mx-table tfoot td.mx-c-name { z-index: 4; background: #f6faf9; }

    .mx-table tfoot .mx-th-count { color: #9aabab; font-size: 8.5px; }

    .tablefoot { padding: 13px 21px; border-top: 1px solid var(--line); }

    .mx-legend { margin-top: 0; align-items: center; }
    .mx-legend-title { font-size: 9.5px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; color: #819092; }
    .mx-legend .mx-chip { min-width: 0; padding: 5px 10px; border-radius: 20px; font-size: 10px; }
    .mx-legend .mx-count { margin-left: 3px; padding: 1px 5px; border-radius: 9px; background: rgba(0, 0, 0, .07); font-size: 9.5px; }

    .mx-empty { color: var(--muted); }

    @media (max-width: 1100px) {
        .mx-wrap { max-height: calc(100vh - 380px); }
    }

    @media (max-width: 760px) {
        .mx-wrap { max-height: calc(100vh - 420px); }
        .mx-table { min-width: 1000px; font-size: 10.5px; }
        .mx-table thead th { padding: 7px 5px; font-size: 8.5px; }
        .mx-table td { padding: 5px; }
        .mx-table td.mx-c-code,
        .mx-table td.mx-c-name,
        .mx-table thead .mx-c-code,
        .mx-table thead .mx-c-name,
        .mx-table tfoot .mx-c-code,
        .mx-table tfoot .mx-c-name { position: sticky; }
        .mx-table thead .mx-c-code, .mx-table td.mx-c-code, .mx-table tfoot .mx-c-code { min-width: 70px; width: 70px; }
        .mx-table thead .mx-c-name, .mx-table td.mx-c-name, .mx-table tfoot .mx-c-name { left: 70px; min-width: 150px; width: 150px; }
        .person b { max-width: 96px; font-size: 10.5px; }
        .mx-avatar { width: 22px; height: 22px; font-size: 8.5px; }
        .mx-chip { min-width: 36px; padding: 2px 4px; font-size: 9.5px; }
        .tablefoot { padding: 12px 13px; }
    }

    @media print {
        @page { size: landscape; margin: 8mm; }
        body { background: #fff !important; color: #233746 !important; }
        .mx-hide-print { display: none !important; }
        .mx-table tfoot { display: table-row-group !important; }
        .mx-print-header {
            display: block;
            background: #f4f7f8 !important;
            text-align: center;
            padding: 7px 18px;
            margin-bottom: 8px;
            border-bottom: 2px solid #0f6b78;
        }
        .mx-print-header .cross-img { width: 36px; height: 36px; object-fit: contain; margin-bottom: 3px; }
        .mx-print-header .l1 { font: 700 13pt Georgia, 'Times New Roman', serif; color: #123047; margin-bottom: 2px; }
        .mx-print-header .l2 { font: 600 9pt Georgia, 'Times New Roman', 'Noto Sans Ethiopic', serif; color: #405764; margin-bottom: 3px; }
        .mx-print-header .l3 { font: 700 11pt Calibri, 'Noto Sans Ethiopic', Arial, sans-serif; color: #0f6b78; margin-bottom: 2px; }
        .mx-print-header .l4 { font: italic 7pt Calibri, Arial, sans-serif; color: #6b7d86; }
        .tablepanel { padding: 0 !important; border: 0 !important; box-shadow: none !important; }
        .mx-wrap { max-height: none; min-height: 0; overflow: visible; border: 0; }
        .mx-table { width: 100%; min-width: 0; table-layout: fixed; font-size: 9px; }
        .mx-table thead { display: table-header-group; }
        .mx-table tr { page-break-inside: avoid; }
        .mx-table thead th { background: #123047 !important; color: #fff !important; font-size: 8px; font-weight: 700; padding: 6px 4px; }
        .mx-table th, .mx-table td { border: 1px solid #cbd7dc !important; padding: 6px 4px; font-size: 9px; }
        .mx-table tbody tr:nth-child(even) td { background: #f4f7f8 !important; }
        .mx-table .mx-c-code { width: 100px !important; min-width: 100px !important; white-space: nowrap; }
        .mx-table .mx-c-name { width: 190px !important; min-width: 190px !important; }
        .mx-table .mx-c-total, .mx-table .mx-c-remaining { width: 95px !important; min-width: 95px !important; }
        .mx-table thead th,
        .mx-table td,
        .mx-table tfoot td { position: static !important; box-shadow: none !important; }
        .mx-mini-progress, .mx-avatar { display: none !important; }
        .mx-print-legend { display: block !important; padding: 8px 0; border-top: 1px solid #cbd7dc; }
        .mx-print-legend .mx-legend { justify-content: flex-start; flex-wrap: wrap; }
        .mx-table td.mx-c-code .ref { display: inline-block; white-space: nowrap; font-size: 9px; }
        .mx-table td.mx-c-name b { display: block; max-width: none; white-space: normal; overflow: visible; color: #233746; font-size: 9px; line-height: 1.25; }
        * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>

@if ($autoPrint ?? false)
    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 400);
        });
    </script>
@endif

@push('scripts')
<script>
    (function () {
        var input = document.getElementById('mxSearch');
        if (!input) return;
        input.addEventListener('input', function () {
            var q = this.value.toLowerCase().trim();
            document.querySelectorAll('#mxTable tbody tr[data-search]').forEach(function (row) {
                row.style.display = (!q || row.getAttribute('data-search').indexOf(q) !== -1) ? '' : 'none';
            });
        });
    })();
</script>
@endpush
@endsection
