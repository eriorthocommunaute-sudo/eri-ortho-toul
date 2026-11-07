@extends('layouts.app')
@section('content')
<div class="flex gap-2 mb-4">
    <h1>{{ $purchaseRequest->reference }} - {{ $purchaseRequest->title }}</h1>
    <div style="margin-left: auto;">
        @if(auth()->user()->hasPermission('purchases.manage'))
        @if($purchaseRequest->status === 'draft')
        <a class="button" href="{{ route('purchase-requests.edit', $purchaseRequest) }}">{{ __('app.edit') }}</a>
        <form method="post" action="{{ route('purchase-requests.destroy', $purchaseRequest) }}" style="display: inline;">@csrf @method('DELETE')<button type="submit" class="button" style="background: #c53030;" onclick="return confirm('{{ __('app.confirm_delete') }}')">{{ __('app.delete') }}</button></form>
        @endif
        @if($purchaseRequest->status === 'submitted')
        <form method="post" action="{{ route('purchase-requests.approve', $purchaseRequest) }}" style="display: inline;">@csrf<input name="approval_notes" type="text" placeholder="{{ __('app.approval_notes_placeholder') }}" style="margin-right: .5rem;"><button type="submit" class="button" style="background: #059669;">{{ __('app.approve') }}</button></form>
        <form method="post" action="{{ route('purchase-requests.reject', $purchaseRequest) }}" style="display: inline;">@csrf<input name="approval_notes" type="text" placeholder="{{ __('app.rejection_reason') }}" style="margin-right: .5rem;"><button type="submit" class="button" style="background: #c53030;">{{ __('app.reject') }}</button></form>
        @endif
        @if($purchaseRequest->status === 'approved')
        <form method="post" action="{{ route('purchase-requests.ordered', $purchaseRequest) }}" style="display: inline;">@csrf<button type="submit" class="button" style="background: #b45309;">{{ __('app.mark_ordered') }}</button></form>
        @endif
        @if($purchaseRequest->status === 'ordered')
        <form method="post" action="{{ route('purchase-requests.received', $purchaseRequest) }}" style="display: inline;">@csrf
            <input name="paid_amount" type="number" step="0.01" min="0" placeholder="Montant versé (€)" required style="width: 120px; margin-right: .5rem;">
            <input name="payment_date" type="date" required style="width: 150px; margin-right: .5rem;">
            <input name="supplier" type="text" placeholder="{{ __('app.supplier') }}" style="width: 150px; margin-right: .5rem;">
            <select name="payment_method" required style="margin-right: .5rem;">@foreach(['cash', 'transfer', 'check', 'other'] as $m)<option value="{{ $m }}">{{ __('app.'.$m) }}</option>@endforeach</select>
            <input name="payment_reference" type="text" placeholder="Référence paiement" style="width: 150px; margin-right: .5rem;">
            <input name="accounting_document_number" type="text" placeholder="N° pièce comptable" style="width: 150px; margin-right: .5rem;">
            <button type="submit" class="button" style="background: #059669;">{{ __('app.mark_received') }}</button>
        </form>
        @endif
        @endif
    </div>
</div>

<div class="grid" style="margin-bottom: 1.5rem;">
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.reference') }}</strong><br>
        {{ $purchaseRequest->reference }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.status') }}</strong><br>
        <span class="status-badge status-{{ $purchaseRequest->status }}">{{ __('app.'.$purchaseRequest->status) }}</span>
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.priority') }}</strong><br>
        <span class="status-badge">{{ __('app.'.$purchaseRequest->priority) }}</span>
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.estimated_amount') }}</strong><br>
        {{ number_format($purchaseRequest->estimated_amount, 2) }} €
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.category') }}</strong><br>
        {{ $purchaseRequest->category ? __('app.'.$purchaseRequest->category) : '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.supplier') }}</strong><br>
        {{ $purchaseRequest->supplier ?? '' }}
    </div>
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.requested_by') }}</strong><br>
        {{ $purchaseRequest->requester->name ?? '' }}
    </div>
    @if($purchaseRequest->approved_at)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.approved_by') }}</strong><br>
        {{ $purchaseRequest->approver->name ?? '' }} ({{ $purchaseRequest->approved_at->format('d/m/Y H:i') }})
    </div>
    @endif
    @if($purchaseRequest->equipment_reference)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.equipment_reference') }}</strong><br>
        {{ $purchaseRequest->equipment_reference }}
    </div>
    @endif
    @if($purchaseRequest->financial_reference)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.financial_reference') }}</strong><br>
        {{ $purchaseRequest->financial_reference }}
    </div>
    @endif
    @if($purchaseRequest->financial_manager)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.responsable_financier') }}</strong><br>
        {{ $purchaseRequest->financial_manager }}
    </div>
    @endif
    @if($purchaseRequest->signature)
    <div class="card" style="padding: 1rem; background: #f8fafc; border-radius: .5rem;">
        <strong>{{ __('app.signature') }}</strong><br>
        {{ $purchaseRequest->signature }}
    </div>
    @endif
