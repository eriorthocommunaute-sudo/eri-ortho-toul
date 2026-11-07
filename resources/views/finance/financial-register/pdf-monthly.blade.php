@extends('layouts.app')
@section('content')
<div class="financial-register">
    <div class="register-header">
        <div class="title-section">
            <h1>{{ __('app.financial_register') }} — {{ $period_label }}</h1>
            <p class="reference">{{ $reference }}</p>
        </div>
        <div class="bilingual">
            <div class="fr">{{ __('app.financial_register') }}</div>
            <div class="ti">{{ __('app.financial_register') }}</div>
        </div>
    </div>

    <div class="register-meta">
        <p><strong>{{ __('app.period') }}:</strong> {{ $period_start->format('d/m/Y') }} – {{ $period_end->format('d/m/Y') }}</p>
        <p><strong>{{ __('app.rf_reference') }}:</strong> {{ $reference }}</p>
    </div>

    <section class="table-section">
        <table>
            <thead>
                <tr>
                    <th>{{ __('app.no') }}</th>
                    <th>{{ __('app.rf_reference') }}</th>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.source_document_reference') }}</th>
                    <th>{{ __('app.type_category') }}</th>
                    <th class="income">{{ __('app.income') }} (€)</th>
                    <th class="expense">{{ __('app.expense') }} (€)</th>
                    <th>{{ __('app.balance') }} (€)</th>
                    <th>{{ __('app.payment_method_notes') }}</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= $empty_rows_count; $i++)
                <tr>
                    <td>{{ $i }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="income"></td>
                    <td class="expense"></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
            </tbody>
        </table>
    </section>

    <div class="register-totals">
        <div class="total-row">
            <span>{{ __('app.total_income') }}:</span>
            <strong class="positive"></strong>
        </div>
        <div class="total-row">
            <span>{{ __('app.total_expense') }}:</span>
            <strong class="negative"></strong>
        </div>
        <div class="total-row">
            <span>{{ __('app.closing_balance') }}:</span>
            <strong></strong>
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
</div>
@endsection