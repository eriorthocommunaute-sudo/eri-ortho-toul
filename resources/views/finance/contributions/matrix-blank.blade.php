<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.matrix_membership_contributions') }}, {{ $monthName }} {{ $year }} / ወርሓዊ ክፍሊት {{ $monthNameTi }} {{ $year }}</title>
    <style>
        @page { size: letter landscape; margin: 8mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #233746; font-family: Arial, sans-serif; }
        .sheet { width: 100%; margin: 0 auto; }
        .header { text-align: center; padding: 7px 18px; margin-bottom: 8px; background: #f4f7f8; border-bottom: 2px solid #0f6b78; }
        .header img { display: block; width: 36px; height: 36px; object-fit: contain; margin: 0 auto 3px; }
        .header .fr { font: 700 13pt Georgia, 'Times New Roman', serif; color: #123047; margin-bottom: 2px; }
        .header .ti { font: 600 9pt Georgia, 'Times New Roman', serif; color: #405764; margin-bottom: 3px; }
        .header h1 { margin: 0 0 2px; color: #0f6b78; font: 700 11pt Calibri, 'Noto Sans Ethiopic', Arial, sans-serif; }
        .header .meta { font: italic 7pt Arial, sans-serif; color: #6b7d86; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        th, td { border: 1px solid #cbd7dc; padding: 6px 4px; font-size: 9px; }
        th { background: #123047; color: #fff; font-size: 8px; font-weight: 700; text-align: center; text-transform: uppercase; }
        td { height: 30px; vertical-align: middle; }
        tbody tr:nth-child(even) { background: #f4f7f8; }
        .code { width: 10%; color: #0f6b78; font-weight: 700; white-space: nowrap; }
        .member { width: 18%; text-align: left; }
        .month { width: 4.5%; text-align: center; }
        .total { width: 9%; text-align: center; }
        tfoot td { height: 34px; background: #f6faf9; color: #304e51; text-align: center; }
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
            <h1>{{ __('app.matrix_membership_contributions') }}, {{ $monthName }} {{ $year }} / ወርሓዊ ክፍሊት {{ $monthNameTi }} {{ $year }}</h1>
            <div class="meta">DOCUMENT ASSOCIATIF / ሰነድ ማሕበር · {{ now()->format('d/m/Y') }}</div>
        </header>

        @php($months = \App\Services\FinancialRegisterGenerator::MONTHS_FR)
        <table>
            <colgroup>
                <col class="code">
                <col class="member">
                @foreach (range(1, 12) as $monthNumber)
                    <col class="month">
                @endforeach
                <col class="total">
                <col class="total">
            </colgroup>
            <thead>
                <tr>
                    <th>{{ __('app.member_id') }}</th>
                    <th>{{ __('app.member_name') }}</th>
                    @foreach (range(1, 12) as $monthNumber)
                        <th>{{ mb_strtoupper(mb_substr($months[$monthNumber], 0, 3)) }}</th>
                    @endforeach
                    <th>Total versé (€)</th>
                    <th>Reste à payer</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                    <tr>
                        <td class="code">{{ $member->member_code }}</td>
                        <td>{{ $member->given_name }} {{ $member->family_name }}</td>
                        @foreach (range(1, 12) as $monthNumber)
                            <td></td>
                        @endforeach
                        <td></td>
                        <td></td>
                    </tr>
                @empty
                    <tr><td colspan="16">{{ __('app.no_members_data') }}</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td>{{ __('app.total_collected') }}</td>
                    <td>{{ __('app.months') }}</td>
                    @foreach (range(1, 14) as $emptyCell)
                        <td></td>
                    @endforeach
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
