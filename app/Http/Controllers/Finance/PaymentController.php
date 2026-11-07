<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\PaymentRequest;
use App\Models\Contribution;
use App\Models\FinancialEntry;
use App\Models\Member;
use App\Models\MemberContribution;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Receipt;
use App\Services\ReferenceNumberService;
use App\Services\IncomeReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $year = $request->filled('year') ? $request->integer('year') : (int) now()->format('Y');
        $years = range((int) now()->format('Y') + 1, (int) now()->format('Y') - 5);

        $payments = Payment::with('member')
            ->whereYear('payment_date', $year)
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Months covered per payment (for display + receipt links).
        $allocations = PaymentAllocation::whereIn('payment_id', $payments->pluck('id'))
            ->with('memberContribution')
            ->get()
            ->groupBy('payment_id');

        $receiptsByReference = Receipt::whereIn('reference', $payments->pluck('receipt_reference')->filter())
            ->get(['id', 'reference'])
            ->keyBy('reference');

        $financialRefs = FinancialEntry::where('source_type', Payment::class)
            ->whereIn('source_id', $payments->pluck('id'))
            ->get(['source_id', 'reference'])
            ->keyBy('source_id');

        // Rule 5: monthly cash/bank totals are built from the actual payment
        // date of confirmed payments only (received money, counted once).
        $monthly = DB::table('payments')
            ->where('status', 'confirmed')
            ->whereYear('payment_date', $year)
            ->selectRaw('MONTH(payment_date) as month, payment_method, SUM(amount) as total, COUNT(*) as cnt')
            ->groupBy('month', 'payment_method')
            ->get()
            ->groupBy('month');

        $monthlyTotals = [];
        for ($m = 1; $m <= 12; $m++) {
            $rows = $monthly->get($m, collect());
            $cash = (float) $rows->where('payment_method', 'cash')->sum('total');
            $bank = (float) $rows->where('payment_method', 'bank_transfer')->sum('total');
            $monthlyTotals[$m] = ['cash' => $cash, 'bank' => $bank, 'total' => $cash + $bank];
        }

        $cashTotal = array_sum(array_column($monthlyTotals, 'cash'));
        $bankTotal = array_sum(array_column($monthlyTotals, 'bank'));
        $totalPaid = $cashTotal + $bankTotal;
        $membersPaid = Payment::where('status', 'confirmed')
            ->whereYear('payment_date', $year)
            ->distinct()
            ->count('member_id');

        return view('finance.payments.index', compact(
            'payments',
            'allocations',
            'receiptsByReference',
            'financialRefs',
            'year',
            'years',
            'monthlyTotals',
            'cashTotal',
            'bankTotal',
            'totalPaid',
            'membersPaid'
        ));
    }

    public function create(Request $request): View
    {
        $members = Member::where('status', 'active')->orderBy('member_code')->get();
        $member = $members->firstWhere('id', $request->integer('member_id'));
        $selectedMonths = [];

        if ($request->filled('contribution_id')) {
            $contribution = Contribution::find($request->integer('contribution_id'));
            if ($contribution) {
                $selectedMonths[] = sprintf('%04d-%02d', $contribution->year, $contribution->month);
            }
        } elseif ($request->filled('month') && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $request->string('month')->toString())) {
            $selectedMonths[] = $request->string('month')->toString();
        }

        return view('finance.payments.form', [
            'members' => $members,
            'member' => $member,
            'selectedMonths' => $selectedMonths,
            'payment' => null,
        ]);
    }

    public function formPrint(): View
    {
        $members = Member::where('status', 'active')->orderBy('member_code')->get();

        return view('finance.payments.form', [
            'members' => $members,
            'member' => null,
            'selectedMonths' => [],
            'payment' => null,
        ]);
    }

    public function store(PaymentRequest $request, ReferenceNumberService $references, IncomeReceiptService $receipts): RedirectResponse
    {
        $data = $request->validated();

        $member = Member::find($data['member_id']);
        if (! $member) {
            return back()->with('error', 'Membre introuvable.')->withInput();
        }

        $paymentDate = \Carbon\Carbon::createFromFormat('Y-m-d', $data['payment_date'])->startOfDay();
        $amount = round((float) $data['amount'], 2);
        $paymentMethod = $data['payment_method'];

        // Months are stored as "Y-m" so payments can cross year boundaries.
        $months = collect($data['months_covered'])->unique()->sort()->values();
        if ($months->count() !== count($data['months_covered'])) {
            throw ValidationException::withMessages(['months_covered' => 'Un même mois ne peut pas être sélectionné plusieurs fois.']);
        }

        $paymentRef = $references->next('PAY');
        $receiptRef = $references->next('REC');
        $financialRef = $references->next('RF');
        $userId = $request->user()->id;
        $notes = $data['notes'] ?? null;
        // Legacy receipt and financial-entry schemas use 'transfer', while the
        // dedicated payments table uses the clearer 'bank_transfer' value.
        $registerPaymentMethod = $paymentMethod === 'bank_transfer' ? 'transfer' : $paymentMethod;

        try {
            $result = DB::transaction(function () use (
                $member, $paymentDate, $amount, $paymentMethod, $months,
                $paymentRef, $receiptRef, $financialRef, $userId, $notes, $registerPaymentMethod, $receipts
            ) {
                // Prepare and lock contribution rows before creating any financial record.
                $contributionRows = collect();
                $availableAmount = 0.0;
                foreach ($months as $key) {
                    [$year, $month] = array_map('intval', explode('-', $key));
                    $defaultExpected = (float) (Contribution::where('year', $year)
                        ->where('month', $month)
                        ->value('amount_per_member')
                        ?? config('finance.default_monthly_contribution', 10.00));

                    $contribution = MemberContribution::firstOrCreate(
                        ['member_id' => $member->id, 'period_year' => $year, 'period_month' => $month],
                        ['expected_amount' => $defaultExpected, 'allocated_amount' => 0.00, 'status' => 'unpaid', 'paid_count' => 0, 'total_paid' => 0.00]
                    );
                    $contribution = MemberContribution::whereKey($contribution->id)->lockForUpdate()->firstOrFail();
                    // Keep the expected amount stored for an existing period. This
                    // protects historical contribution rates from later changes.
                    $expected = (float) ($contribution->expected_amount ?: $defaultExpected);
                    $due = max(0, round($expected - (float) $contribution->allocated_amount, 2));
                    $contributionRows->push(['model' => $contribution, 'expected' => $expected, 'due' => $due, 'year' => $year, 'month' => $month]);
                    $availableAmount += $due;
                }

                if ($availableAmount <= 0) {
                    throw ValidationException::withMessages(['months_covered' => 'Tous les mois sélectionnés sont déjà soldés.']);
                }
                if ($amount > round($availableAmount, 2) + 0.001) {
                    throw ValidationException::withMessages(['amount' => 'Le montant dépasse le solde restant des mois sélectionnés (' . number_format($availableAmount, 2, ',', ' ') . ' €).']);
                }

                // 1. One payment = one record, one receipt document, one financial entry.
                $payment = Payment::create([
                    'payment_reference' => $paymentRef,
                    'receipt_reference' => $receiptRef,
                    'member_id' => $member->id,
                    'payment_date' => $paymentDate->toDateString(),
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'status' => 'confirmed',
                    'notes' => $notes,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                // 2. Allocate the amount across the selected months (may span years).
                $covered = 0;
                $totalAllocated = 0.0;

                foreach ($contributionRows as $row) {
                    $year = $row['year'];
                    $month = $row['month'];
                    $expected = $row['expected'];
                    $contribution = $row['model'];
                    $previous = (float) $contribution->allocated_amount;
                    $allocationAmount = round(min($amount - $totalAllocated, $row['due']), 2);

                    if ($allocationAmount <= 0) {
                        continue;
                    }

                    PaymentAllocation::create([
                        'payment_id' => $payment->id,
                        'member_contribution_id' => $contribution->id,
                        'allocated_amount' => $allocationAmount,
                    ]);

                    $allocated = round($previous + $allocationAmount, 2);
                    $contribution->expected_amount = $expected;
                    $contribution->allocated_amount = $allocated;
                    $contribution->total_paid = $allocated;
                    $contribution->paid_count = (int) $contribution->paid_count + ($previous <= 0 ? 1 : 0);
                    $contribution->status = $this->resolveStatus($allocated, $expected, $year, $month);
                    $contribution->save();

                    // Keep the monthly contribution register in sync.
                    if ($monthly = Contribution::where('year', $year)->where('month', $month)->first()) {
                        if ($previous <= 0) {
                            $monthly->received_count = (int) $monthly->received_count + 1;
                        }
                        $monthly->total_received = round((float) $monthly->total_received + $allocationAmount, 2);
                        $monthly->save();
                    }

                    $covered++;
                    $totalAllocated += $allocationAmount;
                }

                if (abs($totalAllocated - $amount) > 0.001) {
                    throw ValidationException::withMessages(['amount' => 'Le montant n’a pas pu être entièrement affecté aux mois sélectionnés.']);
                }

                // 3. Single financial entry for the whole payment (actual payment date).
                FinancialEntry::create([
                    'reference' => $financialRef,
                    'entry_date' => $paymentDate->toDateString(),
                    'type' => 'income',
                    'category' => 'cotisation',
                    'description' => "Cotisation {$member->member_code} - {$paymentRef} - {$paymentMethod}",
                    'amount' => $amount,
                    'payment_method' => $registerPaymentMethod,
                    'source_type' => Payment::class,
                    'source_id' => $payment->id,
                    'notes' => 'Mois couverts : ' . $months->implode(', '),
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                // 4. Create the single general receipt linked to this payment.
                $receipts->sync($payment, $receiptRef);

                return ['payment' => $payment, 'covered' => $covered, 'selected' => $months->count(), 'allocated' => $totalAllocated];
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Une erreur est survenue lors de l\'enregistrement du paiement : ' . $e->getMessage())->withInput();
        }

        if ($result['covered'] === 0) {
            return redirect()->route('payments.index')
                ->with('error', 'Aucun mois éligible : les mois sélectionnés sont déjà soldés ou le membre est introuvable.')
                ->withInput();
        }

        $message = "Paiement {$paymentRef} enregistré ({$result['allocated']} €). "
            . "{$result['covered']} mois couverts sur {$result['selected']} sélectionnés.";

        if ($result['covered'] < $result['selected']) {
            $message .= ' Les mois déjà soldés ont été ignorés.';
        }

        return redirect()->route('payments.index')->with('success', $message);
    }

    public function show(Payment $payment): View
    {
        $payment->load('member');
        $allocations = PaymentAllocation::where('payment_id', $payment->id)
            ->with('memberContribution')
            ->get();
        $financialEntry = FinancialEntry::where('source_type', Payment::class)
            ->where('source_id', $payment->id)
            ->first();
        $receipt = Receipt::where('reference', $payment->receipt_reference)->first();

        return view('finance.payments.show', compact('payment', 'allocations', 'financialEntry', 'receipt'));
    }

    /** Cancel a confirmed payment while preserving its payment and allocation audit trail. */
    public function cancel(Request $request, Payment $payment, ReferenceNumberService $references): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        try {
            DB::transaction(function () use ($request, $payment, $references): void {
                $payment = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
                if ($payment->status !== 'confirmed') {
                    throw ValidationException::withMessages([
                        'payment' => 'Seul un paiement confirmé peut être annulé.',
                    ]);
                }

                $allocations = PaymentAllocation::where('payment_id', $payment->id)
                    ->with('memberContribution')
                    ->lockForUpdate()
                    ->get();

                $financialEntry = FinancialEntry::where('source_type', Payment::class)
                    ->where('source_id', $payment->id)
                    ->lockForUpdate()
                    ->first();

                // Keep the original income entry for audit. Add a matching expense reversal.
                if ($financialEntry) {
                    FinancialEntry::create([
                        'reference' => $references->next('RF'),
                        'entry_date' => now()->toDateString(),
                        'type' => 'expense',
                        'category' => 'cotisation_annulation',
                        'description' => 'Annulation du paiement ' . $payment->payment_reference,
                        'amount' => $payment->amount,
                        'payment_method' => $financialEntry->payment_method,
                        'source_type' => 'payment_cancellation',
                        'source_id' => $payment->id,
                        'notes' => $request->string('cancellation_reason')->toString(),
                        'created_by' => $request->user()->id,
                        'updated_by' => $request->user()->id,
                    ]);
                }

                $payment->status = 'cancelled';
                $payment->cancelled_at = now();
                $payment->cancelled_by = $request->user()->id;
                $payment->cancellation_reason = $request->string('cancellation_reason')->toString();
                $payment->updated_by = $request->user()->id;
                $payment->save();

                Receipt::where('reference', $payment->receipt_reference)
                    ->where('status', 'confirmed')
                    ->update(['status' => 'cancelled', 'updated_by' => $request->user()->id]);

                // Recalculate every affected month from confirmed payment allocations.
                $periods = $allocations->map(fn ($allocation) => $allocation->memberContribution)
                    ->filter()->unique('id');

                foreach ($periods as $contribution) {
                    $confirmedAllocations = PaymentAllocation::where('member_contribution_id', $contribution->id)
                        ->whereHas('payment', fn ($query) => $query->where('status', 'confirmed'))
                        ->with('payment')
                        ->get();
                    $allocated = round((float) $confirmedAllocations->sum('allocated_amount'), 2);
                    $expected = (float) $contribution->expected_amount;

                    $contribution->allocated_amount = $allocated;
                    $contribution->total_paid = $allocated;
                    $contribution->paid_count = $confirmedAllocations->pluck('payment_id')->unique()->count();
                    $contribution->status = $this->resolveStatus(
                        $allocated,
                        $expected,
                        (int) $contribution->period_year,
                        (int) $contribution->period_month
                    );
                    $contribution->save();

                    $monthlyContribution = Contribution::where('year', $contribution->period_year)
                        ->where('month', $contribution->period_month)
                        ->first();
                    if ($monthlyContribution) {
                        $allPeriodRows = MemberContribution::where('period_year', $contribution->period_year)
                            ->where('period_month', $contribution->period_month)->get();
                        $monthlyContribution->total_received = round((float) $allPeriodRows->sum('allocated_amount'), 2);
                        $monthlyContribution->received_count = $allPeriodRows->filter(fn ($row) =>
                            (float) $row->allocated_amount + 0.001 >= (float) $row->expected_amount
                        )->count();
                        $monthlyContribution->save();
                    }
                }
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            return back()->with('error', 'L’annulation a échoué. Aucune modification financière n’a été validée.');
        }

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Paiement annulé. Les allocations ont été recalculées et une écriture de contrepassation a été créée.');
    }

    private function resolveStatus(float $allocated, float $expected, int $year, int $month): string
    {
        if ($allocated <= 0) {
            return 'unpaid';
        }

        if ($allocated + 0.001 < $expected) {
            return 'partially_paid';
        }

        $now = now();
        if ($year > (int) $now->year || ($year === (int) $now->year && $month > (int) $now->month)) {
            return 'covered_in_advance';
        }

        return 'paid';
    }
}
