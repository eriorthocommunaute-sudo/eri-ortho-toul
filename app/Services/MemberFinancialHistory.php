<?php

namespace App\Services;

use App\Models\Contribution;
use App\Models\Donation;
use App\Models\FinancialEntry;
use App\Models\Member;
use App\Models\Offrande;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MemberFinancialHistory
{
    protected Member $member;
    protected ?int $filterYear;
    protected ?string $filterStatus;
    protected ?int $donationFilterYear;

    public function __construct(Member $member, ?int $year = null, ?string $status = null, ?int $donationYear = null)
    {
        $this->member = $member;
        $this->filterYear = $year;
        $this->filterStatus = $status;
        $this->donationFilterYear = $donationYear;
    }

    public function getMonthlyHistory(): Collection
    {
        $joinedYear = $this->member->joined_at?->year ?? now()->year;
        $currentYear = now()->year;
        $endYear = $this->filterYear ?? $currentYear;

        $months = collect();

        for ($year = $joinedYear; $year <= $endYear; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                $monthKey = sprintf('%04d-%02d', $year, $month);
                $monthDate = Carbon::create($year, $month, 1);

                // Skip months before member joined
                if ($this->member->joined_at && $monthDate->lt($this->member->joined_at->copy()->startOfMonth())) {
                    continue;
                }

                // Apply year filter
                if ($this->filterYear && $year !== $this->filterYear) {
                    continue;
                }

                // Special handling for 2026: only show Oct, Nov, Dec (per requirement)
                if ($year === 2026 && $month < 10) {
                    continue;
                }

                // Skip future months only when no specific year filter is applied
                // When a year is explicitly requested, show all its months
                // Exception: 2026 has a special business rule to show Oct-Dec regardless of current date
                if (!$this->filterYear && $year !== 2026 && $monthDate->isFuture()) {
                    continue;
                }

                $contribution = Contribution::where('year', $year)->where('month', $month)->first();
                $expectedAmount = $contribution?->amount_per_member ?? config('finance.default_monthly_contribution', 10.00);

                // Get confirmed receipts for this member and month
                $receipts = Receipt::where('member_id', $this->member->id)
                    ->where('status', 'confirmed')
                    ->whereHas('contribution', function ($q) use ($year, $month) {
                        $q->where('year', $year)->where('month', $month);
                    })
                    ->get();

                $paidAmount = $receipts->sum('amount');
                $paymentDate = $receipts->max('payment_date');
                $latestReceipt = $receipts->sortByDesc('payment_date')->first();

                // Get offrandes (member offerings) for this month
                $offrandes = Offrande::where('member_id', $this->member->id)
                    ->whereYear('offering_date', $year)
                    ->whereMonth('offering_date', $month)
                    ->confirmed()
                    ->get();

                $offrandeAmount = $offrandes->sum('amount');

                $totalPaid = $paidAmount + $offrandeAmount;

                // Determine status
                $status = $this->calculateStatus($paidAmount, $expectedAmount, $receipts->isNotEmpty());

                // Apply status filter
                if ($this->filterStatus && $status !== $this->filterStatus) {
                    continue;
                }

                $months->push([
                    'year' => $year,
                    'month' => $month,
                    'month_label' => (app()->getLocale() === 'ti' ? FinancialRegisterGenerator::MONTHS_TI : FinancialRegisterGenerator::MONTHS_FR)[$month] . ' ' . $year,
                    'expected_amount' => $expectedAmount,
                    'paid_amount' => (float) $paidAmount,
                    'offrande_amount' => (float) $offrandeAmount,
                    'total_paid' => (float) $totalPaid,
                    'payment_date' => $paymentDate,
                    'status' => $status,
                    'status_label' => $this->getStatusLabel($status),
                    'receipt_count' => $receipts->count(),
                    'offrande_count' => $offrandes->count(),
                    'latest_receipt' => $latestReceipt,
                    'contribution_id' => $contribution?->id,
                    'contribution_reference' => $contribution?->reference,
                ]);
            }
        }

        return $months->sortByDesc(fn ($m) => sprintf('%04d-%02d', $m['year'], $m['month']));
    }

    /**
     * Get donation history for the member (offrandes + donations where donor matches member)
     */
    public function getDonationHistory(): Collection
    {
        $memberName = $this->member->given_name . ' ' . $this->member->family_name;
        $year = $this->donationFilterYear ?? now()->year;

        // Get confirmed offrandes for this member in the selected year
        $offrandes = Offrande::where('member_id', $this->member->id)
            ->whereYear('offering_date', $year)
            ->confirmed()
            ->orderByDesc('offering_date')
            ->get();

        // Get confirmed donations where donor_name matches member name
        $donations = Donation::where('donor_name', 'like', "%{$memberName}%")
            ->whereYear('donation_date', $year)
            ->confirmed()
            ->orderByDesc('donation_date')
            ->get();

        $donationHistory = collect();

        foreach ($offrandes as $offrande) {
            $financialEntry = FinancialEntry::where('source_type', Offrande::class)
                ->where('source_id', $offrande->id)
                ->first();

            $donationHistory->push([
                'type' => 'offrande',
                'reference' => $offrande->reference,
                'date' => $offrande->offering_date,
                'donor' => $offrande->donor_name ?? $memberName,
                'category' => $offrande->occasion ?? __('app.general_donation'),
                'amount' => (float) $offrande->amount,
                'payment_method' => $offrande->payment_method ?? ($financialEntry?->payment_method ?? '—'),
                'notes' => $offrande->notes,
                'financial_reference' => $offrande->financial_reference,
                'id' => $offrande->id,
            ]);
        }

        foreach ($donations as $donation) {
            $financialEntry = FinancialEntry::where('source_type', Donation::class)
                ->where('source_id', $donation->id)
                ->first();

            $donationHistory->push([
                'type' => 'donation',
                'reference' => $donation->reference,
                'date' => $donation->donation_date,
                'donor' => $donation->donor_name,
                'category' => $donation->purpose ?? $donation->donor_type ?? __('app.general_donation'),
                'amount' => (float) $donation->amount,
                'payment_method' => $financialEntry?->payment_method ?? '—',
                'notes' => $donation->notes,
                'financial_reference' => $donation->financial_reference,
                'id' => $donation->id,
            ]);
        }

        // Sort by date descending (newest first)
        return $donationHistory->sortByDesc(fn ($d) => $d['date'])->values();
    }

    /**
     * Get donation summary for the selected year
     */
    public function getDonationSummary(): array
    {
        $history = $this->getDonationHistory();

        $totalAmount = $history->sum('amount');
        $count = $history->count();
        $average = $count > 0 ? $totalAmount / $count : 0;

        // Breakdown by category
        $byCategory = $history->groupBy('category')->map(function ($items) {
            return [
                'count' => $items->count(),
                'total' => (float) $items->sum('amount'),
            ];
        })->toArray();

        return [
            'total_amount' => (float) $totalAmount,
            'count' => $count,
            'average_amount' => (float) $average,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Get available years for donation history (2026, 2027 as per requirement)
     */
    public function getDonationAvailableYears(): array
    {
        return [2027, 2026]; // 2027 first (newest), then 2026
    }

    /**
     * Get available years for cotisation table
     * 2026: only Oct-Dec, 2027: all months
     */
    public function getCotisationAvailableYears(): array
    {
        $currentYear = now()->year;
        $years = [];
        for ($y = $currentYear; $y >= 2026; $y--) {
            $years[] = $y;
        }
        return $years;
    }

    protected function calculateStatus(float $paidAmount, float $expectedAmount, bool $hasReceipts): string
    {
        if (!$hasReceipts) {
            return 'unpaid';
        }

        if ($paidAmount >= $expectedAmount) {
            return 'paid';
        }

        if ($paidAmount > 0) {
            return 'partial';
        }

        return 'unpaid';
    }

    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => __('app.paid'),
            'partial' => __('app.partial'),
            'unpaid' => __('app.unpaid'),
            'pending' => __('app.pending_verification'),
            default => $status,
        };
    }

    public function getSummaryCards(): array
    {
        $currentYear = now()->year;
        $joinedYear = $this->member->joined_at?->year ?? $currentYear;

        // This year confirmed receipts (contributions)
        $thisYearReceipts = Receipt::where('member_id', $this->member->id)
            ->where('status', 'confirmed')
            ->whereYear('payment_date', $currentYear)
            ->get();

        // Lifetime confirmed receipts
        $lifetimeReceipts = Receipt::where('member_id', $this->member->id)
            ->where('status', 'confirmed')
            ->get();

        // This year offrandes
        $thisYearOffrandes = Offrande::where('member_id', $this->member->id)
            ->whereYear('offering_date', $currentYear)
            ->confirmed()
            ->get();

        // Lifetime offrandes
        $lifetimeOffrandes = Offrande::where('member_id', $this->member->id)
            ->confirmed()
            ->get();

        // Expected contributions this year
        $thisYearExpected = Contribution::where('year', $currentYear)
            ->where('month', '>=', 1)
            ->where('month', '<=', now()->month)
            ->sum('amount_per_member');

        // Expected contributions since joining
        $lifetimeExpected = Contribution::where(function ($q) use ($joinedYear, $currentYear) {
            $q->where('year', '>', $joinedYear)
              ->orWhere(function ($q2) use ($joinedYear) {
                  $q2->where('year', $joinedYear)
                     ->where('month', '>=', $this->member->joined_at?->month ?? 1);
              });
        })->sum('amount_per_member');

        $thisYearContributionsPaid = $thisYearReceipts->sum('amount');
        $thisYearOffrandesTotal = $thisYearOffrandes->sum('amount');
        $lifetimeContributionsPaid = $lifetimeReceipts->sum('amount');
        $lifetimeOffrandesTotal = $lifetimeOffrandes->sum('amount');

        // Outstanding = expected - paid (only for months with contributions)
        $thisYearOutstanding = max(0, $thisYearExpected - $thisYearContributionsPaid);
        $lifetimeOutstanding = max(0, $lifetimeExpected - $lifetimeContributionsPaid);

        return [
            'this_year_contributions_paid' => (float) $thisYearContributionsPaid,
            'this_year_offrandes_total' => (float) $thisYearOffrandesTotal,
            'this_year_total_paid' => (float) ($thisYearContributionsPaid + $thisYearOffrandesTotal),
            'this_year_outstanding' => (float) $thisYearOutstanding,
            'lifetime_contributions_paid' => (float) $lifetimeContributionsPaid,
            'lifetime_offrandes_total' => (float) $lifetimeOffrandesTotal,
            'lifetime_total_paid' => (float) ($lifetimeContributionsPaid + $lifetimeOffrandesTotal),
            'lifetime_outstanding' => (float) $lifetimeOutstanding,
            'this_year_expected' => (float) $thisYearExpected,
            'lifetime_expected' => (float) $lifetimeExpected,
        ];
    }

    public function getAvailableYears(): array
    {
        $joinedYear = $this->member->joined_at?->year ?? now()->year;
        $currentYear = now()->year;

        return range($currentYear, $joinedYear);
    }

    public function exportToCsv(): string
    {
        $history = $this->getMonthlyHistory();

        $headers = [
            __('app.year'),
            __('app.month'),
            __('app.expected_contribution'),
            __('app.paid_contribution'),
            __('app.offrande'),
            __('app.total_paid'),
            __('app.payment_date'),
            __('app.status'),
            __('app.receipt_count'),
            __('app.offrande_count'),
        ];

        $rows = $history->map(function ($row) {
            return [
                $row['year'],
                $row['month'],
                number_format($row['expected_amount'], 2, ',', ' ') . ' €',
                number_format($row['paid_amount'], 2, ',', ' ') . ' €',
                number_format($row['offrande_amount'], 2, ',', ' ') . ' €',
                number_format($row['total_paid'], 2, ',', ' ') . ' €',
                $row['payment_date']?->format('d/m/Y') ?? '',
                $row['status_label'],
                $row['receipt_count'],
                $row['offrande_count'],
            ];
        })->toArray();

        array_unshift($rows, $headers);

        $output = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        // Add BOM for Excel compatibility
        return "\xEF\xBB\xBF" . $csv;
    }
}
