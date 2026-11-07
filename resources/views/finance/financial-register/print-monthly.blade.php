<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reference }} - {{ $period_label }}</title>
    <style>
        /* Gabarit : Registre_Financier_2026_Tigrigna_French.xlsx (A4 paysage, 1 seule page, centré, pleine page) */
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
            font-size: 11pt;
            color: #000;
        }

        .screen-wrap { overflow-x: auto; padding: 16px 0; text-align: center; }

        .sheet { display: inline-block; width: 1533px; text-align: left; }

        @media print {
            .screen-wrap { overflow: visible; padding: 0; }
            .sheet { zoom: 0.69; }
            .no-print { display: none !important; }
        }

        table.register {
            width: 1533px;
            table-layout: fixed;
            border-collapse: collapse;
            border-spacing: 0;
        }

        table.register td, table.register th { padding: 0; }

        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        table.register { page-break-inside: avoid; break-inside: avoid; }

        /* ---- Bandeau d'en-tête (lignes 1 à 5) ---- */
        .band {
            position: relative;
            height: 141px;
            background: #F6F1E7;
            text-align: center;
            font-weight: normal;
        }
        .band .l1, .band .l2, .band .l3, .band .l4, .band .l5 {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 90px;
            text-align: center;
        }
        .band .l1 { height: 36px; font: 700 18pt Georgia, 'Times New Roman', serif; color: #243447; }
        .band .l2 { height: 36px; font: 700 14pt Georgia, 'Times New Roman', 'Nyala', 'Noto Sans Ethiopic', serif; color: #000; }
        .band .l3 { height: 29px; font: 700 14pt Georgia, 'Times New Roman', 'Nyala', 'Noto Sans Ethiopic', serif; color: #7A2E2E; }
        .band .l4 { height: 20px; font: 700 9pt Calibri, Carlito, Arial, sans-serif; color: #B08D3B; }
        .band .l5 { height: 20px; font: italic 9pt Calibri, Carlito, 'Nyala', 'Noto Sans Ethiopic', Arial, sans-serif; color: #243447; }
        .band img {
            position: absolute;
            width: 54px;
            height: 54px;
            object-fit: contain;
        }
        .band img.left  { left: 266px;  top: 13px; }
        .band img.right { left: 1209px; top: 16px; }

        .spacer-row td { height: 20px; border: 0; }

        /* ---- En-tête du tableau (ligne 7) ---- */
        th.col {
            height: 73px;
            background: #243447;
            color: #fff;
            font: 700 9pt Calibri, Carlito, 'Nyala', 'Noto Sans Ethiopic', Arial, sans-serif;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #D8D0C0;
            padding: 0 3px;
            line-height: 1.25;
        }

        /* ---- Lignes de saisie ---- */
        td.cell {
            height: 20px;
            line-height: 18px;
            border: 1px solid #000;
            text-align: center;
            vertical-align: top;
            overflow: hidden;
            white-space: nowrap;
        }
        .opening-balance {
            display: grid;
            grid-template-columns: 54px 146px 215px 239px 229px 131px 131px 131px 257px;
            width: 1533px;
            min-height: 22px;
            margin-bottom: 2px;
            background: #F6F1E7;
            color: #243447;
            font: 700 10pt Calibri, Carlito, Arial, sans-serif;
            align-items: center;
        }
        .opening-balance .label {
            grid-column: 1 / span 7;
            text-align: right;
            padding-right: 6px;
        }
        .opening-balance .amount {
            grid-column: 8;
            text-align: center;
        }
        .opening-balance .note-line {
            grid-column: 9;
            height: 16px;
            margin: 0 8px;
            border-bottom: 1px solid #D8D0C0;
        }
        tr.alt td.cell { background: #FBF9F4; }

        /* ---- Lignes vides ---- */
        tr.gap td { height: 20px; border: 0; padding: 0; }

        /* ---- Références sources ---- */
        td.src-title {
            height: 20px;
            text-align: center;
            font: 700 11pt Calibri, Carlito, 'Nyala', 'Noto Sans Ethiopic', Arial, sans-serif;
            color: #7A2E2E;
            border: 0;
            border-bottom: 1px solid #000;
        }
        tr.src td {
            height: 21px;
            vertical-align: middle;
            text-align: left;
            font: 10pt 'Aptos Narrow', 'Arial Narrow', Calibri, Carlito, 'Nyala', 'Noto Sans Ethiopic', Arial, sans-serif;
            padding: 0 4px;
            border: 1px solid #000;
            white-space: normal;
        }
        tr.src td.code { font: 700 10pt Calibri, Carlito, Arial, sans-serif; color: #7A2E2E; }
        tr.src td.file { font: 700 10pt Calibri, Carlito, Arial, sans-serif; color: #243447; }
        tr.src td.label { border-color: #D8D0C0; }
        tr.src.tall td { height: 35px; }
        /* Cases de montants : pas de cadre, un seul trait gras en bas de colonne (somme -> TOTAL) */
        tr.src td.amt { text-align: center; border: 0; }
        tr.src td.amt.sum-line { border-bottom: 2.5px solid #000; }

        /* ---- Ligne TOTAL (ligne 227) ---- */
        tr.total td {
            height: 20px;
            background: #F6F1E7;
            border: 1px solid #D8D0C0;
            font: 700 11pt Calibri, Carlito, 'Nyala', 'Noto Sans Ethiopic', Arial, sans-serif;
            color: #243447;
            text-align: center;
            vertical-align: middle;
        }
        tr.total td.total-label { text-align: right; padding-right: 4px; }

        /* ---- Infos document (hors gabarit, discret) ---- */
        .document-info {
            margin-top: 10px;
            font: 8pt Calibri, Carlito, Arial, sans-serif;
            color: #243447;
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
        }
        .document-info p { margin: 0; }

        .no-print { margin: 24px 0; text-align: center; }
        .no-print button {
            background: #243447;
            color: #fff;
            border: 0;
            padding: 12px 24px;
            border-radius: 8px;
            font: 600 1rem Calibri, Carlito, Arial, sans-serif;
            cursor: pointer;
        }
        .no-print button:hover { background: #1a2635; }
    </style>
</head>
<body>
@php
    $rowsCount = max(25, $entries->count()); // conserve les lignes du modèle et ajoute les écritures réelles
    $year = $period_start->format('Y');
    $sources = [
        ['COT-', 'Registre_Cotisations', 'Cotisations mensuelles / ኣበርክቶ ወርሓዊ', 'G'],
        ['FCO-', 'Cotisations fondatrices 200€', 'Cotisations fondatrices 200€ / ኣበርክቶ መስረቲ 200€', 'G'],
        ['OFF-', 'Registre_Offrandes', 'Offrandes / ሞባእ', 'G'],
        ['DON-', 'Registre_Dons', "Dons / ልገሳ", 'G'],
        ['EQP-', 'Registre_Equipements', 'Équipements / መሳርሒታት', 'H'],
        ['TRP-', 'Registre_Transport_Pretre', 'Transport du prêtre / መጓዓዝያ ካህን', 'H'],
        ['PRE-', 'Registre_Indemnite_Pretre', 'Indemnité du prêtre / ክፍሊት ካህን', 'H'],
        ['EVT-', 'Registre_Evenements', 'Événements / ኣጋጣሚታት', 'H'],
        ['EXC-', 'Registre_Demandes_Exceptionnelles', 'Demandes exceptionnelles / ፍሉይ ሕቶታት', 'H'],
    ];
@endphp

<div class="screen-wrap">
<div class="sheet">
<div class="band">
    <img class="left" src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="">
    <img class="right" src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="">
    <div class="l1">Communauté Orthodoxe Érythréenne de Toulouse</div>
    <div class="l2">ኦርቶዶክስ ተዋሕዶ ቤተ ክርስቲያን ኤርትራ ባእታ ማርያም ቱሉዝ ፈረንሳ</div>
    <div class="l3">Registre financier {{ $period_label }} / መዝገብ ገንዘብ {{ $period_label }}</div>
    <div class="l4">{{ $reference }} · {{ $period_start->format('d/m/Y') }} – {{ $period_end->format('d/m/Y') }}</div>
    <div class="l5">Registre central des recettes et dépenses / ማእከላይ መዝገብ እቶትን ወጻኢታትን</div>
</div>
<div class="opening-balance">
    <span class="label">Solde d'ouverture / መእተዊ ተረፍ</span>
    <span class="amount">{{ !($blank_register ?? false) && $opening_balance !== null ? number_format($opening_balance, 2, ',', ' ') . ' €' : '' }}</span>
    <span class="note-line"></span>
</div>
<table class="register">
    <colgroup>
        <col style="width: 54px;">   {{-- B  N° --}}
        <col style="width: 146px;">  {{-- C  Référence RF --}}
        <col style="width: 215px;">  {{-- D  Date --}}
        <col style="width: 239px;">  {{-- E  Référence source --}}
        <col style="width: 229px;">  {{-- F  Type / Catégorie --}}
        <col style="width: 131px;">  {{-- G  Entrée --}}
        <col style="width: 131px;">  {{-- H  Sortie --}}
        <col style="width: 131px;">  {{-- I  Solde --}}
        <col style="width: 257px;">  {{-- J  Mode de paiement / Notes --}}
    </colgroup>

    <thead>
        <tr class="spacer-row"><td colspan="9"></td></tr>
        <tr>
            <th class="col">N°</th>
            <th class="col">Référence RF<br>መወከሲ ገንዘብ</th>
            <th class="col">Date<br>ዕለት</th>
            <th class="col">Référence source<br>መወከሲ ሰነድ</th>
            <th class="col">Type / Catégorie<br>ዓይነት / ምድብ</th>
            <th class="col">Entrée (€)<br>እቶት (€)</th>
            <th class="col">Sortie (€)<br>ወጻኢ (€)</th>
            <th class="col">Solde (€)<br>ተረፍ (€)</th>
            <th class="col">Mode de paiement / Notes<br>ኣገባብ ክፍሊት / መዘኻኸሪ</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($entries as $index => $entry)
        <tr class="{{ ($index + 1) % 2 === 0 ? 'alt' : '' }}">
            <td class="cell">{{ $index + 1 }}</td>
            <td class="cell">{{ $entry['line_reference'] }}</td>
            <td class="cell">{{ \Carbon\Carbon::parse($entry['entry_date'])->format('d/m/Y') }}</td>
            <td class="cell">{{ $entry['source_reference'] }}</td>
            <td class="cell">{{ $entry['category_label'] }}</td>
            <td class="cell">{{ $entry['type'] === 'income' ? number_format($entry['amount'], 2, ',', ' ') : '' }}</td>
            <td class="cell">{{ $entry['type'] === 'expense' ? number_format($entry['amount'], 2, ',', ' ') : '' }}</td>
            <td class="cell">{{ number_format($entry['running_balance'], 2, ',', ' ') }}</td>
            <td class="cell">{{ trim($entry['payment_method'] . ($entry['notes'] ? ' | ' . $entry['notes'] : '')) }}</td>
        </tr>
        @endforeach
        @for ($i = $entries->count() + 1; $i <= $rowsCount; $i++)
        <tr class="{{ $i % 2 === 0 ? 'alt' : '' }}">
            <td class="cell">{{ $i }}</td>
            <td class="cell">{{ $reference }}-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</td>
            <td class="cell"></td><td class="cell"></td><td class="cell"></td>
            <td class="cell"></td><td class="cell"></td><td class="cell"></td><td class="cell"></td>
        </tr>
        @endfor

        <tr class="gap"><td colspan="9"></td></tr>

        <tr>
            <td class="src-title" colspan="9">RÉFÉRENCES SOURCES / መወከሲ ምንጪ ሰነዳት</td>
        </tr>
        <tr class="gap"><td colspan="9"></td></tr>

        @foreach ($sources as $s)
        <tr class="src {{ $s[0] === 'EXC-' ? 'tall' : '' }}">
            <td style="border:0;"></td>
            <td class="code">{{ $s[0] }}{{ $year }}-</td>
            <td class="file">@if ($s[0] === 'FCO-')<a href="{{ route('founding-cotisations.index') }}" style="color: #243447;">{{ $s[1] }}</a>@else{{ $s[1] }}@endif</td>
            <td class="label">{{ $s[2] }}</td>
            <td style="border:0;"></td>
            @if ($s[3] === 'G')
                <td class="amt sum-line">@if (!($blank_register ?? false)){{ number_format(data_get($source_totals->get(rtrim($s[0], '-')), 'income', 0), 2, ',', ' ') }}@endif</td>
                <td style="border:0;"></td>
            @else
                <td style="border:0;"></td>
                <td class="amt sum-line">@if (!($blank_register ?? false)){{ number_format(data_get($source_totals->get(rtrim($s[0], '-')), 'expense', 0), 2, ',', ' ') }}@endif</td>
            @endif
            <td style="border:0;" colspan="2"></td>
        </tr>
        @endforeach

        <tr class="gap"><td colspan="9"></td></tr>

        <tr class="total">
            <td class="total-label" colspan="5">TOTAL / ጠቕላላ</td>
            <td>@if (!($blank_register ?? false)){{ number_format($total_income, 2, ',', ' ') }}@endif</td>
            <td>@if (!($blank_register ?? false)){{ number_format($total_expense, 2, ',', ' ') }}@endif</td>
            <td>@if (!($blank_register ?? false)){{ number_format($closing_balance, 2, ',', ' ') }}@endif</td>
            <td></td>
        </tr>
    </tbody>
</table>

<div class="document-info">
    <p><strong>{{ __('app.document_reference') }}:</strong> {{ $reference }}</p>
    <p><strong>{{ __('app.period') }}:</strong> {{ $period_label }}</p>
    <p><strong>{{ __('app.generation_date') }}:</strong> {{ now()->format('d/m/Y H:i') }}</p>
    <p><strong>{{ __('app.generated_by') }}:</strong> {{ auth()->user()?->name }}</p>
</div>
</div>
</div>

<div class="no-print">
    <button onclick="window.print()">{{ __('app.print_save_pdf') }}</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() { window.print(); }, 500);
});
</script>
</body>
</html>
