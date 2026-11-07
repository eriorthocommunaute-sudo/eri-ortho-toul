<?php

namespace App\Console\Commands;

use App\Services\RecurringExpenseGenerator;
use Illuminate\Console\Command;

class GenerateRecurringExpenses extends Command
{
    protected $signature = 'finance:generate-recurring-expenses {--year=} {--month=}';
    protected $description = 'Generate monthly obligations for rent, insurance and priest indemnity.';

    public function handle(RecurringExpenseGenerator $generator): int
    {
        $year = (int) ($this->option('year') ?: now()->year);
        $month = (int) ($this->option('month') ?: now()->month);

        if ($year < 2020 || $year > 2100 || $month < 1 || $month > 12) {
            $this->error('Invalid year or month.');
            return self::FAILURE;
        }

        $created = $generator->generate($year, $month);
        $overdue = $generator->markOverdue();

        $this->info("Generated {$created} recurring expense obligation(s) for {$year}-{$month}.");
        $this->line("Updated {$overdue} overdue obligation(s).");
        $this->line('No financial entries were created. Confirm each actual payment to post it to the financial register.');

        return self::SUCCESS;
    }
}
