<?php

namespace App\Services;

use App\Models\ReferenceSequence;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReferenceNumberService
{
    /** @var array<string, string> */
    protected array $formats = [
        'MEM' => '{PREFIX}-{YEAR}-{NUMBER:03d}',
        'PAY' => '{PREFIX}-{YEAR}-{NUMBER:06d}',
        'RF'  => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'COT' => '{PREFIX}-{YEAR}-{MONTH:02d}',
        'REC' => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'AVI' => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'OFF' => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'DON' => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'EQP' => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'TRP' => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'PRE' => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'EVT' => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'DA'  => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'EXC' => '{PREFIX}-{YEAR}-{NUMBER:04d}',
        'PV'  => '{PREFIX}-{YEAR}-{NUMBER:04d}',
    ];

    /**
     * Tables holding already-issued references per prefix. Used to repair a
     * sequence that is missing or behind the highest number already in use.
     *
     * @var array<string, list<array{0: string, 1: string}>>
     */
    protected array $sources = [
        'MEM' => [['members', 'member_code']],
        'PAY' => [['payments', 'payment_reference']],
        'REC' => [['receipts', 'reference']],
        'AVI' => [['contribution_notices', 'reference']],
        'OFF' => [['offrandes', 'reference']],
        'DON' => [['donations', 'reference']],
        'EVT' => [['events', 'reference']],
        'DA'  => [['purchase_requests', 'reference']],
        'EQP' => [['equipment', 'reference']],
        'EXC' => [['exceptional_demands', 'reference']],
        'PV'  => [['meeting_minutes', 'reference']],
        'TRP' => [['priest_transports', 'reference']],
        'PRE' => [['priest_indemnities', 'reference']],
        'RF'  => [
            ['financial_entries', 'reference'],
            ['receipts', 'financial_reference'],
            ['offrandes', 'financial_reference'],
            ['donations', 'financial_reference'],
            ['events', 'financial_reference'],
            ['equipment', 'financial_reference'],
            ['purchase_requests', 'financial_reference'],
            ['exceptional_demands', 'financial_reference'],
            ['priest_transports', 'financial_reference'],
            ['priest_indemnities', 'financial_reference'],
        ],
    ];

    public function next(string $prefix, ?int $year = null, ?int $month = null): string
    {
        $prefix = strtoupper(trim($prefix));
        if (! preg_match('/^[A-Z0-9]{2,12}$/', $prefix)) {
            throw new \InvalidArgumentException('Invalid reference prefix.');
        }
        $year ??= (int) now()->format('Y');

        for ($attempt = 0; $attempt < 3; $attempt++) {
            try {
                return DB::transaction(function () use ($prefix, $year, $month) {
                    $sequence = ReferenceSequence::where('prefix', $prefix)
                        ->where('sequence_year', $year)
                        ->lockForUpdate()
                        ->first();

                    $existingMax = $this->existingMaxNumber($prefix, $year);

                    if (! $sequence) {
                        $sequence = ReferenceSequence::create([
                            'prefix' => $prefix,
                            'sequence_year' => $year,
                            'last_number' => $existingMax,
                        ]);
                        $sequence = ReferenceSequence::whereKey($sequence->id)->lockForUpdate()->firstOrFail();
                    } elseif ($existingMax > (int) $sequence->last_number) {
                        $sequence->update(['last_number' => $existingMax]);
                    }

                    $sequence->increment('last_number');
                    $number = $sequence->fresh()->last_number;

                    if ($prefix === 'COT' && $month) {
                        return sprintf('%s-%d-%02d', $prefix, $year, $month);
                    }

                    $digits = $this->digitsFor($prefix);

                    return sprintf('%s-%d-%s', $prefix, $year, str_pad((string) $number, $digits, '0', STR_PAD_LEFT));
                }, 3);
            } catch (QueryException $exception) {
                if ($attempt === 2) {
                    throw $exception;
                }
            }
        }
    }

    /**
     * Highest number already issued for the prefix/year across its source tables.
     */
    protected function existingMaxNumber(string $prefix, int $year): int
    {
        $max = 0;

        foreach ($this->sources[$prefix] ?? [] as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            $codes = DB::table($table)
                ->where($column, 'like', sprintf('%s-%d-%%', $prefix, $year))
                ->pluck($column);

            foreach ($codes as $code) {
                if (preg_match('/-(\d+)$/', (string) $code, $matches)) {
                    $max = max($max, (int) $matches[1]);
                }
            }
        }

        return $max;
    }

    public function nextCot(int $year, int $month): string
    {
        return $this->next('COT', $year, $month);
    }

    public function getFormat(string $prefix): string
    {
        return $this->formats[$prefix] ?? '{PREFIX}-{YEAR}-{NUMBER:04d}';
    }

    /**
     * Zero-padding width declared in the prefix format (e.g. :03d -> 3).
     */
    protected function digitsFor(string $prefix): int
    {
        if (preg_match('/\{NUMBER:(\d+)d\}/', $this->getFormat($prefix), $matches)) {
            return (int) $matches[1];
        }

        return 4;
    }

    public function getNextNumber(string $prefix, ?int $year = null): int
    {
        $year ??= (int) now()->format('Y');
        $sequence = ReferenceSequence::where('prefix', $prefix)->where('sequence_year', $year)->first();
        return ($sequence?->last_number ?? 0) + 1;
    }
}