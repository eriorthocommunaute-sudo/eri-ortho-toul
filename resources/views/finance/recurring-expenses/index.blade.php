@extends('layouts.app')

@section('content')
@php
    $monthNames = app()->getLocale() === 'ti'
        ? \App\Services\FinancialRegisterGenerator::MONTHS_TI
        : \App\Services\FinancialRegisterGenerator::MONTHS_FR;
@endphp
<div class="pagebar">
    <div>
        <p class="muted">Loyer, assurance et indemnité du prêtre — génération automatique mensuelle</p>
    </div>
    <form method="GET" class="tools">
        <input class="tool-input" type="number" name="year" value="{{ $year }}" min="2020" max="2100" aria-label="Année" style="width:90px;flex:none;">
        <select class="tool-select" name="month" aria-label="Mois">
            @foreach(range(1, 12) as $m)
                <option value="{{ $m }}" @selected($month === $m)>{{ $monthNames[$m] }}</option>
            @endforeach
        </select>
        <button class="button">Afficher</button>
    </form>
</div>

@if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert error">{{ $errors->first() }}</div>@endif

<section class="stats stats-3" style="grid-template-columns:repeat(auto-fit,minmax(280px,1fr));">
    @foreach($templates as $template)
        <article>
            <div class="kpi-head">
                <span class="kpi-ico {{ $template->is_active ? 'teal' : 'blue' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true"><rect x="2.5" y="6" width="19" height="13" rx="2.5"/><path d="M2.5 10h19M16 15h2"/></svg>
                </span>
                <span class="kpi-label">{{ $template->name }}</span>
            </div>
            <strong>{{ number_format((float) $template->amount, 2, ',', ' ') }} <small>&euro;</small></strong>
            <small>Jour d'échéance : {{ $template->due_day }} — {{ $template->beneficiary ?: 'Non défini' }}</small>

            <form method="POST" action="{{ route('recurring-expenses.templates.update', $template) }}" style="margin-top:14px;">
                @csrf @method('PUT')
                <div class="grid" style="grid-template-columns:1fr 1fr;gap:10px;">
                    <label style="margin:0;">Montant mensuel (€)
                        <input type="number" name="amount" min="0.01" step="0.01" value="{{ $template->amount }}" required>
                    </label>
                    <label style="margin:0;">Jour d'échéance
                        <input type="number" name="due_day" min="1" max="28" value="{{ $template->due_day }}" required>
                    </label>
                </div>
                <label style="margin:10px 0 0;">Bénéficiaire
                    <input name="beneficiary" value="{{ $template->beneficiary }}">
                </label>
                <label style="margin:10px 0 0;">Mode de paiement préféré
                    <select name="payment_method">
                        <option value="">Non défini</option>
                        @foreach(['transfer' => 'Virement', 'cash' => 'Espèces', 'check' => 'Chèque', 'other' => 'Autre'] as $value => $label)
                            <option value="{{ $value }}" @selected($template->payment_method === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="remember" style="margin:10px 0;">
                    <input type="checkbox" name="is_active" value="1" id="active-{{ $template->id }}" @checked($template->is_active)>
                    <label for="active-{{ $template->id }}" style="margin:0;">Génération active</label>
                </div>
                <button class="button outline w-full">Enregistrer la configuration</button>
            </form>
        </article>
    @endforeach
</section>

<section class="panel tablepanel">
    <div class="panelhead tabletop">
        <div>
            <h2>Obligations de {{ sprintf('%02d/%04d', $month, $year) }}</h2>
            <p class="muted">Paiements en attente et déjà enregistrés pour la période sélectionnée</p>
        </div>
    </div>

    <div class="scroll">
        <table class="table-grid">
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Dépense</th>
                    <th>Échéance</th>
                    <th class="text-right">Montant dû</th>
                    <th>Statut</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($occurrences as $occurrence)
                <tr>
                    <td><span class="ref">{{ $occurrence->reference }}</span></td>
                    <td>{{ $occurrence->template->name }}</td>
                    <td>{{ $occurrence->due_date->format('d/m/Y') }}</td>
                    <td class="text-right"><span class="amount">{{ number_format((float) $occurrence->amount_due, 2, ',', ' ') }} &euro;</span></td>
                    <td>
                        <span class="status-badge status-{{ $occurrence->status === 'paid' ? 'paid' : ($occurrence->status === 'overdue' ? 'overdue' : 'pending') }}">
                            @if($occurrence->status === 'paid') Payé
                            @elseif($occurrence->status === 'overdue') En retard
                            @else En attente
                            @endif
                        </span>
                    </td>
                    <td>
                        @if(in_array($occurrence->status, ['pending', 'overdue'], true))
                            <button class="button" onclick="document.getElementById('pay-{{ $occurrence->id }}').classList.add('open')">Enregistrer le paiement</button>

                            <div class="modal-overlay" id="pay-{{ $occurrence->id }}">
                                <div class="modal-dialog">
                                    <form method="POST" enctype="multipart/form-data" action="{{ route('recurring-expenses.pay', $occurrence) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h3>Paiement — {{ $occurrence->template->name }}</h3>
                                            <button type="button" class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('open')">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <p>Montant à payer : <strong>{{ number_format((float) $occurrence->amount_due, 2, ',', ' ') }} &euro;</strong></p>
                                            <label>Date réelle du paiement
                                                <input type="date" name="paid_at" value="{{ today()->toDateString() }}" required>
                                            </label>
                                            <label>Mode de paiement
                                                <select name="payment_method" required>
                                                    <option value="transfer">Virement</option>
                                                    <option value="cash">Espèces</option>
                                                    <option value="check">Chèque</option>
                                                    <option value="other">Autre</option>
                                                </select>
                                            </label>
                                            <label>Référence de paiement
                                                <input name="payment_reference">
                                            </label>
                                            <label>Justificatif (PDF, JPG, PNG)
                                                <input type="file" name="proof" accept=".pdf,.jpg,.jpeg,.png">
                                            </label>
                                            <label>Notes
                                                <textarea name="notes" rows="2"></textarea>
                                            </label>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="button outline" onclick="this.closest('.modal-overlay').classList.remove('open')">Annuler</button>
                                            <button class="button">Confirmer le paiement</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @elseif($occurrence->financial_entry_id)
                            <a class="link" href="{{ route('financial-entries.index') }}">RF liée #{{ $occurrence->financial_entry_id }} &rarr;</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="empty-state"><b>Aucune obligation générée pour cette période.</b><small>Les obligations sont créées automatiquement le 1er de chaque mois.</small></div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(function(m) {
            m.classList.remove('open');
        });
    }
});
document.querySelectorAll('.modal-overlay').forEach(function(m) {
    m.addEventListener('click', function(e) {
        if (e.target === m) m.classList.remove('open');
    });
});
</script>
@endsection
