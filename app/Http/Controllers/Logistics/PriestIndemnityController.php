<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logistics\PriestIndemnityRequest;
use App\Models\Priest;
use App\Models\PriestIndemnity;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PriestIndemnityController extends Controller
{
    public function index(Request $request): View
    {
        $query = PriestIndemnity::query()
            ->when($request->filled('year'), fn ($q) => $q->whereYear('service_date', $request->integer('year')))
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('service_date', $request->integer('month')))
            ->when($request->filled('occasion'), fn ($q) => $q->where('occasion', $request->string('occasion')))
            ->when($request->filled('priest_id'), fn ($q) => $q->where('priest_id', $request->integer('priest_id')));

        $totalAmount = (clone $query)->sum('amount');

        $indemnities = $query
            ->orderByDesc('service_date')
            ->paginate(20)
            ->withQueryString();

        $years = range((int) now()->format('Y'), 2020);
        $months = range(1, 12);
        $priests = Priest::where('is_active', true)->orderBy('name')->get();

        return view('logistics.priest_indemnities.index', compact('indemnities', 'years', 'months', 'priests', 'totalAmount'));
    }

    public function create(): View
    {
        $priests = Priest::where('is_active', true)->orderBy('name')->get();

        return view('logistics.priest_indemnities.create', compact('priests'));
    }

    public function store(PriestIndemnityRequest $request, ReferenceNumberService $references): RedirectResponse
    {
        $data = $request->validated();
        $data['reference'] = $references->next('PRE');
        $data['financial_reference'] = $references->next('RF');
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        DB::transaction(function () use ($data) {
            $pre = PriestIndemnity::create($data);

            $priestName = $pre->priest?->name ?? '';
            $location = $data['location'] ?? '';

            \App\Models\FinancialEntry::create([
                'reference' => $data['financial_reference'],
                'entry_date' => $data['service_date'],
                'type' => 'expense',
                'category' => 'indemnite',
                'source_type' => PriestIndemnity::class,
                'source_id' => $pre->id,
                'description' => trim("Indemnité prêtre" . ($priestName ? " {$priestName}" : '') . ": {$data['occasion']}" . ($location ? " à {$location}" : '')),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'other',
                'notes' => $data['notes'],
                'created_by' => $data['created_by'],
                'updated_by' => $data['updated_by'],
            ]);
        });

        return redirect()->route('priest-indemnities.index')->with('success', __('app.indemnity_created'));
    }

    public function show(PriestIndemnity $indemnity): View
    {
        return view('logistics.priest_indemnities.show', compact('indemnity'));
    }

    public function edit(PriestIndemnity $indemnity): View
    {
        $priests = Priest::where('is_active', true)->orderBy('name')->get();

        return view('logistics.priest_indemnities.edit', compact('indemnity', 'priests'));
    }

    public function update(PriestIndemnityRequest $request, PriestIndemnity $indemnity): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $indemnity->update($data);

        $priestName = $indemnity->fresh()->priest?->name ?? '';
        $location = $data['location'] ?? '';

        \App\Models\FinancialEntry::where('source_type', PriestIndemnity::class)
            ->where('source_id', $indemnity->id)
            ->update([
                'amount' => $data['amount'],
                'description' => trim("Indemnité prêtre" . ($priestName ? " {$priestName}" : '') . ": {$data['occasion']}" . ($location ? " à {$location}" : '')),
                'notes' => $data['notes'],
            ]);

        return redirect()->route('priest-indemnities.show', $indemnity)->with('success', __('app.indemnity_updated'));
    }

    public function destroy(Request $request, PriestIndemnity $indemnity): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('finance.record'), 403);

        \App\Models\FinancialEntry::where('source_type', PriestIndemnity::class)
            ->where('source_id', $indemnity->id)
            ->delete();

        $indemnity->delete();

        return redirect()->route('priest-indemnities.index')->with('success', __('app.indemnity_deleted'));
    }
}
