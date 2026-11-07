<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logistics\EquipmentRequest;
use App\Models\Equipment;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $equipment = Equipment::query()
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->filled('condition'), fn ($q) => $q->where('condition', $request->string('condition')))
            ->when($request->filled('location'), fn ($q) => $q->where('location', $request->string('location')))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = ['audio', 'video', 'furniture', 'liturgical', 'IT', 'other'];
        $conditions = ['new', 'good', 'fair', 'poor', 'disposed'];

        return view('logistics.equipment.index', compact('equipment', 'categories', 'conditions'));
    }

    public function create(): View
    {
        return view('logistics.equipment.create');
    }

    public function store(EquipmentRequest $request, ReferenceNumberService $references): RedirectResponse
    {
        $data = $request->validated();
        $data['reference'] = $references->next('EQP');
        $data['financial_reference'] = $references->next('RF');
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        DB::transaction(function () use ($data) {
            $eqp = Equipment::create($data);

            if ($data['purchase_price']) {
                \App\Models\FinancialEntry::create([
                    'reference' => $data['financial_reference'],
                    'entry_date' => $data['purchase_date'] ?? now()->toDateString(),
                    'type' => 'expense',
                    'category' => 'achat',
                    'source_type' => Equipment::class,
                    'source_id' => $eqp->id,
                    'description' => "Achat équipement: {$data['name']}",
                    'amount' => $data['purchase_price'],
                    'payment_method' => $data['payment_method'] ?? 'other',
                    'notes' => $data['notes'],
                    'created_by' => $data['created_by'],
                    'updated_by' => $data['updated_by'],
                ]);
            }
        });

        return redirect()->route('equipment.index')->with('success', __('app.equipment_created'));
    }

    public function show(Equipment $equipment): View
    {
        return view('logistics.equipment.show', compact('equipment'));
    }

    public function edit(Equipment $equipment): View
    {
        return view('logistics.equipment.edit', compact('equipment'));
    }

    public function update(EquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $equipment->update($data);

        return redirect()->route('equipment.show', $equipment)->with('success', __('app.equipment_updated'));
    }

    public function destroy(Request $request, Equipment $equipment): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('logistics.manage'), 403);
        $equipment->delete();

        return redirect()->route('equipment.index')->with('success', __('app.equipment_deleted'));
    }
}