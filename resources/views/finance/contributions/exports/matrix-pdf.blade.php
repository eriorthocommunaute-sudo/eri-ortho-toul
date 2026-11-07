<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Matrice des cotisations {{ $year }}</title>
    <style>
        @page { size: A3 landscape; margin: 18px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #243b49; }
        .band { background: #f4f7f8; border-bottom: 2px solid #0f6b78; text-align: center; padding: 7px 20px; margin-bottom: 8px; }
        .band .cross-img { display: block; width: 34px; height: 42px; margin: 0 auto 3px; }
        .band .l1 { font: bold 13pt Georgia, 'Times New Roman', serif; color: #123047; margin-bottom: 2px; }
        .band .l2 { font: bold 9pt Georgia, 'Times New Roman', 'Noto Sans Ethiopic', serif; color: #405764; margin-bottom: 3px; }
        .band .l3 { font: bold 11pt Calibri, 'Noto Sans Ethiopic', Arial, sans-serif; color: #0f6b78; margin-bottom: 2px; }
        .band .l4 { font: italic 7pt Calibri, Arial, sans-serif; color: #6b7d86; }
        p { margin: 0 0 10px; color: #5c707c; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { border: 1px solid #d5e0e4; padding: 6px 4px; text-align: center; }
        th { background: #123047; color: #fff; font-size: 8px; font-weight: bold; }
        td { font-size: 8px; }
        tbody tr:nth-child(even) { background: #f4f7f8; }
        td.name, th.name { text-align: left; width: 190px; }
        td.code, th.code { width: 100px; white-space: nowrap; }
        td.name { white-space: nowrap; }
        .pa { background: #e8f1ff; color: #174ea6; font-weight: bold; }
        .partial { background: #fff4d6; color: #805b00; font-weight: bold; }
        .zero { color: #899ba4; }
        .legend { margin-top: 12px; color: #536b76; font-size: 9px; }
    </style>
</head>
<body>
    <div class="band">
        <svg class="cross-img" viewBox="0 0 100 150" role="img" aria-label="Croix orthodoxe" xmlns="http://www.w3.org/2000/svg">
            <path d="M43 7h14v35h28v12H57v30l31 16v12l-31-16v46H43V96L12 112V100l31-16V54H15V42h28z" fill="#a85f27" stroke="#e8ddca" stroke-width="5" stroke-linejoin="round"/>
            <path d="M49 18v15m-8-7h16M49 58v17m-11-9h22M49 99v16m-8-8h16" fill="none" stroke="#fff8e9" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <div class="l1">Communauté Orthodoxe Érythréenne de Toulouse et de ses environs</div>
        <div class="l2">ኦርቶዶክስ ተዋሕዶ ቤተ ክርስቲያን ኤርትራ ባእታ ማርያም ቱሉዝ ፈረንሳ</div>
        <div class="l3">Matrice des cotisations membres · {{ $year }}</div>
        <div class="l4">DOCUMENT ASSOCIATIF / ሰነድ ማሕበር — {{ now()->format('d/m/Y') }}</div>
    </div>
    <p>{{ $members->count() }} membres actifs. Les montants représentent les paiements reçus, PA indique un paiement anticipé, 0 indique aucun paiement, Partial indique un paiement partiel.</p>
    <table>
        <thead>
            <tr>
                <th class="code">Code</th>
                <th class="name">Membre</th>
                @foreach (\App\Services\FinancialRegisterGenerator::MONTHS_FR as $monthName)
                    <th>{{ $monthName }}</th>
                @endforeach
                <th>Total versé (€)</th>
                <th>Reste à payer</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($members as $member)
                @php
                    $row = $matrix[$member->id] ?? [];
                    $paid = 0.0;
                    $expected = 0.0;
                    foreach ($row as $cell) {
                        $paid += (float) ($cell['allocated_amount'] ?? 0);
                        $expected += (float) ($cell['expected_amount'] ?? 10);
                    }
                @endphp
                <tr>
                    <td class="code">{{ $member->member_code }}</td>
                    <td class="name">{{ $member->given_name }} {{ $member->family_name }}</td>
                    @foreach (range(1, 12) as $month)
                        @php $cell = $row[$month] ?? ['display' => '0']; $display = $cell['display'] ?? '0'; @endphp
                        <td class="{{ $display === 'PA' ? 'pa' : ($display === 'Partial' ? 'partial' : ($display === '0' ? 'zero' : '')) }}">{{ $display }}</td>
                    @endforeach
                    <td>{{ number_format($paid, 2, ',', ' ') }} €</td>
                    <td>{{ number_format(max(0, $expected - $paid), 2, ',', ' ') }} €</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="legend"><strong>Légende :</strong> Montant = paiement reçu · PA = payé à l'avance · Partial = paiement partiel · 0 = aucun paiement</div>
</body>
</html>
