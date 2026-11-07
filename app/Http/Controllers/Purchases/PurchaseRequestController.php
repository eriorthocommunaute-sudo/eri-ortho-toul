<?php

namespace App\Http\Controllers\Purchases;

use App\Http\Controllers\Controller;
use App\Http\Requests\Purchases\PurchaseRequestRequest;
use App\Models\Equipment;
use App\Models\FinancialEntry;
use App\Models\PurchaseRequest;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseRequestController extends Controller
{
    public function index(Request $request): View
    {
        $requests = PurchaseRequest::query()
            ->with('requester', 'approver')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $statuses = ['draft', 'submitted', 'approved', 'rejected', 'ordered', 'received', 'cancelled'];
        $categories = ['equipment', 'supplies', 'maintenance', 'transport', 'train_ticket', 'services', 'operating_expense', 'other'];

        return view('purchases.requests.index', compact('requests', 'statuses', 'categories'));
    }

    public function print(Request $request): View
    {
        $requests = PurchaseRequest::query()
            ->with('requester', 'approver')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->orderBy('reference')
            ->get();

        $paidAmounts = FinancialEntry::query()
            ->where('type', 'expense')
            ->where('source_type', PurchaseRequest::class)
            ->get(['source_id', 'amount'])
            ->groupBy('source_id')
            ->map(fn ($entries) => $entries->sum('amount'));

        return view('purchases.requests.print', compact('requests', 'paidAmounts'));
    }

    public function formPrint(Request $request): View
    {
        $count = max(1, min(200, (int) $request->input('count', 10)));
        $year = (int) now()->format('Y');
        $month = (int) now()->format('m');
        $prefix = sprintf('DA-%04d-%02d-', $year, $month);

        // Continue after the highest reference already registered for this month.
        // This produces DA-2026-11-05 when DA-2026-11-04 is the latest reference.
        $lastNumber = PurchaseRequest::query()
            ->where('reference', 'like', $prefix . '%')
            ->pluck('reference')
            ->map(function ($reference) use ($prefix) {
                $suffix = substr((string) $reference, strlen($prefix));
                return ctype_digit($suffix) ? (int) $suffix : 0;
            })
            ->max() ?? 0;

        $references = [];
        for ($number = $lastNumber + 1; count($references) < $count; $number++) {
            $references[] = $prefix . str_pad((string) $number, 2, '0', STR_PAD_LEFT);
        }

        return view('purchases.requests.form-print', ['references' => $references]);
    }

    public function create(): View
    {
        return view('purchases.requests.create');
    }

    public function store(PurchaseRequestRequest $request, ReferenceNumberService $references): RedirectResponse
    {
        $data = $request->validated();
        $data['reference'] = $references->next('DA');
        $data['requested_by'] = $request->user()->id;
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;
        $data['status'] = 'submitted';
        $data['decision'] = $data['decision'] ?? null;

        $purchaseRequest = PurchaseRequest::create($data);

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', __('app.purchase_request_created'));
    }

    public function show(PurchaseRequest $purchaseRequest): View
    {
        $purchaseRequest->load('requester', 'approver');
        return view('purchases.requests.show', compact('purchaseRequest'));
    }

    public function edit(PurchaseRequest $purchaseRequest): View
    {
        abort_unless(in_array($purchaseRequest->status, ['draft']), 403, __('app.request_not_editable'));
        return view('purchases.requests.edit', compact('purchaseRequest'));
    }

    public function update(PurchaseRequestRequest $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        abort_unless(in_array($purchaseRequest->status, ['draft']), 403, __('app.request_not_editable'));

        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $purchaseRequest->update($data);

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', __('app.purchase_request_updated'));
    }

    public function approve(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        abort_unless($purchaseRequest->status === 'submitted', 403, __('app.request_not_approvable'));

        $purchaseRequest->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approval_notes' => $request->input('approval_notes'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', __('app.purchase_request_approved'));
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        abort_unless($purchaseRequest->status === 'submitted', 403, __('app.request_not_rejectable'));

        $purchaseRequest->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approval_notes' => $request->input('approval_notes'),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', __('app.purchase_request_rejected'));
    }

    public function markOrdered(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        abort_unless($purchaseRequest->status === 'approved', 403);

        $purchaseRequest->update([
            'status' => 'ordered',
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', __('app.purchase_request_ordered'));
    }

    public function markReceived(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        abort_unless($purchaseRequest->status === 'ordered', 403);
        abort_unless($request->user()->hasPermission('purchases.manage'), 403);

        $data = $request->validate([
            'paid_amount' => ['required', 'numeric', 'gt:0', 'max:100000'],
            'payment_date' => ['required', 'date'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,transfer,check,other'],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'accounting_document_number' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($purchaseRequest, $data, $request) {
            // Lock the request to prevent two concurrent payment confirmations.
            $locked = PurchaseRequest::query()->lockForUpdate()->findOrFail($purchaseRequest->id);

            abort_unless($locked->status === 'ordered', 409, 'Cette demande a déjà été traitée.');
            abort_if(
                FinancialEntry::query()
                    ->where('source_type', PurchaseRequest::class)
                    ->where('source_id', $locked->id)
                    ->exists(),
                409,
                'Une écriture financière existe déjà pour cette demande.'
            );

            $financialReference = app(ReferenceNumberService::class)->next('RF');
            $equipmentReference = null;

            // Only an actual equipment purchase creates an inventory/equipment record.
            if ($locked->category === 'equipment') {
                $equipmentReference = app(ReferenceNumberService::class)->next('EQP');

                Equipment::create([
                    'reference' => $equipmentReference,
                    'name' => $locked->title,
                    'category' => $locked->category,
                    'description' => $locked->description,
                    'purchase_date' => $data['payment_date'],
                    'purchase_price' => $data['paid_amount'],
                    'purchase_reference' => $locked->reference,
                    'financial_reference' => $financialReference,
                    'supplier' => $data['supplier'] ?? $locked->supplier,
                    'condition' => 'new',
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ]);
            }

            // The financial register records an expense only after payment confirmation.
            FinancialEntry::create([
                'reference' => $financialReference,
                'entry_date' => $data['payment_date'],
                'type' => 'expense',
                'category' => match ($locked->category) {
                    'equipment' => 'achat',
                    'supplies' => 'fournitures',
                    'maintenance' => 'entretien',
                    'transport', 'train_ticket' => 'transport',
                    'services' => 'services',
                    'operating_expense' => 'fonctionnement',
                    default => 'autre',
                },
                'source_type' => PurchaseRequest::class,
                'source_id' => $locked->id,
                'description' => 'Dépense : ' . $locked->title,
                'amount' => $data['paid_amount'],
                'payment_method' => $data['payment_method'],
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $locked->update([
                'status' => 'received',
                'equipment_reference' => $equipmentReference,
                'financial_reference' => $financialReference,
                'paid_amount' => $data['paid_amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'accounting_document_number' => $data['accounting_document_number'] ?? $financialReference,
                'supplier' => $data['supplier'] ?? $locked->supplier,
                'updated_by' => $request->user()->id,
            ]);
        });

        return redirect()->route('purchase-requests.show', $purchaseRequest)->with('success', __('app.purchase_request_received'));
    }

    public function destroy(Request $request, PurchaseRequest $purchaseRequest): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('purchases.manage'), 403);
        abort_unless(in_array($purchaseRequest->status, ['draft', 'rejected', 'cancelled']), 403);

        $purchaseRequest->delete();

        return redirect()->route('purchase-requests.index')->with('success', __('app.purchase_request_deleted'));
    }
}
