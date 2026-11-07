<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\ContributionRequest;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\MemberContribution;
use App\Models\PaymentAllocation;
use App\Models\Payment;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;

class ContributionController extends Controller
{
    public function index(Request $request): View
    {
        $contributions = Contribution::query()
            ->when($request->filled('year'), fn ($q) => $q->where('year', $request->integer('year')))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(20)
            ->withQueryString();

        return view('finance.contributions.index', compact('contributions'));
    }

    public function matrix(Request $request): View
    {
        $year = $request->filled('year') ? $request->integer('year') : (int) now()->format('Y');
        $printMonth = $request->boolean('print_month');
        $printMonthNumber = null;

        if ($printMonth) {
            $validated = $request->validate([
                'year' => ['required', 'integer', 'between:2020,2100'],
                'month' => ['required', 'integer', 'between:1,12'],
            ]);
            $printMonthNumber = (int) $validated['month'];
        }

        $members = Member::where('status', 'active')->orderBy('member_code')->get();

        if ($request->boolean('blank')) {
            $validated = $request->validate([
                'year' => ['required', 'integer', 'between:2020,2100'],
                'month' => ['required', 'integer', 'between:1,12'],
            ]);
            $year = (int) $validated['year'];
            $month = (int) $validated['month'];

            return view('finance.contributions.matrix-blank', [
                'members' => $members,
                'year' => $year,
                'monthName' => \App\Services\FinancialRegisterGenerator::MONTHS_FR[$month],
                'monthNameTi' => \App\Services\FinancialRegisterGenerator::MONTHS_TI[$month],
            ]);
        }

        $memberIds = $members->pluck('id');

        $contributions = MemberContribution::query()
            ->where('period_year', $year)
            ->whereIn('member_id', $memberIds)
            ->get()
            ->keyBy(fn ($item) => $item->member_id . ':' . $item->period_month);

        // Load the real payment and allocation records. PA remains a display state,
        // never a payment or a separate financial transaction.
        $allocations = PaymentAllocation::query()
            ->whereHas('memberContribution', fn ($query) => $query
                ->where('period_year', $year)
                ->whereIn('member_id', $memberIds))
            ->with(['payment', 'memberContribution'])
            ->get();

        $matrix = [];
        $paymentDetails = [];
        foreach ($members as $member) {
            $memberMatrix = [];
            for ($month = 1; $month <= 12; $month++) {
                $contribution = $contributions->get($member->id . ':' . $month);
                $memberMatrix[$month] = [
                    'status' => $contribution?->status ?? 'unpaid',
                    'allocated_amount' => (float) ($contribution?->allocated_amount ?? 0),
                    'expected_amount' => (float) ($contribution?->expected_amount
                        ?? Contribution::where('year', $year)->where('month', $month)->value('amount_per_member')
                        ?? config('finance.default_monthly_contribution', 10.00)),
                    // Show the amount allocated to this member and month even
                    // when the detailed payment-allocation record is missing.
                    'display' => ($contribution && (float) $contribution->allocated_amount > 0)
                        ? number_format((float) $contribution->allocated_amount, 2, ',', ' ') . ' €'
                        : '0',
                    'payment_id' => null,
                    'payment_reference' => null,
                ];
            }
            $matrix[$member->id] = $memberMatrix;
        }

        foreach ($allocations as $allocation) {
            $payment = $allocation->payment;
            $contribution = $allocation->memberContribution;
            if (! $payment || $payment->status !== 'confirmed' || ! $contribution) {
                continue;
            }

            $memberId = $contribution->member_id;
            $coveredMonth = (int) $contribution->period_month;
            $coveredKey = sprintf('%04d-%02d', $contribution->period_year, $coveredMonth);
            $paymentKey = $payment->payment_date->format('Y-m');

            $cell = &$matrix[$memberId][$coveredMonth];
            if ($paymentKey === $coveredKey) {
                $cell['display'] = number_format((float) $payment->amount, 2, ',', ' ') . ' €';
                $cell['payment_id'] = $payment->id;
                $cell['payment_reference'] = $payment->payment_reference;
            } elseif ($paymentKey < $coveredKey) {
                $cell['display'] = $contribution->status === 'partially_paid' ? 'Partial' : 'PA';
                $cell['payment_id'] = $payment->id;
                $cell['payment_reference'] = $payment->payment_reference;
                if ($contribution->status !== 'partially_paid') {
                    $cell['status'] = 'covered_in_advance';
                }
            } elseif ($paymentKey > $coveredKey) {
                // For arrears, show the amount allocated to the earlier period.
                // The full cash receipt remains visible only in its payment month.
                $cell['display'] = $contribution->status === 'partially_paid'
                    ? 'Partial'
                    : number_format((float) $allocation->allocated_amount, 2, ',', ' ') . ' €';
                $cell['payment_id'] = $payment->id;
                $cell['payment_reference'] = $payment->payment_reference;
            }
            unset($cell);

            $paymentDetails[$payment->id] = [
                'reference' => $payment->payment_reference,
                'date' => $payment->payment_date->format('d/m/Y'),
                'amount' => number_format((float) $payment->amount, 2, ',', ' ') . ' €',
                'method' => $payment->payment_method,
                'receipt_reference' => $payment->receipt_reference,
                'original_month' => $payment->payment_date->format('m/Y'),
            ];
        }

        // Show actual receipts in their payment month, independently of the
        // contribution year. This also makes a cross-year advance traceable.
        $paymentMonthRecords = PaymentAllocation::query()
            ->whereHas('memberContribution')
            ->whereHas('payment', fn ($query) => $query
                ->where('status', 'confirmed')->whereYear('payment_date', $year))
            ->with(['payment', 'memberContribution'])
            ->get()
            ->groupBy(fn ($allocation) => $allocation->memberContribution->member_id . ':' . $allocation->payment->payment_date->month);

        foreach ($paymentMonthRecords as $key => $records) {
            [$memberId, $month] = array_map('intval', explode(':', $key));
            if (isset($matrix[$memberId][$month])) {
                $uniquePayments = $records->pluck('payment')->unique('id')->values();
                $received = (float) $uniquePayments->sum('amount');
                $payment = $uniquePayments->first();
                $matrix[$memberId][$month]['display'] = number_format($received, 2, ',', ' ') . ' €';
                // Link to the first original payment when several payments share a month.
                $matrix[$memberId][$month]['payment_id'] = $payment?->id;
                $matrix[$memberId][$month]['payment_reference'] = $uniquePayments->pluck('payment_reference')->implode(', ');
            }
        }

        $cashReceived = Payment::where('status', 'confirmed')->whereYear('payment_date', $year)->sum('amount');

        return view('finance.contributions.matrix', [
            'members' => $members,
            'matrix' => $matrix,
            'paymentDetails' => $paymentDetails,
            'cashReceived' => (float) $cashReceived,
            'year' => $year,
            'yearOptions' => range((int) now()->format('Y') - 2, (int) now()->format('Y') + 1),
            'monthOptions' => \App\Services\FinancialRegisterGenerator::MONTHS_FR,
            'autoPrint' => $printMonth,
            'printMonthNumber' => $printMonthNumber,
        ]);
    }

