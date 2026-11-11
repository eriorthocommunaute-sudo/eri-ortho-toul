<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rewrite purchase-order references from the old yearly DA-YYYY-NNNN
     * format to the month-scoped DA-YYYY-MM-NNN format, taking the month
     * from the date the order was placed and numbering per month in
     * registration order.
     */
    public function up(): void
    {
        $renames = [];
        $counters = [];

        $rows = DB::table('purchase_requests')
            ->orderBy('id')
            ->get(['id', 'reference', 'request_date', 'created_at']);

        foreach ($rows as $row) {
            if (! preg_match('/^DA-\d{4}-\d{4}$/', (string) $row->reference)) {
                continue;
            }

            $placedOn = $this->placedOn($row->request_date, $row->created_at);
            $key = $placedOn->format('Y-m');
            $counters[$key] = ($counters[$key] ?? 0) + 1;

            $renames[$row->reference] = sprintf(
                'DA-%s-%03d',
                $key,
                $counters[$key]
            );
        }

        foreach ($renames as $old => $new) {
            DB::table('purchase_requests')
                ->where('reference', $old)
                ->update(['reference' => $new]);

            DB::table('equipment')
                ->where('purchase_reference', $old)
                ->update(['purchase_reference' => $new]);
        }
    }

    /**
     * The mapping is not reversible: the original yearly numbers cannot be
     * rebuilt from the per-month ranks, so down() intentionally does nothing.
     */
    public function down(): void {}

    protected function placedOn(?string $requestDate, string $createdAt): Carbon
    {
        if ($requestDate !== null && $requestDate !== '') {
            try {
                return Carbon::createFromFormat('d/m/Y', $requestDate);
            } catch (Throwable) {
                // Fall back to when the record was created.
            }
        }

        return Carbon::parse($createdAt);
    }
};
