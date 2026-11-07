@extends('layouts.app')
@section('content')
<h1>{{ __('app.edit_purchase_request') }} - {{ $purchaseRequest->reference }}</h1>
<form method="post" action="{{ route('purchase-requests.update', $purchaseRequest) }}">
    @csrf @method('PUT')
    <fieldset class="purchase-section"><legend>DEMANDE D'ACHAT ET DE DÉPENSE / ሕቶ ግዝኣትን ወጻኢታትን</legend>
        <div class="grid">
            <label>Matériel / équipement<input name="title" value="{{ old('title', $purchaseRequest->title) }}" required></label>
            <label>Catégorie
                <select name="category" id="requestCategory" required>
                    @foreach([
                        'equipment' => 'Matériel et équipements',
                        'supplies' => "Matériel d'entretien",
                        'maintenance' => 'Entretien et réparations',
                        'transport' => 'Transport et déplacements',
                        'train_ticket' => 'Billets de train',
                        'services' => 'Services et prestations',
                        'other' => 'Autres dépenses',
                        'operating_expense' => 'Dépense de fonctionnement (ancienne catégorie)',
                    ] as $value => $label)
                        <option value="{{ $value }}" @selected(old('category', $purchaseRequest->category) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Fonction / responsabilité<input name="requester_role" value="{{ old('requester_role',$purchaseRequest->requester_role) }}"></label>
            <label>Téléphone<input name="requester_phone" value="{{ old('requester_phone',$purchaseRequest->requester_phone) }}"></label>
            <label>Fournisseur prévu<input name="supplier" value="{{ old('supplier',$purchaseRequest->supplier) }}"></label>
            <label>Priorité<select name="priority">@foreach(['low','normal','high','urgent'] as $p)<option value="{{ $p }}" @selected(old('priority',$purchaseRequest->priority)===$p)>{{ __('app.'.$p) }}</option>@endforeach</select></label>
        </div>
        <label>Motif / utilisation<textarea name="description" rows="3" required>{{ old('description',$purchaseRequest->description) }}</textarea></label>
        @include('purchases.requests._expense_details')

        <h3>Détail des achats / ዝርዝር ግዝኣት</h3>
        <table class="table-grid"><thead><tr><th>Désignation</th><th>Quantité</th><th>Prix unitaire (€)</th></tr></thead><tbody>
        @foreach(old('items',$purchaseRequest->items ?? []) as $i => $item)
            <tr><td><input name="items[{{ $i }}][designation]" value="{{ $item['designation'] ?? '' }}"></td><td><input type="number" min="1" name="items[{{ $i }}][quantity]" value="{{ $item['quantity'] ?? 1 }}"></td><td><input type="number" min="0" step=".01" name="items[{{ $i }}][unit_price]" value="{{ $item['unit_price'] ?? '' }}"></td></tr>
        @endforeach
        </tbody></table>
        <label>Montant demandé (€)<input name="estimated_amount" type="number" min=".01" step=".01" value="{{ old('estimated_amount',$purchaseRequest->estimated_amount) }}" required></label>
    </fieldset>
    <fieldset class="purchase-section"><legend>APPROBATION / ውሳነ</legend><div class="grid">
        <label>Décision<select name="decision"><option value="">En attente</option>@foreach(['approved'=>'Approuvé','rejected'=>'Refusé','modified'=>'Avec modification'] as $v=>$l)<option value="{{ $v }}" @selected(old('decision',$purchaseRequest->decision)===$v)>{{ $l }}</option>@endforeach</select></label>
        <label>Montant autorisé (€)<input name="authorized_amount" type="number" min="0" step=".01" value="{{ old('authorized_amount',$purchaseRequest->authorized_amount) }}"></label>
        <label>Responsable autorisant<input name="authorizing_manager" value="{{ old('authorizing_manager',$purchaseRequest->authorizing_manager) }}"></label>
        <label>Signature responsable<input name="authorizing_signature" value="{{ old('authorizing_signature',$purchaseRequest->authorizing_signature) }}"></label>
        <label>Responsable financier<input name="financial_manager" value="{{ old('financial_manager',$purchaseRequest->financial_manager) }}"></label>
        <label>Signature<input name="signature" value="{{ old('signature',$purchaseRequest->signature) }}"></label>
    </div></fieldset>
    <fieldset class="purchase-section"><legend>DÉCAISSEMENT / ክፍሊት</legend><div class="grid">
        <label>Montant versé (€)<input name="paid_amount" type="number" min="0" step=".01" value="{{ old('paid_amount',$purchaseRequest->paid_amount) }}"></label>
        <label>Date du paiement<input name="payment_date" type="date" value="{{ old('payment_date',$purchaseRequest->payment_date?->format('Y-m-d')) }}"></label>
        <label>Mode<select name="payment_method"><option value="">Choisir</option>@foreach(['cash','transfer','check','other'] as $m)<option value="{{ $m }}" @selected(old('payment_method',$purchaseRequest->payment_method)===$m)>{{ __('app.'.$m) }}</option>@endforeach</select></label>
        <label>Référence<input name="payment_reference" value="{{ old('payment_reference',$purchaseRequest->payment_reference) }}"></label>
        <label>N° pièce comptable<input name="accounting_document_number" value="{{ old('accounting_document_number',$purchaseRequest->accounting_document_number) }}"></label>
    </div></fieldset>
    <fieldset class="purchase-section"><legend>JUSTIFICATIF / መረጋገጺ</legend><div class="grid">
        <label>Document<select name="supporting_document_type"><option value="">Choisir</option>@foreach(['invoice'=>'Facture','ticket'=>'Ticket','receipt'=>'Reçu','other'=>'Autre'] as $v=>$l)<option value="{{ $v }}" @selected(old('supporting_document_type',$purchaseRequest->supporting_document_type)===$v)>{{ $l }}</option>@endforeach</select></label>
        <label>Montant réellement dépensé (€)<input name="actual_spent_amount" type="number" min="0" step=".01" value="{{ old('actual_spent_amount',$purchaseRequest->actual_spent_amount) }}"></label>
        <label>Montant restant (€)<input name="remaining_amount" type="number" min="0" step=".01" value="{{ old('remaining_amount',$purchaseRequest->remaining_amount) }}"></label>
        <label>Date remise justificatif<input name="proof_submitted_date" type="date" value="{{ old('proof_submitted_date',$purchaseRequest->proof_submitted_date?->format('Y-m-d')) }}"></label>
        <label>Signature demandeur<input name="requester_signature" value="{{ old('requester_signature',$purchaseRequest->requester_signature) }}"></label>
        <label>Signature trésorier<input name="treasurer_signature" value="{{ old('treasurer_signature',$purchaseRequest->treasurer_signature) }}"></label>
    </div></fieldset>
    <label>Priorité<select name="priority">@foreach(['low','normal','high','urgent'] as $p)<option value="{{ $p }}" @selected(old('priority',$purchaseRequest->priority)===$p)>{{ __('app.'.$p) }}</option>@endforeach</select></label>
    @if($errors->any())<ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('purchase-requests.show',$purchaseRequest) }}">Annuler</a>
</form>
<style>.purchase-section{border:1px solid #d8d0c0;border-radius:.5rem;padding:1rem;margin:1rem 0}.purchase-section legend{font-weight:700;color:#243447}.purchase-section label{display:flex;flex-direction:column;gap:.3rem;margin:.5rem 0}</style>
@endsection
