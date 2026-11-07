<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Logistics\PriestTransportRequest;
use App\Models\Priest;
use App\Models\PriestTransport;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PriestTransportController extends Controller
{
    public function index(Request $request): View
    {
        $query = PriestTransport::query()
            ->when($request->filled('year'), fn ($q) => $q->whereYear('transport_date', $request->integer('year')))
            ->when($request->filled('month'), fn ($q) => $q->whereMonth('transport_date', $request->integer('month')))
            ->when($request->filled('transport_type'), fn ($q) => $q->where('transport_type', $request->string('transport_type')))
            ->when($request->filled('priest_id'), fn ($q) => $q->where('priest_id', $request->integer('priest_id')));

        $totalAmount = (clone $query)->sum('amount');

        $transports = $query
            ->orderByDesc('transport_date')
            ->paginate(20)
            ->withQueryString();

        $years = range((int) now()->format('Y'), 2020);
        $months = range(1, 12);
        $types = ['personal_vehicle', 'taxi', 'train', 'bus', 'flight', 'other'];
        $priests = Priest::where('is_active', true)->orderBy('name')->get();

        return view('logistics.priest_transports.index', compact('transports', 'years', 'months', 'types', 'priests', 'totalAmount'));
    }

    public function create(): View
    {
        $priests = Priest::where('is_active', true)->orderBy('name')->get();

        return view('logistics.priest_transports.create', compact('priests'));
    }

    public function store(PriestTransportRequest $request, ReferenceNumberService $references): RedirectResponse
    {
        $data = $request->validated();
        $data['reference'] = $references->next('TRP');
        $data['financial_reference'] = $references->next('RF');
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        DB::transaction(function () use ($data) {
            $trp = PriestTransport::create($data);

            $priestName = $trp->priest?->name ?? '';
            $occasion = $data['occasion'] ?? '';

            \App\Models\FinancialEntry::create([
                'reference' => $data['financial_reference'],
                'entry_date' => $data['transport_date'],
                'type' => 'expense',
                'category' => 'transport',
                'source_type' => PriestTransport::class,
                'source_id' => $trp->id,
                'description' => trim("Transport prêtre" . ($priestName ? " {$priestName}" : '') . ": {$data['origin']} → {$data['destination']}" . ($occasion ? " ({$occasion})" : '')),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'] ?? 'other',
                'notes' => $data['notes'],
                'created_by' => $data['created_by'],
                'updated_by' => $data['updated_by'],
            ]);
        });

        return redirect()->route('priest-transports.index')->with('success', __('app.transport_created'));
    }

    public function show(PriestTransport $transport): View
    {
        return view('logistics.priest_transports.show', compact('transport'));
    }

    public function edit(PriestTransport $transport): View
    {
        $priests = Priest::where('is_active', true)->orderBy('name')->get();

        return view('logistics.priest_transports.edit', compact('transport', 'priests'));
    }

    public function update(PriestTransportRequest $request, PriestTransport $transport): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $transport->update($data);

        $priestName = $transport->fresh()->priest?->name ?? '';
        $occasion = $data['occasion'] ?? '';

        \App\Models\FinancialEntry::where('source_type', PriestTransport::class)
            ->where('source_id', $transport->id)
            ->update([
                'amount' => $data['amount'],
                'description' => trim("Transport prêtre" . ($priestName ? " {$priestName}" : '') . ": {$data['origin']} → {$data['destination']}" . ($occasion ? " ({$occasion})" : '')),
                'notes' => $data['notes'],
            ]);

        return redirect()->route('priest-transports.show', $transport)->with('success', __('app.transport_updated'));
    }

    public function destroy(Request $request, PriestTransport $transport): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('finance.record'), 403);

        \App\Models\FinancialEntry::where('source_type', PriestTransport::class)
            ->where('source_id', $transport->id)
            ->delete();

        $transport->delete();

        return redirect()->route('priest-transports.index')->with('success', __('app.transport_deleted'));
    }
}
