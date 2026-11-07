<?php

namespace App\Services;

use App\Models\RecurringExpenseOccurrence;
use App\Models\RecurringExpenseTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecurringExpenseGenerator
{
    /** Generate missing obligations for one month. Safe to run repeatedly. */
    public function generate(int $year, int $month, ?int $userId = null): int
    {
        $period = Carbon::create($year, $month, 1)->startOfMonth();
        $created = 0;

        $templates = RecurringExpenseTemplate::query()
            ->where('is_active', true)
            ->where(function ($query) use ($period) {
                $query->where('start_year', '<', $period->year)
                    ->orWhere(function ($q) use ($period) {
                        $q->where('start_year', $period->year)->where('start_month', '<=', $period->month);
                    });
            })->get();

        foreach ($templates as $template) {
            $dueDay = min((int) $template->due_day, $period->daysInMonth);
            $reference = sprintf('FEX-%s-%04d-%02d', strtoupper($template->category), $year, $month);

            $wasCreated = DB::table('recurring_expense_occurrences')->insertOrIgnore([
                'reference' => $reference,
                'template_id' => $template->id,
                'period_year' => $year,
                'period_month' => $month,
                'amount_due' => $template->amount,
                'amount_paid' => 0,
                'due_date' => $period->copy()->day($dueDay)->toDateString(),
                'status' => 'pending',
                'created_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $created += (int) $wasCreated;
        }

        return $created;
    }

    /** Mark overdue unpaid obligations without posting anything to the ledger. */
    public function markOverdue(): int
    {
        return RecurringExpenseOccurrence::query()
            ->where('status', 'pending')
            ->whereDate('due_date', '<', today())
            ->update(['status' => 'overdue', 'updated_at' => now()]);
    }
}
