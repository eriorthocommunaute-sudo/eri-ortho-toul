<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchases\ExceptionalDemandRequest;
use App\Models\ExceptionalDemand;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExceptionalDemandController extends Controller
{
    public function index(Request $request): View
    {
        $demands = ExceptionalDemand::query()
            ->with('requester', 'reviewer', 'approver')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('urgency'), fn ($q) => $q->where('urgency', $request->string('urgency')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $statuses = ['draft', 'submitted', 'under_review', 'approved', 'rejected', 'paid', 'cancelled'];
        $urgencies = ['normal', 'urgent', 'critical'];
        $categories = ['emergency', 'maintenance', 'legal', 'medical', 'other'];

        return view('purchases.exceptional_demands.index', compact('demands', 'statuses', 'urgencies', 'categories'));
    }

    public function create(): View
    {
        return view('purchases.exceptional_demands.create');
    }

    public function store(ExceptionalDemandRequest $request, ReferenceNumberService $references): RedirectResponse
    {
        $data = $request->validated();
        $data['reference'] = $references->next('EXC');
        $data['requested_by'] = $request->user()->id;
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;
        $data['status'] = 'submitted';

        $demand = ExceptionalDemand::create($data);

        return redirect()->route('exceptional-demands.show', $demand)->with('success', __('app.exceptional_demand_created'));
    }

    public function show(ExceptionalDemand $demand): View
    {
        $demand->load('requester', 'reviewer', 'approver');
        return view('purchases.exceptional_demands.show', compact('demand'));
    }

    public function edit(ExceptionalDemand $demand): View
    {
        abort_unless(in_array($demand->status, ['draft']), 403, __('app.demand_not_editable'));
        return view('purchases.exceptional_demands.edit', compact('demand'));
    }

    public function update(ExceptionalDemandRequest $request, ExceptionalDemand $demand): RedirectResponse
    {
        abort_unless(in_array($demand->status, ['draft']), 403, __('app.demand_not_editable'));

        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $demand->update($data);

        return redirect()->route('exceptional-demands.show', $demand)->with('success', __('app.exceptional_demand_updated'));
    }

    public function submitForReview(Request $request, ExceptionalDemand $demand): RedirectResponse
    {
        abort_unless($demand->status === 'draft', 403);

        $demand->update([
            'status' => 'under_review',
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('exceptional-demands.show', $demand)->with('success', __('app.demand_submitted_review'));
    }

    public function approve(Request $request, ExceptionalDemand $demand): RedirectResponse
    {
        abort_unless(in_array($demand->status, ['submitted', 'under_review']), 403, __('app.demand_not_approvable'));

        $demand->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approval_notes' => $request->input('approval_notes'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('exceptional-demands.show', $demand)->with('success', __('app.exceptional_demand_approved'));
    }

    public function reject(Request $request, ExceptionalDemand $demand): RedirectResponse
    {
        abort_unless(in_array($demand->status, ['submitted', 'under_review']), 403, __('app.demand_not_rejectable'));

        $demand->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approval_notes' => $request->input('approval_notes'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('exceptional-demands.show', $demand)->with('success', __('app.exceptional_demand_rejected'));
    }

    public function markPaid(Request $request, ExceptionalDemand $demand): RedirectResponse
    {
        abort_unless($demand->status === 'approved', 403, __('app.demand_not_payable'));

        $data = $request->validate([
            'payment_method' => ['required', 'in:cash,transfer,check,other'],
        ]);

        $financialReference = app(ReferenceNumberService::class)->next('RF');

        DB::transaction(function () use ($demand, $data, $financialReference) {
            \App\Models\FinancialEntry::create([
                'reference' => $financialReference,
                'entry_date' => now()->toDateString(),
                'type' => 'expense',
                'category' => 'autre',
                'source_type' => ExceptionalDemand::class,
                'source_id' => $demand->id,
                'description' => "Demande exceptionnelle: {$demand->title}",
                'amount' => $demand->amount,
                'payment_method' => $data['payment_method'],
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $demand->update([
                'status' => 'paid',
                'financial_reference' => $financialReference,
                'updated_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('exceptional-demands.show', $demand)->with('success', __('app.exceptional_demand_paid'));
    }

    public function destroy(Request $request, ExceptionalDemand $demand): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('purchases.manage'), 403);
        abort_unless(in_array($demand->status, ['draft', 'rejected', 'cancelled']), 403);

        $demand->delete();

        return redirect()->route('exceptional-demands.index')->with('success', __('app.exceptional_demand_deleted'));
    }
}