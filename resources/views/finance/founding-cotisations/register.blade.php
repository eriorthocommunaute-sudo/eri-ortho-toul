<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cotisations 200€ des membres 2026</title>
    <style>
        /* Gabarit : Registre_Cotisations_200_Membres_2026.pdf */
        @page {
            size: A4 landscape;
            margin: 8mm;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        body {
            font-family: 'Aptos Narrow', 'Arial Narrow', Calibri, Carlito, 'Nyala', 'Noto Sans Ethiopic', Arial, sans-serif;
            font-size: 10pt;
            color: #000;
        }
        .screen-wrap { padding: 16px 0; text-align: center; }
        .sheet { display: block; text-align: left; margin: 0 auto; }
        @media print {
            .screen-wrap { overflow: visible; padding: 0; display: block !important; }
            .sheet { display: block !important; width: 100% !important; min-width: 0 !important; overflow: visible !important; }
            .no-print { display: none !important; }
            thead { display: table-header-group !important; }
            tfoot { display: table-footer-group !important; }
            tr { page-break-inside: avoid; page-break-after: auto; }
            table { page-break-inside: auto; break-inside: auto; width: 100% !important; }
            thead tr { break-inside: avoid; }
            table, thead, tbody, tr, th, td {
                overflow: visible !important;
                transform: none !important;
                position: static !important;
            }
            .page-footer {
                display: block !important;
                position: static !important;
                margin-top: 14px;
                text-align: center;
                font: 8pt Calibri, Carlito, Arial, sans-serif;
                color: #243447;
                border-top: 1px solid #D8D0C0;
                padding-top: 4px;
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
        .page-footer { display: none; }

        /* ---- Header band ---- */
        .band {
            background: #F6F1E7;
            text-align: center;
            padding: 10px 20px;
            margin-bottom: 6px;
        }
        .band .cross-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 6px;
        }
        .band .l1 { font: 700 16pt Georgia, 'Times New Roman', serif; color: #243447; margin-bottom: 2px; }
        .band .l2 { font: 700 13pt Georgia, 'Times New Roman', 'Nyala', 'Noto Sans Ethiopic', serif; color: #000; margin-bottom: 2px; }
        .band .l3 { font: 700 11pt Calibri, Carlito, 'Nyala', 'Noto Sans Ethiopic', Arial, sans-serif; color: #7A2E2E; margin-bottom: 4px; }
        .band .l4 { font: italic 9pt Calibri, Carlito, Arial, sans-serif; color: #243447; }

        /* ---- Meta line ---- */
        .meta-line {
            font: 9pt Calibri, Carlito, Arial, sans-serif;
            color: #243447;
            margin-bottom: 6px;
            padding-left: 4px;
        }

        /* ---- Description ---- */
        .desc {
            font: italic 9pt Calibri, Carlito, Arial, sans-serif;
            color: #555;
            margin-bottom: 8px;
            padding-left: 4px;
        }

        /* ---- Table ---- */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        thead { display: table-header-group; }
        th {
            background: #243447;
            color: #fff;
            font: 700 8pt Calibri, Carlito, 'Nyala', 'Noto Sans Ethiopic', Arial, sans-serif;
            text-align: center;
            border: 1px solid #D8D0C0;
            padding: 3px 2px;
            line-height: 1.2;
            vertical-align: middle;
        }
        th .ti { display: block; font-weight: 400; font-size: 7pt; margin-top: 1px; }
        td {
            border: 1px solid #000;
            padding: 2px 3px;
            font: 9pt 'Aptos Narrow', 'Arial Narrow', Calibri, Carlito, 'Nyala', 'Noto Sans Ethiopic', Arial, sans-serif;
            text-align: center;
            vertical-align: middle;
            height: 22px;
        }
        td.ref {
            color: #7A2E2E;
            font-weight: 700;
            font-size: 8pt;
        }
        tr:nth-child(even) td { background: #FBF9F4; }
        .paid-full { background: #dcfce7 !important; }
        .paid-partial { background: #fef9c3 !important; }
        .paid-none { background: #fee2e2 !important; }

        /* ---- Footer info ---- */
        .document-info {
            margin-top: 10px;
            font: 8pt Calibri, Carlito, Arial, sans-serif;
            color: #243447;
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }
        .document-info p { margin: 0; }

        .no-print { margin: 20px 0; text-align: center; }
        .no-print button {
            background: #243447; color: #fff; border: 0;
            padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px;
        }
        .no-print a {
            display: inline-block; margin-left: 10px; padding: 10px 20px;
            background: #6b7280; color: #fff; text-decoration: none; border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="screen-wrap">
<div class="sheet">

    <div class="band">
        <img class="cross-img" src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="Cross">
        <div class="l1">Communauté Orthodoxe Érythréenne de Toulouse et de ses environs</div>
        <div class="l2">Cotisations 200 € des membres / መዝገብ ኣበርክቶ 200 € ኣባላት</div>
        <div class="l3">DOCUMENT ASSOCIATIF / ሰነድ ማሕበር</div>
        <div class="l4">Année / ዓመት : 2026</div>
    </div>

    <div class="desc">Registre des cotisations des membres. Les informations peuvent être complétées ou mises à jour au besoin.</div>

    <table>
        <colgroup>
            <col style="width: 3%;">
            <col style="width: 9%;">
            <col style="width: 15%;">
            <col style="width: 6%;">
            <col style="width: 13%;">
            <col style="width: 5%;">
            <col style="width: 6%;">
            <col style="width: 7%;">
            <col style="width: 6%;">
            <col style="width: 7%;">
            <col style="width: 23%;">
        </colgroup>
        <thead>
            <tr>
                <th>N°<span class="ti">ቁ</span></th>
                <th>Member ID<span class="ti">መለለዪ ኣባል</span></th>
                <th>Prénom et Nom<span class="ti">ስምን ኣስማት ስድራን</span></th>
                <th>Télé<span class="ti">ተሌፎን</span></th>
                <th>Email<span class="ti">ኢመይል</span></th>
                <th>Adresse<span class="ti">ኣድራሻ</span></th>
                <th>Famille<span class="ti">ስድራ</span></th>
                <th>Date payé<span class="ti">ዕለት ክፍሊት</span></th>
                <th>Payé<span class="ti">ዝተኸፍለ</span></th>
                <th>Reste à payer (€)<span class="ti">ዝተረፈ ክፍሊት</span></th>
                <th>Notes<span class="ti">መዘኻኸሪ</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($members as $index => $row)
            @php
                $member = $row['member'];
                $paid = $row['paid_amount'];
                $remaining = $row['remaining'];
                $statusClass = $paid >= 200 ? 'paid-full' : ($paid > 0 ? 'paid-partial' : 'paid-none');
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="ref">{{ $member->member_code }}</td>
                <td style="text-align: left;">{{ $member->given_name }} {{ $member->family_name }}</td>
                <td>{{ $member->phone ?? '' }}</td>
                <td>{{ $member->email ?? '' }}</td>
                <td style="text-align: left; font-size: 8pt;">{{ $member->address ?? '' }}</td>
                <td>{{ $member->family_name }}</td>
                <td>{{ $row['founding']?->payment_date?->format('d/m/Y') ?? '' }}</td>
                <td class="{{ $statusClass }}">{{ $paid > 0 ? number_format($paid, 0) . ' €' : '' }}</td>
                <td class="{{ $statusClass }}">{{ $remaining > 0 ? number_format($remaining, 0) . ' €' : '—' }}</td>
                <td>{{ $row['founding']?->notes ?? '' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align: center;">Aucun membre actif</td>
            </tr>
            @endforelse
            @php
                $totalPaid = $members->sum('paid_amount');
                $totalRemaining = $members->sum('remaining');
            @endphp
            <tr style="background: #F6F1E7; font-weight: bold;">
                <td colspan="8" style="text-align: right; border-right: none;">TOTAL / ጠቕላላ</td>
                <td style="border-left: none; border-right: none;">{{ number_format($totalPaid, 0) }} €</td>
                <td style="border-left: none; border-right: none;">{{ number_format($totalRemaining, 0) }} €</td>
                <td style="border-left: none;"></td>
            </tr>
        </tbody>
    </table>

    @php
        $totalPaid = $members->sum('paid_amount');
        $totalRemaining = $members->sum('remaining');
        $fullyPaid = $members->filter(fn($r) => $r['paid_amount'] >= 200)->count();
    @endphp

    <div class="document-info">
        <p><strong>Année:</strong> 2026</p>
        <p><strong>Total membres:</strong> {{ $members->count() }}</p>
        <p><strong>Montant cotisation:</strong> 200 € (une seule fois)</p>
        <p><strong>Total payé:</strong> {{ number_format($totalPaid, 0) }} €</p>
        <p><strong>Reste à payer:</strong> {{ number_format($totalRemaining, 0) }} €</p>
        <p><strong>Membres à jour:</strong> {{ $fullyPaid }} / {{ $members->count() }}</p>
    </div>

</div>
</div>

<div class="no-print">
    <button onclick="window.print()">Imprimer / Enregistrer PDF</button>
    <a href="{{ route('founding-cotisations.index') }}">Retour au registre</a>
</div>

<div class="page-footer">
    Communauté Orthodoxe Érythréenne de Toulouse et de ses environs | Cotisations 200€ des membres 2026 | Généré le {{ now()->format('d/m/Y H:i') }}
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() { window.print(); }, 500);
});
</script>
</body>
</html>
