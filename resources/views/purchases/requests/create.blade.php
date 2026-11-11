@extends('layouts.app')
@section('content')
<h1>{{ __('app.new_purchase_request') }}</h1>

<form method="post" action="{{ route('purchase-requests.store') }}" id="purchaseRequestForm">
    @csrf
    <input type="hidden" name="priority" value="{{ old('priority', 'normal') }}">

    <fieldset class="purchase-section">
        <legend>DEMANDE D’ACHAT ET DE DÉPENSE / ሕቶ ግዝኣትን ወጻኢታትን</legend>
        <div class="grid">
            <label>Référence DA
                <input type="text" value="DA-{{ now()->format('Y-m') }}- (générée à l’enregistrement)" readonly>
            </label>
            <label>Date / ዕለት
                <input type="text" name="request_date" value="{{ old('request_date', now()->format('d/m/Y')) }}">
            </label>
            <label>Demandeur / ሓታቲ
                <input type="text" name="requester_name" value="{{ old('requester_name', auth()->user()?->name ?? '') }}">
            </label>
            <label>Fonction / responsabilité / ሓላፍነት
                <input name="requester_role" value="{{ old('requester_role') }}" maxlength="255">
            </label>
            <label>Téléphone / ስልኪ
                <input name="requester_phone" type="tel" value="{{ old('requester_phone') }}" maxlength="40">
            </label>
            <label>Catégorie / ምድብ
                <select name="category" id="requestCategory" required>
                    <option value="">Choisir / ምረጽ</option>
                    <option value="equipment" @selected(old('category') === 'equipment')>Matériel et équipements / ኣቕሑን መሳርሒታትን</option>
                    <option value="supplies" @selected(old('category') === 'supplies')>Matériel d'entretien / ናውቲ ጽሬት</option>
                    <option value="maintenance" @selected(old('category') === 'maintenance')>Entretien et réparations / ጽገናን ምሕዳስን</option>
                    <option value="transport" @selected(old('category') === 'transport')>Transport et déplacements / መጓዓዝያን ጉዕዞን</option>
                    <option value="train_ticket" @selected(old('category') === 'train_ticket')>Billets de train / ቲኬት ባቡር</option>
                    <option value="services" @selected(old('category') === 'services')>Services et prestations / ኣገልግሎታትን ሞያዊ ስራሓትን</option>
                    <option value="other" @selected(old('category') === 'other')>Autres dépenses / ካልኦት ወጻኢታት</option>
                </select>
            </label>
        </div>

        <label>Objet de la demande / ዕላማ ናይቲ ሕቶ
            <input name="title" value="{{ old('title') }}" required maxlength="255">
        </label>
        <label>Motif / utilisation / ምኽንያትን ኣጠቓቕማን
            <textarea name="description" rows="3" required>{{ old('description') }}</textarea>
        </label>


        @include('purchases.requests._expense_details')

        <h3>Détail des achats / ዝርዝር ግዝኣት</h3>
        <div class="table-scroll">
            <table class="table-grid" id="purchaseItems">
                <thead><tr>
                    <th>Désignation / መግለጺ</th>
                    <th>Quantité / ብዝሒ</th>
                    <th>Prix unitaire (€) / ዋጋ ንሓደ</th>
                    <th>Total (€) / ጠቕላላ</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @foreach(old('items', [['designation' => '', 'quantity' => 1, 'unit_price' => '']]) as $i => $item)
                    <tr>
                        <td><input name="items[{{ $i }}][designation]" value="{{ $item['designation'] ?? '' }}" required></td>
                        <td><input class="item-quantity" name="items[{{ $i }}][quantity]" type="number" min="1" step="1" value="{{ $item['quantity'] ?? 1 }}" required></td>
                        <td><input class="item-price" name="items[{{ $i }}][unit_price]" type="number" min="0" step="0.01" value="{{ $item['unit_price'] ?? '' }}" required></td>
                        <td class="item-total">0,00</td>
                        <td><button type="button" class="remove-item" aria-label="Supprimer">×</button></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" class="button outline" id="addPurchaseItem">Ajouter une ligne</button>

        <div class="grid purchase-amounts">
            <label>Montant demandé / ዝተሓተተ መጠን (€)
                <input name="estimated_amount" id="estimatedAmount" type="number" min="0.01" step="0.01" value="{{ old('estimated_amount') }}" required>
            </label>
        </div>
    </fieldset>

    <fieldset class="purchase-section">
        <legend>APPROBATION / ውሳነ</legend>
        <div class="grid">
            <label>Décision / ውሳነ
                <select name="decision">
                    <option value="">En attente / ኣብ ትጽቢት</option>
                    <option value="approved" @selected(old('decision') === 'approved')>Approuvé / ጸዲቑ</option>
                    <option value="rejected" @selected(old('decision') === 'rejected')>Refusé / ተነጺጉ</option>
                    <option value="modified" @selected(old('decision') === 'modified')>Avec modification / ብለውጢ</option>
                </select>
            </label>
            <label>Montant autorisé (€) / ዝተፈቕደ መጠን
                <input name="authorized_amount" type="number" min="0" step="0.01" value="{{ old('authorized_amount') }}">
            </label>
            <label>Responsable autorisant / ዘፍቅድ ሓላፊ
                <input name="authorizing_manager" value="{{ old('authorizing_manager') }}">
            </label>
            <label>Signature / ፊርማ
                <input name="authorizing_signature" value="{{ old('authorizing_signature') }}">
            </label>
        </div>
    </fieldset>

    <fieldset class="purchase-section">
        <legend>DÉCAISSEMENT / ክፍሊት</legend>
        <div class="grid">
            <label>Montant versé (€) / ዝተኸፍለ መጠን
                <input name="paid_amount" type="number" min="0" step="0.01" value="{{ old('paid_amount') }}">
            </label>
            <label>Date du paiement / ዕለት ክፍሊት
                <input name="payment_date" type="date" value="{{ old('payment_date') }}">
            </label>
            <label>Mode / ኣገባብ ክፍሊት
                <select name="payment_method">
                    <option value="">Choisir / ምረጽ</option>
                    @foreach(['cash', 'transfer', 'check', 'other'] as $m)
                        <option value="{{ $m }}" @selected(old('payment_method') === $m)>{{ __('app.'.$m) }}</option>
                    @endforeach
                </select>
            </label>
            <label>Signature du demandeur / ፊርማ ሓታቲ
                <input name="payment_requester_signature" value="{{ old('payment_requester_signature') }}">
            </label>
            <label>Signature du trésorier / ፊርማ ተሓዚ ገንዘብ
                <input name="payment_treasurer_signature" value="{{ old('payment_treasurer_signature') }}">
            </label>
        </div>
    </fieldset>

    <fieldset class="purchase-section">
        <legend>JUSTIFICATIF / መረጋገጺ</legend>
        <div class="grid">
            <label>Référence RF / መወከሲ ሰነድ ሒሳብ
                <input name="accounting_document_number" value="{{ old('accounting_document_number') }}">
            </label>
            <label>Document / ሰነድ
                <select name="supporting_document_type">
                    <option value="">Choisir / ምረጽ</option>
                    @foreach(['invoice' => 'Facture', 'ticket' => 'Ticket', 'receipt' => 'Reçu', 'other' => 'Autre'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('supporting_document_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Montant réellement dépensé (€)
                <input name="actual_spent_amount" type="number" min="0" step="0.01" value="{{ old('actual_spent_amount') }}">
            </label>
            <label>Montant restant (€) / ዝተረፈ መጠን
                <input name="remaining_amount" type="number" min="0" step="0.01" value="{{ old('remaining_amount') }}">
            </label>
            <label>Date remise justificatif / ዕለት መቕረቢ
                <input name="proof_submitted_date" type="date" value="{{ old('proof_submitted_date') }}">
            </label>
            <label>Signature demandeur / ፊርማ ሓታቲ
                <input name="requester_signature" value="{{ old('requester_signature') }}">
            </label>
            <label>Signature trésorier / ፊርማ ሓላፊ ገንዘብ
                <input name="treasurer_signature" value="{{ old('treasurer_signature') }}">
            </label>
        </div>
    </fieldset>

    @if($errors->any())
        <ul class="error">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    @endif
    <button type="submit">{{ __('app.save') }}</button>
    <a class="button" href="{{ route('purchase-requests.index') }}" style="background:#6b7280;">{{ __('app.cancel') }}</a>
</form>

<style>
.purchase-section { border:1px solid #d8d0c0; border-radius:.6rem; padding:1rem; margin:1.2rem 0; background:#fff; }
.purchase-section legend { padding:0 .5rem; font-weight:700; color:#243447; }
.purchase-section label { display:flex; flex-direction:column; gap:.35rem; margin:.5rem 0; }
.table-scroll { overflow-x:auto; }
#purchaseItems input { width:100%; min-width:80px; }
#purchaseItems th { background:#243447; color:#fff; }
.item-total { white-space:nowrap; font-weight:700; }
.remove-item { border:0; background:#fee2e2; color:#991b1b; border-radius:4px; padding:.3rem .6rem; }
</style>
<script>
(() => {
    const tbody = document.querySelector('#purchaseItems tbody');
    const amountInput = document.getElementById('estimatedAmount');

    function recalculate() {
        let total = 0;
        tbody.querySelectorAll('tr').forEach((row) => {
            const quantity = Number(row.querySelector('.item-quantity').value || 0);
            const price = Number(row.querySelector('.item-price').value || 0);
            const lineTotal = quantity * price;
            row.querySelector('.item-total').textContent = lineTotal.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            total += lineTotal;
        });
        if (tbody.querySelectorAll('tr').length) amountInput.value = total.toFixed(2);
    }

    function reindex() {
        tbody.querySelectorAll('tr').forEach((row, index) => {
            row.querySelectorAll('input').forEach((input) => {
                input.name = input.name.replace(/items\\[\\d+\\]/, `items[${index}]`);
            });
        });
    }

    document.getElementById('addPurchaseItem').addEventListener('click', () => {
        const index = tbody.querySelectorAll('tr').length;
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input name="items[${index}][designation]" required></td>
            <td><input class="item-quantity" name="items[${index}][quantity]" type="number" min="1" step="1" value="1" required></td>
            <td><input class="item-price" name="items[${index}][unit_price]" type="number" min="0" step="0.01" required></td>
            <td class="item-total">0,00</td>
            <td><button type="button" class="remove-item" aria-label="Supprimer">×</button></td>`;
        tbody.appendChild(row);
    });

    tbody.addEventListener('input', recalculate);
    tbody.addEventListener('click', (event) => {
        if (event.target.classList.contains('remove-item') && tbody.querySelectorAll('tr').length > 1) {
            event.target.closest('tr').remove();
            reindex();
            recalculate();
        }
    });
    recalculate();
})();
</script>
@endsection
