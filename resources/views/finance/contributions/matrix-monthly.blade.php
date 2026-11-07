<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.matrix_membership_contributions') }}, {{ $monthName }} {{ $year }}</title>
    <style>
        @page { size: letter landscape; margin: 8mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #233746; font-family: Arial, sans-serif; }
        .sheet { width: 100%; margin: 0 auto; }
        .header { position: relative; text-align: center; padding: 7px 18px; margin-bottom: 8px; background: #f4f7f8; border-bottom: 2px solid #0f6b78; }
        .header img { display: block; width: 36px; height: 36px; object-fit: contain; margin: 0 auto 3px; }
        .header .fr { font: 700 13pt Georgia, 'Times New Roman', serif; color: #123047; margin-bottom: 2px; }
        .header .ti { font: 600 9pt Georgia, 'Times New Roman', serif; color: #405764; margin-bottom: 3px; }
        .header h1 { margin: 0 0 2px; color: #0f6b78; font: 700 11pt Arial, sans-serif; }
        .header .meta { font: italic 7pt Arial, sans-serif; color: #6b7d86; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        th, td { border: 1px solid #cbd7dc; padding: 6px 4px; font-size: 9px; }
        th { background: #123047; color: #fff; font-size: 8px; font-weight: 700; text-align: center; text-transform: uppercase; }
        td { height: 30px; vertical-align: middle; }
        tbody tr:nth-child(even) { background: #f7f9fa; }
        .code { width: 15%; white-space: nowrap; color: #0f6b78; font-weight: 700; }
        .member { width: 31%; }
        .payment { width: 24%; text-align: center; }
        .reference { width: 18%; }
        .notes { width: 12%; }
        .status { display: block; margin-top: 2px; color: #6b7d86; font-size: 8px; }
        tfoot td { height: 34px; background: #f6faf9; color: #304e51; text-align: center; }
        tfoot b { font-weight: 700; }
        .actions { margin: 16px 0; text-align: center; }
        .actions button { border: 0; border-radius: 6px; padding: 10px 18px; color: white; background: #123047; font-size: 14px; cursor: pointer; }
        @media print {
            .sheet { width: 100%; margin: 0; }
            .header { break-after: avoid; }
            .actions { display: none !important; }
            th { padding: 6px 4px; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <main class="sheet">
        <header class="header">
            <img src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="">
            <div class="fr">Communauté Orthodoxe Érythréenne de Toulouse et de ses environs</div>
            <div class="ti">ኦርቶዶክስ ተዋሕዶ ቤተ ክርስቲያን ኤርትራ ባእታ ማርያም ቱሉዝ ፈረንሳ</div>
            <h1>{{ __('app.matrix_membership_contributions') }}, {{ $monthName }} {{ $year }}</h1>
            <div class="meta">DOCUMENT ASSOCIATIF / ሰነድ ማሕበር · {{ $members->count() }} membres actifs · {{ now()->format('d/m/Y') }}</div>
        </header>

        <table>
            <thead>
                <tr>
                    <th class="code">{{ __('app.member_id') }}</th>
                    <th class="member">{{ __('app.member_name') }}</th>
                    <th class="payment">{{ $monthName }} - {{ __('app.payment_status') }} / {{ __('app.amount') }}</th>
                    <th class="reference">{{ __('app.reference') }}</th>
                    <th class="notes">{{ __('app.notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @if ($members->isEmpty())
                    <tr><td colspan="5">{{ __('app.no_members_data') }}</td></tr>
                @else
                    @foreach ($members as $member)
                        @php
                            $cell = $matrix[$member->id][$month] ?? ['status' => 'unpaid', 'display' => '0', 'payment_reference' => null];
                            $statusLabel = match ($cell['status'] ?? 'unpaid') {
                                'paid' => __('app.paid_label'),
                                'partially_paid' => __('app.partially_paid_label'),
                                'covered_in_advance' => __('app.covered_in_advance_label'),
                                default => __('app.unpaid_label'),
                            };
                        @endphp
                        <tr>
                            <td class="code">{{ $member->member_code }}</td>
                            <td>{{ $member->given_name }} {{ $member->family_name }}</td>
                            <td class="payment">{{ $cell['display'] ?? '0' }}<span class="status">{{ $statusLabel }}</span></td>
                            <td>{{ $cell['payment_reference'] ?? '' }}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    @for ($number = $members->count() + 1; $number <= 75; $number++)
                        <tr>
                            <td class="code">MEM-{{ $year }}-{{ str_pad((string) $number, 3, '0', STR_PAD_LEFT) }}</td>
                            <td></td>
                            <td class="payment"></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                @endif
            </tbody>
            @php
                $monthTotal = 0.0;
                foreach ($members as $member) {
                    $monthTotal += (float) ($matrix[$member->id][$month]['allocated_amount'] ?? 0);
                }
            @endphp
            <tfoot>
                <tr>
                    <td>{{ __('app.total_collected') }}</td>
                    <td>{{ __('app.month') }}</td>
                    <td><b>{{ number_format($monthTotal, 2, ',', ' ') }} €</b></td>
                    <td colspan="2">{{ $monthName }} {{ $year }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="actions">
            <button type="button" onclick="window.print()">{{ __('app.print_save_pdf') }}</button>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function () { window.print(); }, 400);
        });
    </script>
</body>
</html>
