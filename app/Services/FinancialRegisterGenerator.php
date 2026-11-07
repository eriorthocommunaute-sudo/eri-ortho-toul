<?php

namespace App\Services;

use App\Models\FinancialEntry;
use App\Models\FoundingCotisation;
use App\Models\Payment;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class FinancialRegisterGenerator
{
    public const ASSOCIATION_FR = 'Communauté Orthodoxe Érythréenne de Toulouse';
    public const ASSOCIATION_TI = 'ኦርቶዶክስ ተዋሕዶ ቤተ ክርስቲያን ኤርትራ ባእታ ማርያም ቱሉዝ ፈረንሳ';
    public const DOC_LINE = 'DOCUMENT ASSOCIATIF / ሰነድ ማሕበር';
    public const DESCRIPTION_FR = 'Registre des recettes et dépenses';
    public const DESCRIPTION_TI = 'መዝገብ እቶትን ወጻኢታትን';

    public const MONTHS_TI = [
        1 => 'ጥሪ', 2 => 'ለካቲት', 3 => 'መጋቢት', 4 => 'ሚያዝያ',
        5 => 'ግንቦት', 6 => 'ሰነ', 7 => 'ሓምለ', 8 => 'ነሓሰ',
        9 => 'መስከረም', 10 => 'ጥቅምቲ', 11 => 'ሕዳር', 12 => 'ታሕሳስ',
    ];

    public const MONTHS_FR = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre',
    ];

    public const MONTHLY_ROWS = 20;
    public const QUARTERLY_ROWS = 50;

    /**
     * Return the aggregated register rows for the financial-entry listing.
     *
     * Membership contributions are aggregated by payment month using the same
     * rules as the official monthly/quarterly register.
     */
    public function generateAggregatedEntries(?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        $candidateDates = collect([
            FinancialEntry::query()->min('entry_date'),
            Payment::query()->where('status', 'confirmed')->min('payment_date'),
            FoundingCotisation::query()->min('payment_date'),
            FoundingCotisation::query()->whereNull('payment_date')->min('created_at'),
        ])->filter();

        $startDate = $dateFrom
            ? Carbon::parse($dateFrom)->startOfDay()
            : Carbon::parse($candidateDates->min() ?? now()->startOfYear())->startOfDay();

        $endDate = $dateTo
            ? Carbon::parse($dateTo)->endOfDay()
            : now()->endOfDay();

        abort_if($startDate->gt($endDate), 422, 'La date de début doit être antérieure ou égale à la date de fin.');

        return $this->buildRegister($startDate, $endDate)['entries'];
    }

    public function generateMonthlyRegister(int $year, int $month): array
    {
        abort_if($month < 1 || $month > 12, 422, 'Mois invalide.');

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $reference = sprintf('RF-%d-%02d', $year, $month);
        $periodFr = self::MONTHS_FR[$month] . ' ' . $year;
        $periodTi = self::MONTHS_TI[$month] . ' ' . $year;

        $register = $this->buildRegister($startDate, $endDate, reserveMonthlyCotisationLine: true);

        return $register + [
            'reference' => $reference,
            'year' => $year,
            'month' => $month,
            'period_type' => 'monthly',
            'period_label' => $periodFr,
            'period_label_ti' => $periodTi,
            'title' => 'Registre financier, ' . $periodFr . ' / መዝገብ ገንዘብ : ' . $periodTi,
            'empty_rows_count' => self::MONTHLY_ROWS,
            'visible_rows' => max(self::MONTHLY_ROWS, $register['entries']->count()),
        ] + $this->headerData($startDate, $endDate, $reference, $periodFr, $periodTi);
    }

    public function generateQuarterlyRegister(int $year, int $quarter): array
    {
        abort_if($quarter < 1 || $quarter > 4, 422, 'Trimestre invalide.');

        $monthStart = ($quarter - 1) * 3 + 1;
        $monthEnd = $monthStart + 2;
        $startDate = Carbon::create($year, $monthStart, 1)->startOfMonth();
        $endDate = Carbon::create($year, $monthEnd, 1)->endOfMonth();
        $reference = sprintf('RF-%d-T%d', $year, $quarter);
        $periodFr = self::MONTHS_FR[$monthStart] . '–' . self::MONTHS_FR[$monthEnd] . ' ' . $year;
        $periodTi = self::MONTHS_TI[$monthStart] . '–' . self::MONTHS_TI[$monthEnd] . ' ' . $year;
        $register = $this->buildRegister($startDate, $endDate);

        return $register + [
            'reference' => $reference,
            'year' => $year,
            'quarter' => $quarter,
            'period_type' => 'quarterly',
            'period_label' => 'T' . $quarter . ' ' . $year . ' (' . $periodFr . ')',
            'period_label_ti' => 'T' . $quarter . ' ' . $year . ' (' . $periodTi . ')',
            'title' => 'Registre financier, T' . $quarter . ' ' . $year . ' / መዝገብ ገንዘብ : T' . $quarter . ' ' . $year,
            'empty_rows_count' => max(self::QUARTERLY_ROWS, $register['entries']->count()),
            'visible_rows' => max(self::QUARTERLY_ROWS, $register['entries']->count()),
        ] + $this->headerData($startDate, $endDate, $reference, $periodFr, $periodTi);
    }

    /** Build real register rows while keeping member payments aggregated once per month. */
    protected function buildRegister(Carbon $startDate, Carbon $endDate, bool $reserveMonthlyCotisationLine = false): array
    {
        $start = $startDate->toDateString();
        $end = $endDate->toDateString();

        $openingBalance = $this->balanceBefore($start);
        $entries = collect();

        // Individual membership payments never appear as separate register lines.
        // Legacy cotisation entries and confirmed Payment records become one COT row per month.
        // A migrated legacy receipt must not be counted alongside its new Payment record.
        // Keep only confirmed payments' receipt references for the duplicate check.
        $migratedReceiptReferences = Payment::query()
            ->where('status', 'confirmed')
            ->whereNotNull('receipt_reference')
            ->pluck('receipt_reference')
            ->flip();

        $legacyCotisations = FinancialEntry::query()
            ->with('source')
            ->where('type', 'income')
            ->where('category', 'cotisation')
            ->where('source_type', '!=', Payment::class)
            ->whereBetween('entry_date', [$start, $end])
            ->get()
            ->filter(function (FinancialEntry $entry) use ($migratedReceiptReferences) {
                // Non-receipt legacy income remains eligible.
                if ($entry->source_type !== Receipt::class) {
                    return true;
                }

                // Count a legacy receipt only when it is confirmed and has not been migrated.
                $receipt = $entry->source;

                return $receipt?->status === 'confirmed'
                    && ! $migratedReceiptReferences->has($receipt->reference);
            });

        $confirmedPayments = Payment::query()
            ->where('status', 'confirmed')
            ->whereBetween('payment_date', [$start, $end])
            ->get();

        $cotisationGroups = $legacyCotisations
            ->groupBy(fn (FinancialEntry $entry) => Carbon::parse($entry->entry_date)->format('Y-m'));

        foreach ($confirmedPayments->groupBy(fn (Payment $payment) => Carbon::parse($payment->payment_date)->format('Y-m')) as $monthKey => $payments) {
            $cotisationGroups->put($monthKey, $cotisationGroups->get($monthKey, collect())->concat($payments));
        }

        $cotisationMonthKeys = $cotisationGroups->keys();
        if ($reserveMonthlyCotisationLine) {
            $cotisationMonthKeys = collect();
            $monthCursor = $startDate->copy()->startOfMonth();
            while ($monthCursor->lte($endDate)) {
                $cotisationMonthKeys->push($monthCursor->format('Y-m'));
                $monthCursor->addMonth();
            }
        }

        foreach ($cotisationMonthKeys as $monthKey) {
            [$year, $month] = array_map('intval', explode('-', $monthKey));
            $items = $cotisationGroups->get($monthKey, collect());
            $amount = round((float) $items->sum(fn ($item) => (float) $item->amount), 2);
            if (! $reserveMonthlyCotisationLine && $amount <= 0) {
                continue;
            }

            $methods = $items->map(function ($item) {
                $method = $item instanceof Payment ? $item->payment_method : $item->payment_method;
                return $method === 'bank_transfer' ? 'transfer' : $method;
            })->filter()->unique()->values();

            $methodLabel = $methods->count() > 1 ? 'Mixte' : ($methods->isEmpty() ? '' : $this->paymentMethodLabel($methods->first()));
            $paymentCount = $items->count();
            $entries->push([
                'entry_date' => $reserveMonthlyCotisationLine
                    ? Carbon::create($year, $month, 1)->max($startDate)->toDateString()
                    : Carbon::create($year, $month, 1)->endOfMonth()->toDateString(),
                'source_reference' => sprintf('COT-%d-%02d', $year, $month),
                'source_code' => 'COT',
                'category_label' => 'Cotisations mensuelles des membres',
                'category' => 'cotisation',
                'type' => 'income',
                'amount' => $amount,
                'payment_method' => $methodLabel,
                'notes' => sprintf('Total mensuel agrégé, %d paiement(s).', $paymentCount),
                'sort_id' => 0,
            ]);

        }

        // Other income and all expenses remain individually traceable.
        $otherEntries = FinancialEntry::query()
            ->with('source')
            ->whereBetween('entry_date', [$start, $end])
            ->where(function ($query) {
                $query->where('category', '!=', 'cotisation')
                    ->orWhere('type', '!=', 'income');
            })
            ->where('source_type', '!=', Payment::class)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        foreach ($otherEntries as $entry) {
            $entries->push([
                'entry_date' => Carbon::parse($entry->entry_date)->toDateString(),
                'source_reference' => data_get($entry->source, 'reference') ?: $entry->reference,
                'source_code' => $this->sourceCodeForEntry($entry),
                'category_label' => ucfirst(str_replace('_', ' ', $entry->category)),
                'category' => $entry->category,
                'type' => $entry->type,
                'amount' => (float) $entry->amount,
                'payment_method' => $this->paymentMethodLabel($entry->payment_method),
                'notes' => trim(($entry->description ?? '') . ($entry->notes ? ' | ' . $entry->notes : '')),
                'sort_id' => (int) $entry->id,
            ]);
        }

        // Founding contributions are stored separately from financial_entries in this project.
        $founding = FoundingCotisation::query()
            ->orderBy('payment_date')
            ->orderBy('id')
            ->get()
            ->filter(function (FoundingCotisation $item) use ($startDate, $endDate) {
                $date = $item->payment_date ?? $item->created_at;
                return $date && Carbon::parse($date)->betweenIncluded($startDate, $endDate);
            });

        foreach ($founding as $item) {
            $foundingDate = $item->payment_date ?? $item->created_at;
            $entries->push([
                'entry_date' => Carbon::parse($foundingDate)->toDateString(),
                'source_reference' => sprintf('FCO-%s-%03d', Carbon::parse($foundingDate)->format('Y'), $item->id),
                'source_code' => 'FCO',
                'category_label' => 'Cotisation fondatrice',
                'category' => 'founding_cotisations',
                'type' => 'income',
                'amount' => (float) $item->amount,
                'payment_method' => $this->paymentMethodLabel($item->payment_method),
                'notes' => $item->notes ?? 'Cotisation fondatrice',
                'sort_id' => (int) $item->id,
            ]);
        }

        $entries = $entries->sortBy(['entry_date', 'sort_id'])->values();
        $balance = $openingBalance;
        $entries = $entries->map(function (array $entry, int $index) use (&$balance, $startDate, $endDate) {
            $balance += $entry['type'] === 'income' ? $entry['amount'] : -$entry['amount'];
            $entry['line_reference'] = sprintf('%s-%02d', $this->periodReferencePrefix($startDate, $endDate), $index + 1);
            $entry['running_balance'] = round($balance, 2);
            return $entry;
        });

        $totalIncome = round((float) $entries->where('type', 'income')->sum('amount'), 2);
        $totalExpense = round((float) $entries->where('type', 'expense')->sum('amount'), 2);
        $sourceTotals = $entries->groupBy('source_code')->map(function (Collection $rows) {
            return [
                'income' => round((float) $rows->where('type', 'income')->sum('amount'), 2),
                'expense' => round((float) $rows->where('type', 'expense')->sum('amount'), 2),
            ];
        });

        return [
            'entries' => $entries,
            'opening_balance' => round($openingBalance, 2),
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'closing_balance' => round($openingBalance + $totalIncome - $totalExpense, 2),
            'source_totals' => $sourceTotals,
            'period_start' => $startDate,
            'period_end' => $endDate,
        ];
    }

    /** Balance from prior financial entries, confirmed payments and founding contributions. */
    protected function balanceBefore(string $start): float
    {
        $validSourceFilter = function ($query) {
            $query->where(function ($sourceQuery) {
                $sourceQuery->where('source_type', '!=', Receipt::class)
                    ->orWhereExists(function ($receiptQuery) {
                        $receiptQuery->selectRaw('1')
                            ->from('receipts')
                            ->whereColumn('receipts.id', 'financial_entries.source_id')
                            ->where('receipts.status', 'confirmed')
                            // A confirmed migrated Payment replaces this legacy receipt entry.
                            ->whereNotExists(function ($paymentQuery) {
                                $paymentQuery->selectRaw('1')
                                    ->from('payments')
                                    ->whereColumn(
                                        'payments.receipt_reference',
                                        'receipts.reference'
                                    )
                                    ->where('payments.status', 'confirmed');
                            });
                    });
            });
        };

        $entryIncomeQuery = FinancialEntry::query()->where('type', 'income')->where('entry_date', '<', $start)
            ->where('source_type', '!=', Payment::class);
        $validSourceFilter($entryIncomeQuery);
        $entryIncome = (float) $entryIncomeQuery->sum('amount');

        $entryExpenseQuery = FinancialEntry::query()->where('type', 'expense')->where('entry_date', '<', $start)
            ->where('source_type', '!=', Payment::class);
        $validSourceFilter($entryExpenseQuery);
        $entryExpense = (float) $entryExpenseQuery->sum('amount');
        $paymentIncome = (float) Payment::query()->where('status', 'confirmed')->where('payment_date', '<', $start)->sum('amount');
        $foundingIncome = (float) FoundingCotisation::query()
            ->where(function ($query) use ($start) {
                $query->where('payment_date', '<', $start)
                    ->orWhere(function ($fallback) use ($start) {
                        $fallback->whereNull('payment_date')->whereDate('created_at', '<', $start);
                    });
            })->sum('amount');

        return round($entryIncome - $entryExpense + $paymentIncome + $foundingIncome, 2);
    }

    protected function periodReferencePrefix(Carbon $start, Carbon $end): string
    {
        if ($start->month === $end->month) {
            return sprintf('RF-%d-%02d', $start->year, $start->month);
        }

        $quarter = (int) ceil($start->month / 3);
        return sprintf('RF-%d-T%d', $start->year, $quarter);
    }


    protected function sourceCodeForEntry(FinancialEntry $entry): string
    {
        $sourceType = $entry->source_type;
        if (str_ends_with($sourceType, '\\Offrande')) return 'OFF';
        if (str_ends_with($sourceType, '\\Donation')) return 'DON';
        if (str_ends_with($sourceType, '\\Equipment')) return 'EQP';
        if (str_ends_with($sourceType, '\\PurchaseRequest')) {
            $purchaseRequest = $entry->source;
            return match ($purchaseRequest?->category) {
                'equipment' => 'EQP',
                'transport', 'train_ticket' => 'TRP',
                default => 'AUT',
            };
        }
        if (str_ends_with($sourceType, '\\PriestTransport')) return 'TRP';
        if (str_ends_with($sourceType, '\\PriestIndemnity')) return 'PRE';
        if (str_ends_with($sourceType, '\\ExceptionalDemand')) return 'EXC';

        return match ($entry->category) {
            'offrande' => 'OFF',
            'don' => 'DON',
            'achat', 'equipment' => 'EQP',
            'transport' => 'TRP',
            'indemnite' => 'PRE',
            'evenement', 'event' => 'EVT',
            'exceptionnel', 'autre' => 'EXC',
            default => 'AUT',
        };
    }

    protected function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'cash' => 'Espèces',
            'transfer', 'bank_transfer' => 'Virement bancaire',
            'check' => 'Chèque',
            'other' => 'Autre',
            default => 'Non renseigné',
        };
    }

    protected function headerData(Carbon $startDate, Carbon $endDate, string $reference, string $periodFr, string $periodTi): array
    {
        return [
            'doc_line' => self::DOC_LINE . ' — ' . $reference,
            'subtitle' => self::DESCRIPTION_FR . ' / ' . self::DESCRIPTION_TI . ' — '
                . $startDate->format('d/m/Y') . ' – ' . $endDate->format('d/m/Y'),
            'assoc_fr' => self::ASSOCIATION_FR,
            'assoc_ti' => self::ASSOCIATION_TI,
        ];
    }

    public function getSourceGuide(): array
    {
        return [
            'cot' => ['fr' => 'Cotisation mensuelle', 'ti' => 'ኣበርክቶ'],
            'off' => ['fr' => 'Offrande', 'ti' => 'ሞባእ'],
            'don' => ['fr' => 'Don', 'ti' => 'ልገሳ'],
            'eqp' => ['fr' => 'Équipement', 'ti' => 'መሳርሒታት'],
            'trp' => ['fr' => 'Transport', 'ti' => 'መጓዓዝያ ካህን'],
            'pre' => ['fr' => 'Indemnité', 'ti' => 'ክፍሊት ካህን'],
            'evt' => ['fr' => 'Événement', 'ti' => 'ኣጋጣሚታት'],
            'exc' => ['fr' => 'Exceptionnel', 'ti' => 'ሕቶታት ፍሉይ'],
        ];
    }
}
