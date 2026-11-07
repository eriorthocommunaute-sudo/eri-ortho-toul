<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\OffrandeRequest;
use App\Models\Member;
use App\Models\Offrande;
use App\Services\ReferenceNumberService;
use App\Services\IncomeReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OffrandeController extends Controller
{
    public function index(Request $request): View
    {
        $offrandes = Offrande::query()
            ->with('member')
            ->when($request->filled('member_id'), fn ($q) => $q->where('member_id', $request->integer('member_id')))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('offering_date', $request->integer('year')))
            ->orderByDesc('offering_date')
            ->paginate(20)
            ->withQueryString();

        $members = Member::where('status', 'active')->orderBy('family_name')->get();
        $years = range((int) now()->format('Y'), 2020);

        return view('finance.offrandes.index', compact('offrandes', 'members', 'years'));
    }

    public function create(): View
    {
        $members = Member::where('status', 'active')->orderBy('family_name')->get();
        return view('finance.offrandes.create', compact('members'));
    }

    public function store(OffrandeRequest $request, ReferenceNumberService $references, IncomeReceiptService $receipts): RedirectResponse
    {
        $data = $request->validated();
        $data['reference'] = $references->next('OFF');
        $data['financial_reference'] = $references->next('RF');
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        DB::transaction(function () use ($data, $receipts) {
            $offrande = Offrande::create($data);

            $occasion = $data['occasion'] ?? 'Libre';
            $donorInfo = $data['member_id'] ? "Membre {$data['member_id']}" : "Anonyme: {$data['donor_name']}";

            \App\Models\FinancialEntry::create([
                'reference' => $data['financial_reference'],
                'entry_date' => $data['offering_date'],
                'type' => 'income',
                'category' => 'offrande',
                'source_type' => Offrande::class,
                'source_id' => $offrande->id,
                'description' => "Offrande: {$occasion} - {$donorInfo}",
                'amount' => $data['amount'],
                'notes' => $data['notes'],
                'created_by' => $data['created_by'],
                'updated_by' => $data['updated_by'],
            ]);

            // Receipt reuses the existing offering and financial reference.
            $receipts->sync($offrande);
        });

        return redirect()->route('offrandes.index')->with('success', __('app.offrande_created'));
    }

    public function show(Offrande $offrande): View
    {
        $offrande->load('member');
        return view('finance.offrandes.show', compact('offrande'));
    }

    public function edit(Offrande $offrande): View
    {
        $members = Member::where('status', 'active')->orderBy('family_name')->get();
        return view('finance.offrandes.edit', compact('offrande', 'members'));
    }

    public function update(OffrandeRequest $request, Offrande $offrande, IncomeReceiptService $receipts): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        DB::transaction(function () use ($offrande, $data, $receipts) {
            $offrande->update($data);

        $occasion = $data['occasion'] ?? 'Libre';
        $donorInfo = $data['member_id'] ? "Membre {$data['member_id']}" : "Anonyme: {$data['donor_name']}";

        \App\Models\FinancialEntry::where('source_type', Offrande::class)
            ->where('source_id', $offrande->id)
            ->update([
                'amount' => $data['amount'],
                'description' => "Offrande: {$occasion} - {$donorInfo}",
                'notes' => $data['notes'],
            ]);
            $receipts->sync($offrande->fresh());
        });

        return redirect()->route('offrandes.show', $offrande)->with('success', __('app.offrande_updated'));
    }

    public function destroy(Request $request, Offrande $offrande): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('finance.record'), 403);

        \App\Models\FinancialEntry::where('source_type', Offrande::class)
            ->where('source_id', $offrande->id)
            ->delete();

        $offrande->delete();

        return redirect()->route('offrandes.index')->with('success', __('app.offrande_deleted'));
    }
}