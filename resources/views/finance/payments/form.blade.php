@extends('layouts.app')

@section('content')
<div class="payment-page">
    <div class="payment-header">
        <div>
            <h1>{{ __('app.record_payment') }}</h1>
            <p class="subtitle">{{ __('app.payment_sub') }}</p>
        </div>
        <a href="{{ route('payments.index') }}" class="button secondary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            {{ __('app.back') }}
        </a>
    </div>

    @if (count($errors) > 0)
        <div class="alert error-list">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('payments.store') }}" class="payment-form">
        @csrf

        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-icon teal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6M18 8v6M15 11h6"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.member') }}</h2>
                    <p class="muted">{{ __('app.payment_member_hint') }}</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="member_id">{{ __('app.member') }}</label>
                        <select name="member_id" id="member_id" required>
                            <option value="">{{ __('app.select_member') }}</option>
                            @foreach ($members as $m)
                                <option value="{{ $m->id }}" @selected(old('member_id') == $m->id || ($member && $member->id == $m->id))>
                                    {{ $m->member_code }} - {{ $m->given_name }} {{ $m->family_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="2.5" y="6" width="19" height="13" rx="2.5"/><path d="M2.5 10h19M16 15h2"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.amount') }} & {{ __('app.payment_date') }}</h2>
                    <p class="muted">{{ __('app.payment_details_hint') }}</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label for="payment_date">{{ __('app.payment_date') }}</label>
                        <input name="payment_date" id="payment_date" type="date"
                               value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="amount">{{ __('app.amount') }} (€)</label>
                        <input name="amount" id="amount" type="number" step="0.01" min="0.01"
                               value="{{ old('amount') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">{{ __('app.payment_method') }}</label>
                        <select name="payment_method" id="payment_method" required>
                            @foreach (['cash', 'bank_transfer'] as $method)
                                <option value="{{ $method }}" @selected(old('payment_method') == $method)>{{ __('app.payment_method_' . $method) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-icon orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2.5"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.months_covered') }}</h2>
                    <p class="muted">{{ __('app.months_covered_hint') }}</p>
                </div>
            </div>
            <div class="form-section-body">
                <div id="monthGroups" class="month-groups"></div>
                <input type="hidden" name="months_covered_backup" value="">
                <div id="monthCounter" class="month-counter"></div>
                <small class="muted">{{ __('app.months_auto_hint') }}</small>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-icon orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><path d="M14 3H6.5A1.5 1.5 0 0 0 5 4.5v15A1.5 1.5 0 0 0 6.5 21h11a1.5 1.5 0 0 0 1.5-1.5V8l-5-5Z"/><path d="M14 3v5h5M8.5 12h7M8.5 16h5"/></svg>
                </span>
                <div>
                    <h2>{{ __('app.notes') }}</h2>
                    <p class="muted">{{ __('app.optional') }}</p>
                </div>
            </div>
            <div class="form-section-body">
                <div class="form-group">
                    <textarea name="notes" rows="3" placeholder="...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button type="submit" class="button-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                {{ __('app.save') }}
            </button>
            <a class="button secondary" href="{{ route('payments.index') }}">{{ __('app.cancel') }}</a>
        </div>
    </form>
</div>

<style>
.payment-page { max-width: 900px; margin: 0 auto; }
.payment-header {
    display: flex; align-items: center; justify-content: space-between; gap: 1rem;
    flex-wrap: wrap; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);
}
.payment-header h1 { margin: 0 0 4px; font-size: 1.375rem; font-weight: 700; color: var(--ink); }
.payment-header .subtitle { margin: 0; color: var(--muted); font-size: 0.875rem; }
.error-list {
    background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
    padding: 12px 16px; border-radius: 10px; margin-bottom: 1.25rem;
}
.error-list ul { margin: 0; padding-left: 1.25rem; }
.error-list li { font-size: 0.875rem; margin-bottom: 4px; }
.error-list li:last-child { margin-bottom: 0; }
.form-section {
    background: #fff; border: 1px solid #e8eeee; border-radius: var(--radius);
    box-shadow: var(--shadow); margin-bottom: 1.25rem; overflow: hidden;
}
.form-section-head {
    display: flex; align-items: center; gap: 10px; padding: 16px 21px;
    background: var(--bg); border-bottom: 1px solid var(--border);
}
.form-section-head h2 { margin: 0; font-size: 0.9375rem; font-weight: 700; color: var(--ink); }
.form-section-head .muted { margin: 2px 0 0; font-size: 11px; }
.form-section-icon {
    display: flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
}
.form-section-icon svg { width: 18px; height: 18px; }
.form-section-icon.teal { background: #e3f4ef; color: #147b78; }
.form-section-icon.green { background: #e6f7ef; color: #218b67; }
.form-section-icon.orange { background: #fdf1e2; color: #c9822f; }
.form-section-body { padding: 21px; }
.form-row { display: grid; gap: 1rem; margin-bottom: 1rem; }
.form-row:last-child { margin-bottom: 0; }
.form-row-3 { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-group label { font-weight: 600; font-size: 0.8125rem; color: var(--ink); }
.form-group input, .form-group select, .form-group textarea {
    padding: 0.625rem 0.875rem; border: 1px solid var(--border); border-radius: 8px;
    background: var(--bg); color: var(--ink); font-size: 0.9375rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus {
    outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(20, 123, 120, 0.12);
}
.form-group textarea { resize: vertical; font-family: inherit; }
.form-footer {
    display: flex; gap: 10px; padding-top: 1.25rem; border-top: 1px solid var(--border);
}
.button-primary {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
    background: var(--teal); color: #fff; border: none; border-radius: 8px;
    font-weight: 600; font-size: 0.9375rem; cursor: pointer; transition: background 0.15s; text-decoration: none;
}
.button-primary:hover { background: var(--teal-dark); color: #fff; text-decoration: none; }
.button-primary svg { width: 16px; height: 16px; }
.button.secondary {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px;
    background: var(--bg); color: var(--ink); border: 1px solid var(--border); border-radius: 8px;
    font-weight: 500; font-size: 0.9375rem; cursor: pointer; transition: all 0.15s; text-decoration: none;
}
.button.secondary:hover { background: var(--muted-light); color: var(--ink); text-decoration: none; }

.month-groups { display: grid; gap: 14px; }
.month-year {
    border: 1px solid var(--border); border-radius: 10px; padding: 12px 14px; background: var(--bg);
}
.month-year legend {
    font-weight: 700; font-size: 0.8125rem; color: var(--ink); padding: 0 6px;
}
.month-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); gap: 8px; }
.month-item {
    display: flex; align-items: center; gap: 7px; padding: 7px 9px;
    background: #fff; border: 1px solid var(--border); border-radius: 8px;
    font-size: 12.5px; cursor: pointer; user-select: none; transition: all 0.12s;
}
.month-item:hover { border-color: var(--teal); }
.month-item.checked { background: #e6f7ef; border-color: #218b67; color: #176c50; font-weight: 600; }
.month-item input { accent-color: var(--teal); cursor: pointer; }
.month-counter {
    margin-top: 14px; padding: 11px 14px; border-radius: 9px;
    background: #edf2fc; color: #43609b; font-size: 12.5px; font-weight: 600;
}
.month-counter.warn { background: #fff5e5; color: #a06a1f; }
.month-counter.ok { background: #e6f7ef; color: #176c50; }
.muted { color: var(--muted); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const MONTH_SHORT = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
    const MONTH_FULL = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    const MONTHLY_EXPECTED = {{ (float) config('finance.default_monthly_contribution', 10.00) }};

    const dateInput = document.getElementById('payment_date');
    const amountInput = document.getElementById('amount');
    const container = document.getElementById('monthGroups');
    const counter = document.getElementById('monthCounter');
    const backup = document.querySelector('[name="months_covered_backup"]');

    const oldMonths = @json(old('months_covered', []));
    let restored = Array.isArray(oldMonths) ? oldMonths.slice() : [];
    if (!restored.length && backup && backup.value) {
        try { restored = JSON.parse(backup.value) || []; } catch (e) { restored = []; }
    }

    function selectedSet() {
        return new Set(Array.from(container.querySelectorAll('input:checked')).map(i => i.value));
    }

    function renderMonths() {
        const keep = restored.length ? new Set(restored) : selectedSet();
        restored = [];
        const dateStr = (dateInput.value || '{{ now()->format('Y-m-d') }}');
        const baseYear = parseInt(dateStr.slice(0, 4), 10);

        container.innerHTML = '';
        for (let offset = -1; offset <= 1; offset++) {
            const year = baseYear + offset;
            const fieldset = document.createElement('fieldset');
            fieldset.className = 'month-year';
            const legend = document.createElement('legend');
            legend.textContent = year;
            fieldset.appendChild(legend);

            const grid = document.createElement('div');
            grid.className = 'month-grid';

            for (let m = 1; m <= 12; m++) {
                const value = year + '-' + String(m).padStart(2, '0');
                const item = document.createElement('label');
                item.className = 'month-item';
                const cb = document.createElement('input');
                cb.type = 'checkbox';
                cb.name = 'months_covered[]';
                cb.value = value;
                if (keep.has(value)) { cb.checked = true; item.classList.add('checked'); }
                const span = document.createElement('span');
                span.textContent = MONTH_SHORT[m - 1];
                item.appendChild(cb);
                item.appendChild(span);
                item.title = MONTH_FULL[m - 1] + ' ' + year;
                cb.addEventListener('change', function () {
                    item.classList.toggle('checked', cb.checked);
                    updateCounter();
                });
                grid.appendChild(item);
            }

            fieldset.appendChild(grid);
            container.appendChild(fieldset);
        }
        updateCounter();
    }

    function updateCounter() {
        const count = container.querySelectorAll('input:checked').length;
        const coverage = count * MONTHLY_EXPECTED;
        const amount = parseFloat(amountInput.value || '0') || 0;
        let text = count + ' mois sélectionnés · couverture : ' + coverage.toFixed(2).replace('.', ',') + ' €';
        let cls = 'month-counter';

        if (count === 0) {
            text = 'Sélectionnez au moins un mois couvert par ce paiement.';
            cls += ' warn';
        } else if (amount > coverage + 0.001) {
            const missing = Math.ceil((amount - coverage) / MONTHLY_EXPECTED);
            text += ' · montant supérieur à la couverture : ajoutez ' + missing + ' mois (' + (amount).toFixed(2).replace('.', ',') + ' €)';
            cls += ' warn';
        } else if (amount > 0 && amount < coverage) {
            text += ' · le trop-perçu reportera sur les mois sélectionnés restants';
            cls += ' ok';
        } else if (amount > 0) {
            cls += ' ok';
        }

        counter.className = cls;
        counter.textContent = text;
        if (backup) {
            backup.value = JSON.stringify(Array.from(container.querySelectorAll('input:checked')).map(i => i.value));
        }
    }

    function autoSelect() {
        const amount = parseFloat(amountInput.value || '0') || 0;
        if (!(amount > 0)) return;
        const needed = Math.ceil(amount / MONTHLY_EXPECTED);
        const dateStr = (dateInput.value || '{{ now()->format('Y-m-d') }}');
        let year = parseInt(dateStr.slice(0, 4), 10);
        let month = parseInt(dateStr.slice(5, 7), 10);

        const all = Array.from(container.querySelectorAll('input'));
        all.forEach(cb => { cb.checked = false; cb.closest('.month-item').classList.remove('checked'); });

        for (let i = 0; i < needed; i++) {
            const value = year + '-' + String(month).padStart(2, '0');
            const cb = all.find(c => c.value === value);
            if (cb) {
                cb.checked = true;
                cb.closest('.month-item').classList.add('checked');
            }
            month++;
            if (month > 12) { month = 1; year++; }
        }
        updateCounter();
    }

    dateInput.addEventListener('change', renderMonths);
    amountInput.addEventListener('change', autoSelect);

    renderMonths();
    if (amountInput.value && !restored.length) autoSelect();
});
</script>
@endsection
