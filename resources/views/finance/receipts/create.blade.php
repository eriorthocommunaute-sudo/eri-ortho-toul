@extends('layouts.app')
@section('content')

<div class="payment-page">
    {{-- Page Header --}}
    <div class="payment-header">
        <div>
            <h1>{{ __('app.record_payment') }}</h1>
            <p class="subtitle">{{ __('app.payment_sub') }}</p>
        </div>
        <a href="{{ route('receipts.index') }}" class="button secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            {{ __('app.back') }}
        </a>
    </div>

    {{-- Validation Errors --}}
    @if (count($errors) > 0)
        <div class="alert error-list">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('receipts.store') }}" class="payment-form">
        @csrf

        {{-- Section 1: Member & Contribution --}}
        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-icon teal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M16 4.5a3 3 0 0 1 0 6M18 14.5c1.8.8 3 2.6 3 4.5"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.member') }} & {{ __('app.contribution') }}</h2>
                    <p class="muted">Sélectionnez le membre et les cotisations concernées</p>
                </div>
            </div>

            <div class="form-section-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="member_id">{{ __('app.member') }}</label>
                        <select name="member_id" id="member_id" required>
                            <option value="">{{ __('app.select_optional') }}</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" @selected(old('member_id') == $m->id || request('member_id') == $m->id)>
                                    {{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contribution_ids">
                            {{ __('app.contribution') }}
                            <span class="label-hint">{{ __('app.max_3_months') }}</span>
                        </label>
                        <select name="contribution_ids[]" id="contribution_ids" multiple required class="multi-select">
                            <option value="">{{ __('app.select_optional') }}</option>
                            @foreach($contributions as $c)
                                <option value="{{ $c->id }}" @selected(in_array($c->id, (array) old('contribution_ids', request('contribution_ids', []))))>
                                    {{ $c->reference }} ({{ $c->month }}/{{ $c->year }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Custom references for each selected contribution (optional) --}}
                <div id="custom-references" class="custom-refs" style="display: none;">
                    <h4>{{ __('app.custom_references') ?? 'Références personnalisées' }}</h4>
                    <div id="custom-ref-fields"></div>
                    <small class="muted">{{ __('app.custom_ref_hint') ?? 'Laissez vide pour utiliser la référence automatique.' }}</small>
                </div>
            </div>
        </div>

        {{-- Section 2: Payment Details --}}
        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="2.5" y="6" width="19" height="13" rx="2.5"/><path d="M2.5 10h19M16 15h2"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.amount') }} & {{ __('app.payment_method') }}</h2>
                    <p class="muted">Détails du paiement</p>
                </div>
            </div>

            <div class="form-section-body">
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label for="payment_date">{{ __('app.payment_date') }}</label>
                        <input name="payment_date" id="payment_date" type="date"
                               value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                               required max="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="amount">{{ __('app.amount') }} (€)</label>
                        <input name="amount" id="amount" type="number" step="0.01" min="0.01"
                               value="{{ old('amount', 10.00) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">{{ __('app.payment_method') }}</label>
                        <select name="payment_method" id="payment_method" required>
                            @foreach(['cash', 'transfer', 'check', 'other'] as $method)
                                <option value="{{ $method }}" @selected(old('payment_method') == $method)>{{ __('app.'.$method) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 3: Notes --}}
        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-icon orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M14 3H6.5A1.5 1.5 0 0 0 5 4.5v15A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5V8l-5-5Z"/><path d="M14 3v5h5M8.5 12h7M8.5 16h5"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.notes') }}</h2>
                    <p class="muted">Informations complémentaires (optionnel)</p>
                </div>
            </div>

            <div class="form-section-body">
                <div class="form-group">
                    <textarea name="notes" rows="3" placeholder="...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="form-footer">
            <button type="submit" class="button-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                {{ __('app.save') }}
            </button>
            <a class="button secondary" href="{{ route('receipts.index') }}">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>

 {{-- Data for JavaScript --}}
<div id="paid-contributions" data-value='{!! json_encode($paidByContribution ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}' style="display:none;"></div>
<div data-select-optional="{{ __('app.select_optional') }}" style="display:none;"></div>
<div data-max-months-alert="{{ __('app.max_3_months_alert') }}" style="display:none;"></div>

<style>
.payment-page {
    max-width: 800px;
    margin: 0 auto;
}

.payment-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.payment-header h1 {
    margin: 0 0 4px;
    font-size: 1.375rem;
    font-weight: 700;
    color: var(--ink);
}
.payment-header .subtitle {
    margin: 0;
    color: var(--muted);
    font-size: 0.875rem;
}

.error-list {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 1.25rem;
}
.error-list ul {
    margin: 0;
    padding-left: 1.25rem;
}
.error-list li {
    font-size: 0.875rem;
    margin-bottom: 4px;
}
.error-list li:last-child { margin-bottom: 0; }

/* Form Sections */
.form-section {
    background: #fff;
    border: 1px solid #e8eeee;
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    margin-bottom: 1.25rem;
    overflow: hidden;
}

.form-section-head {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 21px;
    background: var(--bg);
    border-bottom: 1px solid var(--border);
}
.form-section-head h2 {
    margin: 0;
    font-size: 0.9375rem;
    font-weight: 700;
    color: var(--ink);
}
.form-section-head .muted {
    margin: 2px 0 0;
    font-size: 11px;
}

.form-section-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    flex-shrink: 0;
}
.form-section-icon svg { width: 18px; height: 18px; }
.form-section-icon.teal { background: #e3f4ef; color: #147b78; }
.form-section-icon.green { background: #e6f7ef; color: #218b67; }
.form-section-icon.orange { background: #fdf1e2; color: #c9822f; }

.form-section-body {
    padding: 21px;
}

.form-row {
    display: grid;
    gap: 1rem;
    margin-bottom: 1rem;
}
.form-row:last-child { margin-bottom: 0; }
.form-row-3 { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group label {
    font-weight: 600;
    font-size: 0.8125rem;
    color: var(--ink);
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 8px;
}

.label-hint {
    font-weight: 400;
    font-size: 10px;
    color: var(--muted);
    text-align: right;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--bg);
    color: var(--ink);
    font-size: 0.9375rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(20, 123, 120, 0.12);
}

.form-group textarea {
    resize: vertical;
    font-family: inherit;
}

.multi-select {
    height: 130px !important;
    padding: 8px !important;
}
.multi-select option {
    padding: 6px 8px;
    border-bottom: 1px solid var(--border);
}

.custom-refs {
    margin-top: 1.25rem;
    padding: 14px;
    background: var(--bg);
    border: 1px dashed var(--border);
    border-radius: 10px;
}
.custom-refs h4 {
    margin: 0 0 10px;
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--ink);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-footer {
    display: flex;
    gap: 10px;
    padding-top: 1.25rem;
    border-top: 1px solid var(--border);
    margin-top: 0.5rem;
}

.button-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--teal);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: background 0.15s;
    text-decoration: none;
}
.button-primary:hover { background: var(--teal-dark); text-decoration: none; color: #fff; }
.button-primary svg { width: 16px; height: 16px; }

.button.secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: var(--bg);
    color: var(--ink);
    border: 1px solid var(--border);
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.9375rem;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
}
.button.secondary:hover { background: var(--muted-light); text-decoration: none; color: var(--ink); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const memberSelect = document.getElementById('member_id');
    const contributionSelect = document.getElementById('contribution_ids');

    // Store all contribution options (skip first empty option)
    const allOptions = Array.from(contributionSelect.querySelectorAll('option')).slice(1);

    // Data from server
    const paidByContribution = JSON.parse(document.getElementById('paid-contributions').dataset.value) || {};
    const selectOptional = document.querySelector('[data-select-optional]').dataset.selectOptional;
    const maxMonthsAlert = document.querySelector('[data-max-months-alert]').dataset.maxMonthsAlert;

    // Preserve values pre-selected by old()/request() before any filtering
    contributionSelect.dataset.savedValues = JSON.stringify(
        Array.from(contributionSelect.selectedOptions).map(o => o.value).filter(v => v !== '')
    );

    function filterContributions() {
        const selectedMemberId = parseInt(memberSelect.value, 10);

        contributionSelect.innerHTML = '<option value="">' + selectOptional + '</option>';

        allOptions.forEach(option => {
            // PHP may serialize the member list as an array or as an object ({"0":1,"2":5}): normalize to numbers
            const paidMembers = Object.values(paidByContribution[option.value] || {}).map(Number);

            // Show if no member selected, or member hasn't paid this contribution yet
            if (!selectedMemberId || !paidMembers.includes(selectedMemberId)) {
                contributionSelect.appendChild(option.cloneNode(true));
            }
        });

        // Restore selected values if still valid
        const savedValues = contributionSelect.dataset.savedValues ? JSON.parse(contributionSelect.dataset.savedValues) : [];
        savedValues.forEach(val => {
            const opt = contributionSelect.querySelector('option[value="' + val + '"]');
            if (opt) opt.selected = true;
        });
    }

    memberSelect.addEventListener('change', filterContributions);

    contributionSelect.addEventListener('change', function() {
        // Limit to 3 selections
        if (this.selectedOptions.length > 3) {
            this.selectedOptions[this.selectedOptions.length - 1].selected = false;
            alert(maxMonthsAlert);
        }
        // Save current values for re-filtering
        this.dataset.savedValues = JSON.stringify(
            Array.from(this.selectedOptions).map(opt => opt.value).filter(v => v !== '')
        );
    });

    // Initial filter if member is pre-selected
    if (memberSelect.value) {
        filterContributions();
    }
});
</script>
@endsection
