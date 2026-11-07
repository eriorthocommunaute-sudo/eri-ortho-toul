<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Models\FoundingCotisation;
use App\Models\Offrande;
use App\Models\Payment;
use App\Services\IncomeReceiptService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillIncomeReceipts extends Command
{
    protected $signature = 'receipts:backfill-income';
    protected $description = 'Create missing general receipts for existing income sources without creating financial entries.';

    public function handle(IncomeReceiptService $receipts): int
    {
        $created = 0;

        foreach ([Payment::class, Donation::class, Offrande::class, FoundingCotisation::class] as $modelClass) {
            $modelClass::query()->orderBy('id')->chunkById(100, function ($records) use ($receipts, &$created) {
                foreach ($records as $record) {
                    if ($record instanceof FoundingCotisation && (float) $record->amount <= 0) {
                        continue;
                    }

                    $before = \App\Models\Receipt::where('source_type', $record::class)
                        ->where('source_id', $record->id)
                        ->exists();
                    if (! $before && $record instanceof Payment) {
                        $before = \App\Models\Receipt::where('reference', $record->receipt_reference)->exists();
                    }

                    DB::transaction(fn () => $receipts->sync($record));
                    if (! $before) {
                        $created++;
                    }
                }
            });
        }

        $this->info("Traitement terminé. {$created} reçu(s) ajouté(s), aucune écriture financière créée.");
        return self::SUCCESS;
    }
}
