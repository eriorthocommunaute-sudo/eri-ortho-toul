@extends('layouts.app')
@section('content')
<div class="financial-register-form">
    <div class="form-header">
        <h2>{{ __('app.financial_register') }}</h2>
        <p class="subtitle">{{ __('app.select_period_type_and_year') }}</p>
    </div>

    <div class="form-card">
        <form method="POST" id="registerForm">
            @csrf

            <div class="register-fields">
                <div class="field-row">
                    <div class="form-group period-type-selector">
                        <label>{{ __('app.period_type') }}</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="period_type" value="monthly" checked onchange="updateFields()">
                                <span class="radio-label">{{ __('app.monthly') }}</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="period_type" value="quarterly" onchange="updateFields()">
                                <span class="radio-label">{{ __('app.quarterly') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="field-row">
                    <div class="form-group">
                        <label for="year_select">{{ __('app.year') }}</label>
                        <select name="year" id="year_select" required>
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ request('year', $currentYear) == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-row monthly-field" id="month_field">
                    <div class="form-group">
                        <label for="month_select">{{ __('app.month') }}</label>
                        <select name="month" id="month_select" required>
                            @foreach($months as $month)
                                <option value="{{ $month }}" {{ request('month', $currentMonth) == $month ? 'selected' : '' }}>
                                    {{ ucfirst(__('app.months_short.'.($month - 1))) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field-row monthly-field" id="blank_register_field">
                    <div class="form-group">
                        <label class="checkbox-option">
                            <input type="checkbox" name="blank_register" value="1">
                            <span>{{ __('app.blank_financial_register') }}</span>
                        </label>
                    </div>
                </div>

                <div class="field-row quarterly-field" id="quarter_field" style="display:none;">
                    <div class="form-group">
                        <label for="quarter_select">{{ __('app.quarter') }}</label>
                        <select name="quarter" id="quarter_select" required>
                            @foreach($quarters as $quarter)
                                <option value="{{ $quarter }}" {{ request('quarter') == $quarter ? 'selected' : '' }}>
                                    {{ __('app.quarter') }} {{ $quarter }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="button-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    {{ __('app.open_for_print') }}
                </button>
                <a href="{{ route('financial-register.list') }}" class="button-secondary">
                    {{ __('app.cancel') }}
                </a>
            </div>
        </form>
    </div>

    <!-- Preview cards showing register references -->
    <div class="preview-section">
        <h3>{{ __('app.register_preview') }}</h3>
        <div class="preview-cards">
            <div class="preview-card monthly">
                <div class="preview-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div class="preview-info">
                    <h4>{{ __('app.monthly_register') }}</h4>
                    <p class="reference">RF-2026-10</p>
                    <p class="period">{{ __('app.period') }}: 01/10/2026 – 31/10/2026</p>
                </div>
            </div>
            <div class="preview-card quarterly">
                <div class="preview-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <line x1="3" y1="14" x2="21" y2="14"></line>
                    </svg>
                </div>
                <div class="preview-info">
                    <h4>{{ __('app.quarterly_register') }}</h4>
                    <p class="reference">RF-2026-10-11-12</p>
                    <p class="period">{{ __('app.period') }}: 01/10/2026 – 31/12/2026</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.financial-register-form {
    max-width: 720px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
}

.form-header {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.form-header h2 {
    font-size: 1.75rem;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 0.5rem;
}

.subtitle {
    color: var(--muted);
    font-size: 1rem;
}

.form-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-sm);
}

.register-fields {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.field-row {
    width: 100%;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 500;
    font-size: 0.875rem;
    color: var(--ink);
}

.form-group select {
    padding: 0.625rem 0.875rem;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--bg);
    color: var(--ink);
    font-size: 0.9375rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group select:focus {
    outline: none;
    border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(20, 123, 120, 0.15);
}

.checkbox-option {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    cursor: pointer;
}

.checkbox-option input {
    accent-color: var(--teal);
    width: 18px;
    height: 18px;
}

.period-type-selector .radio-group {
    display: flex;
    gap: 1rem;
}

.radio-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border: 2px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    flex: 1;
    text-align: center;
}

.radio-option:hover {
    border-color: var(--teal);
    background: rgba(20, 123, 120, 0.05);
}

.radio-option input[type="radio"] {
    accent-color: var(--teal);
    width: 18px;
    height: 18px;
}

.radio-option input[type="radio"]:checked + .radio-label {
    color: var(--teal);
    font-weight: 600;
}

.radio-option input[type="radio"]:checked {
    border-color: var(--teal);
}

.radio-label {
    font-size: 0.9375rem;
    color: var(--ink);
}

.form-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.button-primary, .button-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.25rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.9375rem;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    text-decoration: none;
}

.button-primary {
    background: var(--teal);
    color: white;
}

.button-primary:hover {
    background: var(--teal-dark);
    transform: translateY(-1px);
}

.button-secondary {
    background: var(--bg);
    color: var(--ink);
    border: 1px solid var(--border);
}

.button-secondary:hover {
    background: var(--muted-light);
    border-color: var(--muted);
}

.preview-section {
    margin-top: 2rem;
}

.preview-section h3 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 1rem;
}

.preview-cards {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.preview-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 1.25rem;
    display: flex;
    gap: 1rem;
    transition: all 0.2s;
}

.preview-card:hover {
    border-color: var(--teal);
    box-shadow: var(--shadow-md);
}

.preview-card.monthly {
    border-left: 4px solid var(--teal);
}

.preview-card.quarterly {
    border-left: 4px solid var(--amber);
}

.preview-card.monthly .preview-icon {
    background: rgba(20, 123, 120, 0.1);
    color: var(--teal);
}

.preview-card.quarterly .preview-icon {
    background: rgba(245, 158, 11, 0.1);
    color: var(--amber);
}

.preview-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.preview-icon svg {
    width: 24px;
    height: 24px;
}

.preview-info h4 {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 0.25rem;
}

.preview-info .reference {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.8125rem;
    color: var(--teal);
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.preview-info .period {
    font-size: 0.8125rem;
    color: var(--muted);
}

@media (max-width: 640px) {
    .preview-cards {
        grid-template-columns: 1fr;
    }
    .form-actions {
        flex-direction: column;
    }
    .button-primary, .button-secondary {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
function updateFields() {
    const periodType = document.querySelector('input[name="period_type"]:checked').value;
    const form = document.getElementById('registerForm');
    
    // Hide all fields
    document.getElementById('month_field').style.display = 'none';
    document.getElementById('blank_register_field').style.display = 'none';
    document.getElementById('quarter_field').style.display = 'none';
    
    // Show appropriate field and update form action
    if (periodType === 'monthly') {
        document.getElementById('month_field').style.display = 'block';
        document.getElementById('blank_register_field').style.display = 'block';
        form.action = "{{ route('financial-register.generate-monthly') }}";
    } else {
        document.getElementById('quarter_field').style.display = 'block';
        form.action = "{{ route('financial-register.generate-quarterly') }}";
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', updateFields);
</script>
@endsection
