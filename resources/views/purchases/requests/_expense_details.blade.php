<fieldset class="form-section purchase-section" id="expenseDetails">
    <legend>Détails complémentaires selon la catégorie / ተወሳኺ ዝርዝር ብመሰረት ምድብ</legend>

    @php
        $expenseDetails = old('expense_details', ($purchaseRequest->expense_details ?? []));
    @endphp

    <div data-category-fields="equipment">
        <h4>Matériel et équipements</h4>
        <div class="grid">
            <label>Type de matériel
                <input name="expense_details[equipment_type]" value="{{ $expenseDetails['equipment_type'] ?? '' }}" maxlength="255">
            </label>
            <label>Lieu d'utilisation / installation
                <input name="expense_details[installation_location]" value="{{ $expenseDetails['installation_location'] ?? '' }}" maxlength="255">
            </label>
            <label>Utilisation prévue
                <textarea name="expense_details[intended_use]" rows="2">{{ $expenseDetails['intended_use'] ?? '' }}</textarea>
            </label>
        </div>
    </div>

    <div data-category-fields="supplies" hidden>
        <h4>Matériel d'entretien</h4>
        <div class="grid">
            <label>Type de produit ou matériel
                <input name="expense_details[supply_type]" value="{{ $expenseDetails['supply_type'] ?? '' }}" maxlength="255">
            </label>
            <label>Quantité / fréquence d'utilisation
                <input name="expense_details[supply_quantity]" value="{{ $expenseDetails['supply_quantity'] ?? '' }}" maxlength="100">
            </label>
            <label>Utilisation prévue
                <textarea name="expense_details[intended_use]" rows="2">{{ $expenseDetails['intended_use'] ?? '' }}</textarea>
            </label>
        </div>
    </div>

    <div data-category-fields="maintenance" hidden>
        <h4>Entretien et réparations</h4>
        <div class="grid">
            <label>Équipement ou lieu concerné
                <input name="expense_details[asset_or_location]" value="{{ $expenseDetails['asset_or_location'] ?? '' }}" maxlength="255">
            </label>
            <label>Nature du problème / intervention
                <textarea name="expense_details[repair_description]" rows="3">{{ $expenseDetails['repair_description'] ?? '' }}</textarea>
            </label>
            <label>Date souhaitée de l'intervention
                <input type="date" name="expense_details[service_date]" value="{{ $expenseDetails['service_date'] ?? '' }}">
            </label>
        </div>
    </div>

    <div data-category-fields="transport" hidden>
        <h4>Transport et déplacements</h4>
        <div class="grid">
            <label>Départ
                <input name="expense_details[departure]" value="{{ $expenseDetails['departure'] ?? '' }}" maxlength="255">
            </label>
            <label>Destination
                <input name="expense_details[destination]" value="{{ $expenseDetails['destination'] ?? '' }}" maxlength="255">
            </label>
            <label>Type de transport
                <input name="expense_details[transport_type]" value="{{ $expenseDetails['transport_type'] ?? '' }}" maxlength="100">
            </label>
            <label>Date du déplacement
                <input type="date" name="expense_details[service_date]" value="{{ $expenseDetails['service_date'] ?? '' }}">
            </label>
            <label>Motif du déplacement
                <textarea name="expense_details[purpose]" rows="2">{{ $expenseDetails['purpose'] ?? '' }}</textarea>
            </label>
        </div>
    </div>

    <div data-category-fields="train_ticket" hidden>
        <h4>Billets de train</h4>
        <div class="grid">
            <label>Nom du passager
                <input name="expense_details[passenger]" value="{{ $expenseDetails['passenger'] ?? '' }}" maxlength="255">
            </label>
            <label>Départ
                <input name="expense_details[departure]" value="{{ $expenseDetails['departure'] ?? '' }}" maxlength="255">
            </label>
            <label>Destination
                <input name="expense_details[destination]" value="{{ $expenseDetails['destination'] ?? '' }}" maxlength="255">
            </label>
            <label>Date de départ
                <input type="date" name="expense_details[departure_date]" value="{{ $expenseDetails['departure_date'] ?? '' }}">
            </label>
            <label>Date de retour
                <input type="date" name="expense_details[return_date]" value="{{ $expenseDetails['return_date'] ?? '' }}">
            </label>
            <label>Motif du déplacement
                <textarea name="expense_details[purpose]" rows="2">{{ $expenseDetails['purpose'] ?? '' }}</textarea>
            </label>
        </div>
    </div>

    <div data-category-fields="services" hidden>
        <h4>Services et prestations</h4>
        <div class="grid">
            <label>Prestataire
                <input name="expense_details[provider]" value="{{ $expenseDetails['provider'] ?? '' }}" maxlength="255">
            </label>
            <label>Date prévue de la prestation
                <input type="date" name="expense_details[service_date]" value="{{ $expenseDetails['service_date'] ?? '' }}">
            </label>
            <label>Description de la prestation
                <textarea name="expense_details[service_description]" rows="3">{{ $expenseDetails['service_description'] ?? '' }}</textarea>
            </label>
        </div>
    </div>

    <div data-category-fields="other" hidden>
        <h4>Autres dépenses</h4>
        <div class="grid">
            <label>Bénéficiaire / prestataire
                <input name="expense_details[beneficiary]" value="{{ $expenseDetails['beneficiary'] ?? '' }}" maxlength="255">
            </label>
            <label>Justification / précisions
                <textarea name="expense_details[justification]" rows="3">{{ $expenseDetails['justification'] ?? '' }}</textarea>
            </label>
        </div>
    </div>
</fieldset>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const categorySelect = document.getElementById('requestCategory')
        || document.querySelector('#purchaseRequestForm [name="category"]');
    const groups = document.querySelectorAll('#expenseDetails [data-category-fields]');

    if (!categorySelect || !groups.length) return;

    const refreshExpenseFields = () => {
        groups.forEach((group) => {
            const categories = (group.dataset.categoryFields || '').trim().split(/\\s+/);
            const visible = categories.includes(categorySelect.value);

            group.hidden = !visible;
            group.querySelectorAll('input, textarea, select').forEach((field) => {
                field.disabled = !visible;
            });
        });
    };

    categorySelect.addEventListener('change', refreshExpenseFields);
    refreshExpenseFields();
});
</script>
@endpush