</div>

<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.description') }}</strong><br>
    {{ $purchaseRequest->description }}
</div>


@if($purchaseRequest->requester_role || $purchaseRequest->requester_phone || $purchaseRequest->items)
<div class="card" style="padding:1rem;margin-top:1rem;">
    <h2>Demande d'achat et de dépenses / ሕቶ ግዝኣትን ወጻኢታትን</h2>
    <p><strong>Fonction :</strong> {{ $purchaseRequest->requester_role }} |
       <strong>Téléphone :</strong> {{ $purchaseRequest->requester_phone }}</p>
    @if($purchaseRequest->items)
    <table class="table-grid"><thead><tr><th>Désignation</th><th>Quantité</th><th>Prix unitaire</th><th>Total</th></tr></thead><tbody>
    @foreach($purchaseRequest->items as $item)
    <tr><td>{{ $item['designation'] }}</td><td>{{ $item['quantity'] }}</td><td>{{ number_format((float)$item['unit_price'],2,',',' ') }} €</td><td>{{ number_format((float)$item['quantity']*(float)$item['unit_price'],2,',',' ') }} €</td></tr>
    @endforeach
    </tbody></table>
    @endif
</div>
@endif
<div class="card" style="padding:1rem;margin-top:1rem;">
    <h2>Approbation / Décaissement / Justificatif</h2>
    <div class="grid">
        <p><strong>Décision :</strong> {{ $purchaseRequest->decision }}</p>
        <p><strong>Montant autorisé :</strong> {{ $purchaseRequest->authorized_amount }} €</p>
        <p><strong>Responsable autorisant :</strong> {{ $purchaseRequest->authorizing_manager }}</p>
        <p><strong>Montant versé :</strong> {{ $purchaseRequest->paid_amount }} €</p>
        <p><strong>Date paiement :</strong> {{ $purchaseRequest->payment_date?->format('d/m/Y') }}</p>
        <p><strong>Mode :</strong> {{ $purchaseRequest->payment_method }}</p>
        <p><strong>Référence paiement :</strong> {{ $purchaseRequest->payment_reference }}</p>
        <p><strong>N° pièce comptable :</strong> {{ $purchaseRequest->accounting_document_number }}</p>
        <p><strong>Justificatif :</strong> {{ $purchaseRequest->supporting_document_type }}</p>
        <p><strong>Montant dépensé :</strong> {{ $purchaseRequest->actual_spent_amount }} €</p>
        <p><strong>Montant restant :</strong> {{ $purchaseRequest->remaining_amount }} €</p>
        <p><strong>Date remise justificatif :</strong> {{ $purchaseRequest->proof_submitted_date?->format('d/m/Y') }}</p>
        <p><strong>Signature demandeur :</strong> {{ $purchaseRequest->requester_signature }}</p>
        <p><strong>Signature trésorier :</strong> {{ $purchaseRequest->treasurer_signature }}</p>
    </div>
</div>

@if($purchaseRequest->approval_notes)
<div style="margin-top: 1rem; padding: 1rem; background: #f8fafc; border-radius: .5rem;">
    <strong>{{ __('app.approval_notes') }}</strong><br>
    {{ $purchaseRequest->approval_notes }}
</div>
@endif
@endsection