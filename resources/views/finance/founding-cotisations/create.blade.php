@extends('layouts.app')
@section('content')

<div class="payment-page">
    {{-- Page Header --}}
    <div class="payment-header">
        <div>
            <p class="eyebrow">{{ __('app.founding_cotisations') }}</p>
            <h1>{{ __('app.add_founding_cotisation') }}</h1>
            <p class="subtitle">Enregistrer une nouvelle cotisation fondatrice pour un membre</p>
        </div>
        <a href="{{ route('founding-cotisations.index') }}" class="button secondary">
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

    <form method="POST" action="{{ route('founding-cotisations.store') }}">
        @csrf

        {{-- Section 1: Member --}}
        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-icon teal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><path d="M16 4.5a3 3 0 0 1 0 6M18 14.5c1.8.8 3 2.6 3 4.5"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.member') }}</h2>
                    <p class="muted">Sélectionnez le membre concerné par la cotisation fondatrice</p>
                </div>
            </div>

            <div class="form-section-body">
                <div class="form-group">
                    <label for="member_id">{{ __('app.member') }}</label>
                    <select name="member_id" id="member_id" required>
                        <option value="">{{ __('app.select_member') }}</option>
                        @foreach($members as $member)
                            <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>{{ $member->given_name }} {{ $member->family_name }} ({{ $member->member_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Section 2: Amount --}}
        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M12 3v18M16.5 7.5C16.5 5.6 14.4 4 12 4S7.5 5.6 7.5 7.5 9.6 11 12 11s4.5 1.6 4.5 3.5S14.4 18 12 18s-4.5-1.6-4.5-3.5"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.amount') }}</h2>
                    <p class="muted">Montant de la cotisation fondatrice (maximum 200 €)</p>
                </div>
            </div>

            <div class="form-section-body">
                <div class="amount-display">
                    <div class="amount-field">
                        <label for="amount">{{ __('app.amount') }} (€)</label>
                        <input type="number" id="amount" name="amount" value="{{ old('amount', 0) }}" min="0" max="200" step="1" required
                               oninput="document.getElementById('remaining').value = 200 - this.value">
                    </div>

                    <div class="amount-remaining">
                        <label>{{ __('app.remaining') }} (€)</label>
                        <div class="remaining-value" id="remainingDisplay">{{ number_format(old('remaining', 200), 0, ',', ' ') }} €</div>
                        <input type="hidden" id="remaining" name="remaining" value="{{ old('remaining', 200) }}">
                    </div>
                </div>

                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill" style="width: {{ old('amount', 0) > 0 ? (old('amount', 0) / 200 * 100) : 0 }}%"></div>
                </div>
                <p class="progress-label"><span id="progressPercent">{{ old('amount', 0) }}</span> / 200 € versés</p>
            </div>
        </div>

        {{-- Section 3: Payment Details --}}
        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.payment_date') }} & {{ __('app.payment_method') }}</h2>
                    <p class="muted">Informations sur le paiement</p>
                </div>
            </div>

            <div class="form-section-body">
                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label for="payment_date">{{ __('app.payment_date') }}</label>
                        <input type="date" name="payment_date" id="payment_date" value="{{ old('payment_date') }}">
                    </div>
                    <div class="form-group">
                        <label for="payment_method">{{ __('app.payment_method') }}</label>
                        <select name="payment_method" id="payment_method">
                            <option value="">{{ __('app.select') }}</option>
                            <option value="cash" @selected(old('payment_method') == 'cash')>{{ __('app.cash') }}</option>
                            <option value="transfer" @selected(old('payment_method') == 'transfer')>{{ __('app.transfer') }}</option>
                            <option value="check" @selected(old('payment_method') == 'check')>{{ __('app.check') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 4: Notes --}}
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
            <a class="button secondary" href="{{ route('founding-cotisations.index') }}">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>

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
.form-section-icon.blue { background: #e8f1f7; color: #4a86ab; }
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
.form-row-2 { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }

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

/* Amount display */
.amount-display {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.amount-field label,
.amount-remaining label {
    font-weight: 600;
    font-size: 0.8125rem;
    color: var(--ink);
    margin-bottom: 5px;
    display: block;
}

.amount-field input {
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--bg);
    color: var(--ink);
    font-size: 0.9375rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.amount-field input:focus {
    outline: none;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(20, 123, 120, 0.12);
}

.remaining-value {
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--muted-light);
    color: var(--muted);
    font-size: 0.9375rem;
    font-weight: 600;
}

/* Progress bar */
.progress-bar {
    height: 8px;
    background: #e6f1ee;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 6px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #229b8e, #147b78);
    border-radius: 10px;
    transition: width 0.3s ease;
}

.progress-label {
    margin: 0;
    color: var(--muted);
    font-size: 11px;
    text-align: right;
}

/* Footer */
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

/* Responsive */
@media (max-width: 520px) {
    .amount-display { grid-template-columns: 1fr; }
    .form-row-2 { grid-template-columns: 1fr; }
    .payment-header { flex-direction: column; align-items: flex-start; }
    .form-footer { flex-direction: column; }
    .form-footer .button-primary,
    .form-footer .button.secondary { width: 100%; justify-content: center; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('amount');
    const remainingDisplay = document.getElementById('remainingDisplay');
    const remainingInput = document.getElementById('remaining');
    const progressFill = document.getElementById('progressFill');
    const progressPercent = document.getElementById('progressPercent');

    function updateDisplay() {
        const amount = parseFloat(amountInput.value) || 0;
        const remaining = 200 - amount;
        const percent = Math.min(100, Math.max(0, amount / 200 * 100));

        remainingInput.value = remaining;
        remainingDisplay.textContent = remaining.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }) + ' €';
        progressFill.style.width = percent + '%';
        progressPercent.textContent = amount.toLocaleString('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    amountInput.addEventListener('input', updateDisplay);
    updateDisplay();
});
</script>
@endsection
