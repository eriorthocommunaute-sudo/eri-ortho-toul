<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $reference }} - {{ $period_label }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        @media print {
            @page {
                margin: 15mm 10mm;
                size: A4 landscape;
            }
            .no-print {
                display: none !important;
            }
            body {
                background: white;
                font-size: 10pt;
            }
            .financial-register {
                padding: 0;
            }
            .register-header {
                page-break-after: avoid;
            }
            table {
                page-break-inside: auto;
            }
            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }
            thead {
                display: table-header-group;
            }
            tfoot {
                display: table-footer-group;
            }
        }

        .financial-register {
            max-width: 100%;
            margin: 0 auto;
            padding: 20px;
            font-family: 'DM Sans', system-ui, sans-serif;
        }

        .register-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid var(--amber);
        }

        .register-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--ink);
            margin: 0 0 4px;
        }

        .register-header .reference {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--amber);
        }

        .bilingual {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 8px;
            font-size: 0.875rem;
            color: var(--muted);
        }

        .register-meta {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 24px;
            font-size: 0.875rem;
            color: var(--ink);
        }

        .register-meta strong {
            color: var(--ink);
        }

        .table-section {
            margin-bottom: 24px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        th, td {
            padding: 8px 6px;
            text-align: left;
            border: 1px solid var(--border);
        }

        .opening-balance {
            display: grid;
            grid-template-columns: 40px 130px 80px 120px minmax(160px, 1fr) 90px 90px 90px minmax(160px, 1fr);
            width: 100%;
            min-height: 28px;
            margin-bottom: 2px;
            background: #F6F1E7;
            color: #243447;
            font-weight: 700;
            align-items: center;
        }

        .opening-balance .label {
            grid-column: 1 / span 7;
            text-align: right;
            padding-right: 8px;
        }

        .opening-balance .amount {
            grid-column: 8;
            text-align: right;
            padding-right: 6px;
        }

        .opening-balance .note-line {
            grid-column: 9;
            height: 18px;
            margin: 0 6px;
            border-bottom: 1px solid #D8D0C0;
        }

        th {
            background: var(--muted-light);
            font-weight: 600;
            color: var(--ink);
            white-space: nowrap;
        }

        td.income {
            text-align: right;
            color: var(--positive);
        }

        td.expense {
            text-align: right;
            color: var(--negative);
        }

        td.balance {
            text-align: right;
            font-weight: 500;
        }

        td.empty-cell {
            color: transparent;
        }

        td.ref {
            font-weight: 700;
            color: #287b78;
            white-space: nowrap;
        }

        tr:nth-child(even) td {
            background: var(--muted-light);
        }

        .register-totals {
            display: flex;
            justify-content: flex-end;
            gap: 2rem;
            margin-bottom: 24px;
            padding: 16px;
            background: var(--muted-light);
            border-radius: 8px;
        }

        .total-row {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .total-row span {
            font-size: 0.8125rem;
            color: var(--muted);
        }

        .total-row strong {
            font-size: 1rem;
            font-weight: 700;
        }

        .total-row strong.positive {
            color: var(--positive);
        }

        .total-row strong.negative {
            color: var(--negative);
        }

        .source-guide {
            margin-top: 24px;
        }

        .source-guide h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }

        .source-guide > p {
            font-size: 0.8125rem;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .source-guide table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        .source-guide th, .source-guide td {
            padding: 8px 12px;
            text-align: left;
            border: 1px solid var(--border);
        }

        .source-guide th {
            background: var(--muted-light);
            font-weight: 600;
        }

        .document-info {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px dashed var(--border);
            font-size: 0.8125rem;
            color: var(--muted);
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .document-info p {
            margin: 0;
        }

        .document-info strong {
            color: var(--ink);
        }

        .no-print {
            margin-top: 24px;
            text-align: center;
        }

        .no-print button {
            background: var(--amber);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 1rem;
        }

        .no-print button:hover {
            background: #d97706;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
</head>
<body>
<div class="financial-register">
    <div class="register-header">
        <h1>{{ __('app.financial_register') }} — {{ $period_label }}</h1>
        <p class="reference">{{ $reference }}</p>
    </div>

    <div class="bilingual">
        <div class="fr">{{ __('app.financial_register') }}</div>
        <div class="ti">{{ __('app.financial_register') }}</div>
    </div>

    <div class="register-meta">
        <p><strong>{{ __('app.period') }}:</strong> {{ $period_start->format('d/m/Y') }} – {{ $period_end->format('d/m/Y') }}</p>
        <p><strong>{{ __('app.rf_reference') }}:</strong> {{ $reference }}</p>
    </div>

    <div class="opening-balance">
        <span class="label">Solde d'ouverture / መእተዊ ተረፍ</span>
        <span class="amount">{{ number_format($opening_balance, 2, ',', ' ') }} €</span>
        <span class="note-line"></span>
    </div>

    <section class="table-section">
        <table>
            <thead>
                <tr>
                    <th style="width: 40px;">{{ __('app.no') }}</th>
                    <th style="width: 130px;">{{ __('app.rf_reference') }}</th>
                    <th style="width: 80px;">{{ __('app.date') }}</th>
                    <th style="width: 120px;">{{ __('app.source_document_reference') }}</th>
                    <th>{{ __('app.type_category') }}</th>
                    <th class="income" style="width: 90px;">{{ __('app.income') }} (€)</th>
                    <th class="expense" style="width: 90px;">{{ __('app.expense') }} (€)</th>
                    <th style="width: 90px;">{{ __('app.balance') }} (€)</th>
                    <th>{{ __('app.payment_method_notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $index => $entry)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="ref">{{ $entry['line_reference'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($entry['entry_date'])->format('d/m/Y') }}</td>
                    <td>{{ $entry['source_reference'] }}</td>
                    <td>{{ $entry['category_label'] }}</td>
                    <td class="income">{{ $entry['type'] === 'income' ? number_format($entry['amount'], 2, ',', ' ') : '' }}</td>
                    <td class="expense">{{ $entry['type'] === 'expense' ? number_format($entry['amount'], 2, ',', ' ') : '' }}</td>
                    <td>{{ number_format($entry['running_balance'], 2, ',', ' ') }}</td>
                    <td>{{ trim($entry['payment_method'] . ($entry['notes'] ? ' | ' . $entry['notes'] : '')) }}</td>
                </tr>
                @endforeach
                @for ($i = $entries->count() + 1; $i <= $empty_rows_count; $i++)
                <tr>
                    <td>{{ $i }}</td><td class="ref">{{ $reference }}-{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="empty-cell">—</td><td class="empty-cell">—</td><td class="empty-cell">—</td>
                    <td class="income empty-cell">—</td><td class="expense empty-cell">—</td><td class="empty-cell">—</td><td class="empty-cell">—</td>
                </tr>
                @endfor
            </tbody>
        </table>
    </section>

    <div class="register-totals">
        <div class="total-row">
            <span>{{ __('app.total_income') }}</span>
            <strong class="positive">{{ number_format($total_income, 2, ',', ' ') }}</strong>
        </div>
        <div class="total-row">
            <span>{{ __('app.total_expense') }}</span>
            <strong class="negative">{{ number_format($total_expense, 2, ',', ' ') }}</strong>
        </div>
        <div class="total-row">
            <span>{{ __('app.closing_balance') }}</span>
            <strong>{{ number_format($closing_balance, 2, ',', ' ') }}</strong>
        </div>
    </div>

    <section class="source-guide">
        <h3>{{ __('app.source_guide') }}</h3>
        <p>{{ __('app.source_document_reference_guide') }}</p>
        <table>
            <thead>
                <tr>
                    <th>Code</th>
                    <th>{{ __('app.french') }}</th>
                    <th>{{ __('app.tigrigna') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sourceGuide as $code => $labels)
                <tr>
                    <td>{{ $code }}</td>
                    <td>{{ $labels['fr'] }}</td>
                    <td>{{ $labels['ti'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </section>

    <div class="document-info">
        <p><strong>{{ __('app.document_reference') }}:</strong> {{ $reference }}</p>
        <p><strong>{{ __('app.period') }}:</strong> {{ $period_label }}</p>
        <p><strong>{{ __('app.generation_date') }}:</strong> {{ now()->format('d/m/Y H:i') }}</p>
        <p><strong>{{ __('app.generated_by') }}:</strong> {{ auth()->user()?->name }}</p>
    </div>

    <div class="no-print">
        <button onclick="window.print()">{{ __('app.print_save_pdf') }}</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        window.print();
    }, 500);
});
</script>
</body>
</html>
