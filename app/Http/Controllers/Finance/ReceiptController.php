<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\ReceiptRequest;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\Receipt;
use App\Services\MemberContributionAllocator;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function index(Request $request): View
    {
        $receipts = Receipt::query()
            ->with(['member', 'contribution', 'confirmer'])
            ->when($request->filled('receipt_type'), fn ($q) => $q->where('receipt_type', $request->string('receipt_type')))
            ->when($request->filled('member_id'), fn ($q) => $q->where('member_id', $request->integer('member_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('payment_date', $request->integer('year')))
            ->orderByDesc('payment_date')
            ->paginate(20)
            ->withQueryString();

        $members = Member::where('status', 'active')->orderBy('family_name')->get();
        $years = range((int) now()->format('Y'), 2020);

        return view('finance.receipts.index', compact('receipts', 'members', 'years'));
    }

    public function create(Request $request): View
    {
        $members = Member::where('status', 'active')->orderBy('family_name')->get();
        
        // Get contributions that are open
        $contributions = Contribution::open()->orderByDesc('year')->orderByDesc('month')->get();
        
        // Get all confirmed receipts grouped by contribution_id -> member_ids
        $paidByContribution = Receipt::where('status', 'confirmed')
            ->whereNotNull('contribution_id')
            ->get()
            ->groupBy('contribution_id')
            ->map(fn ($receipts) => $receipts->pluck('member_id')->unique()->toArray())
            ->toArray();
        
        // If member is pre-selected, filter out contributions they already paid for
        if ($request->filled('member_id')) {
            $memberId = $request->integer('member_id');
            $paidContributionIds = Receipt::where('member_id', $memberId)
                ->where('status', 'confirmed')
                ->pluck('contribution_id')
                ->toArray();
            
            $contributions = $contributions->reject(fn ($c) => in_array($c->id, $paidContributionIds));
        }

        return view('finance.receipts.create', compact('members', 'contributions', 'paidByContribution'));
    }

    public function store(ReceiptRequest $request, ReferenceNumberService $references): RedirectResponse
    {
        $data = $request->validated();
        $contributionIds = $data['contribution_ids'];
        $memberId = $data['member_id'];
        $member = Member::findOrFail($memberId);
        $amountPerMonth = $data['amount'] / count($contributionIds);
        
        // Verify member hasn't already paid for any of these contributions
        $alreadyPaid = Receipt::where('member_id', $memberId)
            ->where('status', 'confirmed')
            ->whereIn('contribution_id', $contributionIds)
            ->exists();
        
        if ($alreadyPaid) {
            return back()->withErrors(['contribution_ids' => 'Ce membre a déjà payé pour l\'un de ces mois.'])->withInput();
        }

        $userId = $request->user()->id;
            
            $receipts = DB::transaction(function () use ($data, $references, $contributionIds, $member, $amountPerMonth, $userId) {
                $createdReceipts = [];
                
                foreach ($contributionIds as $index => $contributionId) {
                    $contribution = Contribution::findOrFail($contributionId);
                    
                    $receiptData = [
                        'reference' => $references->next('REC'),
                        'financial_reference' => $references->next('RF'),
                        'member_id' => $data['member_id'],
                        'contribution_id' => $contributionId,
                        'payment_date' => $data['payment_date'],
                        'amount' => $amountPerMonth,
                        'payment_method' => $data['payment_method'],
                        'notes' => $data['notes'] ?? null,
                        'status' => 'draft',
                        'created_by' => $userId,
                        'updated_by' => $userId,
                    ];
                
                $receipt = Receipt::create($receiptData);
                $createdReceipts[] = $receipt;
                
                // Create financial entry for each receipt
                \App\Models\FinancialEntry::create([
                    'reference' => $receiptData['financial_reference'],
                    'entry_date' => $data['payment_date'],
                    'type' => 'income',
                    'category' => 'cotisation',
                    'source_type' => Receipt::class,
                    'source_id' => $receipt->id,
                    'description' => "Cotisation {$member->member_code} - {$contribution->reference} - {$data['payment_method']}",
                    'amount' => $amountPerMonth,
                    'payment_method' => $data['payment_method'],
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
            
            return $createdReceipts;
        });

        return redirect()->route('receipts.show', $receipts[0])->with('success', __('app.receipt_created') . ' (' . count($receipts) . ' mois)');
    }

    public function show(Receipt $receipt): View
    {
        $receipt->load('member', 'contribution', 'confirmer');
        return view('finance.receipts.show', compact('receipt'));
    }

    public function edit(Receipt $receipt): View
    {
        abort_unless(in_array($receipt->status, ['draft']), 403, __('app.receipt_not_editable'));

        $members = Member::where('status', 'active')->orderBy('family_name')->get();
        $contributions = Contribution::open()->orderByDesc('year')->orderByDesc('month')->get();

        return view('finance.receipts.edit', compact('receipt', 'members', 'contributions'));
    }

    public function update(ReceiptRequest $request, Receipt $receipt): RedirectResponse
    {
        abort_unless(in_array($receipt->status, ['draft']), 403, __('app.receipt_not_editable'));

        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;

        DB::transaction(function () use ($receipt, $data) {
            $oldAmount = $receipt->amount;
            $receipt->update($data);

            // Update financial entry
            \App\Models\FinancialEntry::where('source_type', Receipt::class)
                ->where('source_id', $receipt->id)
                ->update([
                    'amount' => $data['amount'],
                    'description' => "Cotisation {$receipt->member->member_code} - {$data['payment_method']}",
                    'payment_method' => $data['payment_method'],
                    'notes' => $data['notes'],
                ]);
        });

        return redirect()->route('receipts.show', $receipt)->with('success', __('app.receipt_updated'));
    }

    public function confirm(Request $request, Receipt $receipt): RedirectResponse
    {
        abort_unless($receipt->status === 'draft', 403, __('app.receipt_not_confirmable'));

        DB::transaction(function () use ($request, $receipt) {
            $receipt->update([
                'status' => 'confirmed',
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
                'updated_by' => $request->user()->id,
            ]);

            // Update contribution totals
            if ($receipt->contribution_id) {
                $contribution = $receipt->contribution;
                $contribution->increment('received_count');
                $contribution->increment('total_received', $receipt->amount);
            }

            app(MemberContributionAllocator::class)->allocate($receipt);
        });

        return redirect()->route('receipts.show', $receipt)->with('success', __('app.receipt_confirmed'));
    }

    public function cancel(Request $request, Receipt $receipt): RedirectResponse
    {
        $wasConfirmed = $receipt->status === 'confirmed';

        DB::transaction(function () use ($request, $receipt, $wasConfirmed) {
            $receipt->update([
                'status' => 'cancelled',
                'updated_by' => $request->user()->id,
            ]);

            if ($wasConfirmed) {
                app(MemberContributionAllocator::class)->release($receipt);
            }

            // Reverse financial entry
            \App\Models\FinancialEntry::where('source_type', Receipt::class)
                ->where('source_id', $receipt->id)
                ->update(['notes' => 'ANNULÉ: ' . ($receipt->notes ?? '')]);
        });

        return redirect()->route('receipts.index')->with('success', __('app.receipt_cancelled'));
    }

    public function destroy(Request $request, Receipt $receipt): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('finance.record'), 403);
        abort_unless($receipt->status === 'draft', 403);

        \App\Models\FinancialEntry::where('source_type', Receipt::class)
            ->where('source_id', $receipt->id)
            ->delete();

        $receipt->delete();

        return redirect()->route('receipts.index')->with('success', __('app.receipt_deleted'));
    }

    public function print(Receipt $receipt): View
    {
        $receipt->load('member', 'contribution', 'confirmer');
        return view('finance.receipts.print', compact('receipt'));
    }

    public function printBlank(): View
    {
        return view('finance.receipts.print', [
            'receipt' => null,
            'blankReceipt' => true,
        ]);
    }
}