    /** Export the same contribution matrix used by the web interface as PDF. */
    public function exportPdf(Request $request)
    {
        $data = $this->matrix($request)->getData();
        $pdf = Pdf::loadView('finance.contributions.exports.matrix-pdf', $data)
            ->setPaper('a3', 'landscape');

        return $pdf->download('cotisations-' . $data['year'] . '.pdf');
    }

    /** Export an Excel-compatible SpreadsheetML workbook without adding a package dependency. */
    public function exportExcel(Request $request)
    {
        $data = $this->matrix($request)->getData();
        $year = (int) $data['year'];
        $months = \App\Services\FinancialRegisterGenerator::MONTHS_FR;
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<?mso-application progid="Excel.Sheet"?>'
            . '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            . 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            . '<Worksheet ss:Name="Cotisations"><Table>';

        $addRow = static function (array $values) use (&$xml): void {
            $xml .= '<Row>';
            foreach ($values as $value) {
                $xml .= '<Cell><Data ss:Type="String">'
                    . htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                    . '</Data></Cell>';
            }
            $xml .= '</Row>';
        };

        $addRow(['Matrice des cotisations', $year]);
        $addRow(array_merge(['Code membre', 'Nom'], array_values($months), ['Total payé', 'Solde restant']));

        foreach ($data['members'] as $member) {
            $memberMatrix = $data['matrix'][$member->id] ?? [];
            $allocatedTotal = 0.0;
            $expectedTotal = 0.0;
            $cells = [];
            for ($month = 1; $month <= 12; $month++) {
                $cell = $memberMatrix[$month] ?? ['display' => '0', 'allocated_amount' => 0, 'expected_amount' => 10];
                $cells[] = $cell['display'] ?? '0';
                $allocatedTotal += (float) ($cell['allocated_amount'] ?? 0);
                $expectedTotal += (float) ($cell['expected_amount'] ?? 10);
            }
            $addRow(array_merge(
                [$member->member_code, trim($member->given_name . ' ' . $member->family_name)],
                $cells,
                [number_format($allocatedTotal, 2, '.', '') . ' EUR', number_format(max(0, $expectedTotal - $allocatedTotal), 2, '.', '') . ' EUR']
            ));
        }

        $xml .= '</Table></Worksheet></Workbook>';
        $filename = 'cotisations-' . $year . '.xls';

        return response($xml, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => ' no-store, no-cache',
        ]);
    }

    public function create(): View
    {
        $currentYear = (int) now()->format('Y');
        $currentMonth = (int) now()->format('m');

        $nextMonth = $currentMonth === 12 ? 1 : $currentMonth + 1;
        $nextYear = $currentMonth === 12 ? $currentYear + 1 : $currentYear;

        $existing = Contribution::where('year', $nextYear)->where('month', $nextMonth)->exists();

        $activeMembers = Member::where('status', 'active')->count();

        return view('finance.contributions.create', compact('nextYear', 'nextMonth', 'existing', 'activeMembers'));
    }

    public function store(ContributionRequest $request, ReferenceNumberService $references): RedirectResponse
    {
        $data = $request->validated();
        $data['reference'] = $references->nextCot($data['year'], $data['month']);
        $data['expected_count'] = Member::where('status', 'active')->count();
        $data['total_expected'] = $data['expected_count'] * $data['amount_per_member'];
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $contribution = Contribution::create($data);

        return redirect()->route('contributions.show', $contribution)->with('success', __('app.contribution_created'));
    }

    public function show(Contribution $contribution): View
    {
        $contribution->load(['receipts.member', 'notices.member']);
        $unpaidMembers = Member::where('status', 'active')
            ->whereDoesntHave('memberContributions', fn ($query) => $query
                ->where('period_year', $contribution->year)
                ->where('period_month', $contribution->month)
                ->whereColumn('allocated_amount', '>=', 'expected_amount'))
            ->get();

        $paymentAllocations = PaymentAllocation::query()
            ->whereHas('memberContribution', fn ($query) => $query
                ->where('period_year', $contribution->year)
                ->where('period_month', $contribution->month))
            ->with(['payment.member'])
            ->get();

        return view('finance.contributions.show', compact('contribution', 'unpaidMembers', 'paymentAllocations'));
    }

    public function edit(Contribution $contribution): View
    {
        return view('finance.contributions.edit', compact('contribution'));
    }

    public function update(ContributionRequest $request, Contribution $contribution): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $contribution->update($data);

        return redirect()->route('contributions.show', $contribution)->with('success', __('app.contribution_updated'));
    }

    public function destroy(Request $request, Contribution $contribution): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('finance.record'), 403);
        $contribution->delete();

        return redirect()->route('contributions.index')->with('success', __('app.contribution_deleted'));
    }
}
