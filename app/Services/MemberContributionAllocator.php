<?php

namespace App\Services;

use App\Models\MemberContribution;
use App\Models\Receipt;

class MemberContributionAllocator
{
    public function allocate(Receipt $receipt): void
    {
        $this->apply($receipt, 1);
    }

    public function release(Receipt $receipt): void
    {
        $this->apply($receipt, -1);
    }

    private function apply(Receipt $receipt, int $sign): void
    {
        if (! $receipt->member_id) {
            return;
        }

        [$year, $month] = $this->resolvePeriod($receipt);
        $expected = $this->expectedFor($receipt);

        $row = MemberContribution::firstOrCreate(
            [
                'member_id' => $receipt->member_id,
                'period_year' => $year,
                'period_month' => $month,
            ],
            [
                'expected_amount' => $expected,
                'allocated_amount' => 0,
                'status' => 'unpaid',
                'paid_count' => 0,
                'total_paid' => 0,
            ]
        );

        $allocated = max(0, round((float) $row->allocated_amount + ((float) $receipt->amount * $sign), 2));

        $row->expected_amount = $expected;
        $row->allocated_amount = $allocated;
        $row->total_paid = $allocated;
        $row->paid_count = max(0, (int) $row->paid_count + $sign);
        $row->status = $this->resolveStatus($allocated, (float) $expected, $year, $month);
        $row->save();
    }

    private function resolvePeriod(Receipt $receipt): array
    {
        $contribution = $receipt->contribution;

        if ($contribution) {
            return [(int) $contribution->year, (int) $contribution->month];
        }

        $date = $receipt->payment_date ?? now();

        return [(int) $date->format('Y'), (int) $date->format('m')];
    }

    private function expectedFor(Receipt $receipt): float
    {
        return (float) ($receipt->contribution?->amount_per_member ?? 10.00);
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
