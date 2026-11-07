<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.purchase_requests') }} — {{ now()->format('d/m/Y') }}</title>
    <style>
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

        .meta-line {
            font: 9pt Calibri, Carlito, Arial, sans-serif;
            color: #243447;
            margin-bottom: 6px;
            padding-left: 4px;
            display: flex;
            justify-content: space-between;
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
            font: 8pt 'Aptos Narrow', 'Arial Narrow', Calibri, Carlito, 'Nyala', 'Noto Sans Ethiopic', Arial, sans-serif;
            text-align: center;
            vertical-align: middle;
            height: 20px;
        }
        td.ref {
            color: #7A2E2E;
            font-weight: 700;
            font-size: 8pt;
        }
        td.obj { text-align: left; }
        tr:nth-child(even) td { background: #FBF9F4; }
        tr.total-row td {
            background: #F6F1E7 !important;
            font-weight: 700;
            border-top: 2px solid #243447;
        }

        .status-badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 7pt; font-weight: bold; text-transform: uppercase; white-space: nowrap; }
        .status-draft { background: #f3f4f6; color: #374151; }
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .status-approved { background: #dcfce7; color: #166534; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-ordered { background: #fef3c7; color: #92400e; }
        .status-received { background: #dcfce7; color: #166534; }
        .status-cancelled { background: #f3f4f6; color: #6b7280; }

        .no-print { margin-top: 20px; text-align: center; }
        .no-print .btn {
            background: #243447; color: #fff; border: 0;
            padding: 10px 20px; border-radius: 4px; cursor: pointer; font-size: 14px;
        }
        .no-print a {
            display: inline-block; margin-left: 10px; padding: 10px 20px;
            background: #6b7280; color: #fff; text-decoration: none; border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
<div class="screen-wrap">
<div class="sheet">

    <div class="band">
        <img class="cross-img" src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="Cross">
        <div class="l1">Communauté Orthodoxe Érythréenne de Toulouse et de ses environs</div>
        <div class="l2">ኦርቶዶክስ ተዋሕዶ ቤተ ክርስቲያን ኤርትራ ባእታ ማርያም ቱሉዝ ፈረንሳ</div>
        <div class="l3">Demande d'achat et de décaissement / ሕቶ ንግዛእ ፡ ወጻኢ ገንዘብን</div>
        <div class="l4">DOCUMENT ASSOCIATIF / ሰነድ ማሕበር — Une référence DA par demande / ንነፍሲ ወከፍ ሕቶ ሓደ DA መወከሲ</div>
    </div>

    <div class="meta-line">
        <div>{{ __('app.total_requests_print', ['count' => $requests->count()]) }}</div>
        <div>{{ __('app.generated_on', ['date' => now()->format('d/m/Y H:i')]) }}</div>
    </div>

    <table>
        <colgroup>
            <col style="width: 3%;">
            <col style="width: 7%;">
            <col style="width: 6%;">
            <col style="width: 10%;">
            <col style="width: 17%;">
            <col style="width: 7%;">
            <col style="width: 4%;">
            <col style="width: 10%;">
            <col style="width: 7%;">
            <col style="width: 7%;">
            <col style="width: 6%;">
            <col style="width: 6%;">
            <col style="width: 10%;">
        </colgroup>
        <thead>
            <tr>
                <th>N°</th>
                <th>Référence DA<span class="ti">መወከሲ ሕቶ</span></th>
                <th>Date demande<span class="ti">ዕለት ሕቶ</span></th>
                <th>Demandeur<span class="ti">ሓታቲ</span></th>
                <th>Objet / Désignation<span class="ti">ዕላማ / መግለጺ</span></th>
                <th>Catégorie<span class="ti">ምድብ</span></th>
                <th>Qté<span class="ti">ብዝሒ</span></th>
                <th>Fournisseur prévu<span class="ti">ዝተመደበ ኣቕራቢ</span></th>
                <th>Montant estimé (€)<span class="ti">ዝተገመተ መጠን (€)</span></th>
                <th>Montant payé (€)<span class="ti">ዝተኸፍለ (€)</span></th>
                <th>Réf. EQP<span class="ti">መወከሲ መሳርሒ</span></th>
                <th>Réf. RF<span class="ti">መወከሲ ገንዘብ</span></th>
                <th>Statut / Approbation<span class="ti">ኩነታት / ምጽድቕ</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse($requests as $index => $req)
            @php $paid = $paidAmounts->get($req->id); @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="ref">{{ $req->reference }}</td>
                <td>{{ $req->created_at->format('d/m/Y') }}</td>
                <td>{{ $req->requester->name ?? '' }}</td>
                <td class="obj">{{ $req->title }}</td>
                <td>{{ $req->category ? __('app.'.$req->category) : '' }}</td>
                <td></td>
                <td>{{ $req->supplier ?? '' }}</td>
                <td>{{ number_format((float) $req->estimated_amount, 2) }}</td>
                <td>{{ $paid !== null ? number_format((float) $paid, 2) : '' }}</td>
                <td class="ref">{{ $req->equipment_reference ?? '' }}</td>
                <td class="ref">{{ $req->financial_reference ?? '' }}</td>
                <td><span class="status-badge status-{{ $req->status }}">{{ __('app.'.$req->status) }}</span></td>
            </tr>
            @empty
            <tr>
                <td colspan="13" style="text-align: center;">{{ __('app.no_data') }}</td>
            </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="8" style="text-align: right;">TOTAL / ጠቕላላ</td>
                <td>{{ number_format((float) $requests->sum('estimated_amount'), 2) }}</td>
                <td>{{ number_format((float) $paidAmounts->sum(), 2) }}</td>
                <td colspan="3"></td>
            </tr>
        </tbody>
    </table>

    <div class="page-footer">
        Communauté Orthodoxe Érythréenne de Toulouse et de ses environs | Demande d'achat et de décaissement / ሕቶ ንግዛእ ፡ ወጻኢ ገንዘብን | {{ now()->format('d/m/Y H:i') }}
    </div>

</div>
</div>

<div class="no-print">
    <button class="btn" onclick="window.print()">{{ __('app.print') }}</button>
    <a href="{{ route('purchase-requests.index') }}">{{ __('app.back_to_list') }}</a>
</div>

<script>
    window.onload = function() {
        setTimeout(function() { window.print(); }, 500);
    };
</script>
</body>
</html>
