<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\FoundingCotisation;
use App\Models\Offrande;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Database\Eloquent\Model;

class IncomeReceiptService
{
    public function __construct(private readonly ReferenceNumberService $references)
    {
    }

    /** Create or update the receipt attached to an existing income source. */
    public function sync(Model $source, ?string $receiptReference = null): Receipt
    {
        [$type, $date, $payer, $description, $method, $financialReference, $memberId] = $this->details($source);

        $receipt = Receipt::query()
            ->where('source_type', $source::class)
            ->where('source_id', $source->getKey())
            ->first();

        // Reuse the receipt already created by the legacy payment workflow.
        if (! $receipt && $source instanceof Payment) {
            $receipt = Receipt::where('reference', $source->receipt_reference)->first();
        }

        $generatedReference = $receiptReference
            ?: ($source instanceof Payment ? $source->receipt_reference : null)
            ?: $this->references->next('REC');

        $receipt ??= new Receipt([
            'reference' => $generatedReference,
            'source_type' => $source::class,
            'source_id' => $source->getKey(),
            'receipt_type' => $type,
        ]);

        $receipt->fill([
            'source_type' => $source::class,
            'source_id' => $source->getKey(),
            'receipt_type' => $type,
            'member_id' => $memberId,
            'payer_name' => $payer,
            'description' => $description,
            'payment_date' => $date,
            'amount' => $source->amount,
            'payment_method' => $method,
            'financial_reference' => $financialReference,
            'status' => $this->receiptStatus($source),
            'notes' => $source->notes,
            'confirmed_at' => $this->receiptStatus($source) === 'confirmed' ? ($receipt->confirmed_at ?: now()) : null,
            'confirmed_by' => $this->receiptStatus($source) === 'confirmed' ? ($receipt->confirmed_by ?: $source->created_by) : null,
            'created_by' => $receipt->exists ? $receipt->created_by : $source->created_by,
            'updated_by' => $source->updated_by,
        ]);
        $receipt->save();

        return $receipt;
    }

    private function details(Model $source): array
    {
        if ($source instanceof Payment) {
            $source->loadMissing('member');
            $member = $source->member;
            $months = $source->allocations()->with('memberContribution')->get()
                ->map(fn ($allocation) => sprintf('%02d/%04d', $allocation->memberContribution->period_month, $allocation->memberContribution->period_year))
                ->implode(', ');

            return [
                'cotisation', $source->payment_date, trim(($member?->given_name ?? '') . ' ' . ($member?->family_name ?? '')),
                'Cotisation' . ($months !== '' ? ' , mois couverts : ' . $months : ''),
                $source->payment_method === 'bank_transfer' ? 'transfer' : $source->payment_method,
                $source->financialEntry()->value('reference')
                    ?? Receipt::where('reference', $source->receipt_reference)->value('financial_reference')
                    ?? '',
                $source->member_id,
            ];
        }

        if ($source instanceof Donation) {
            return ['don', $source->donation_date, $source->donor_name,
                'Don' . ($source->purpose ? ' , ' . $source->purpose : ''), 'cash', $source->financial_reference, null];
        }

        if ($source instanceof Offrande) {
            $source->loadMissing('member');
            $payer = $source->member
                ? trim($source->member->given_name . ' ' . $source->member->family_name)
                : ($source->donor_name ?: 'Anonyme');

            return ['offrande', $source->offering_date, $payer,
                'Offrande' . ($source->occasion ? ' , ' . $source->occasion : ''),
                $source->payment_method ?: 'cash', $source->financial_reference, $source->member_id];
        }

        if ($source instanceof FoundingCotisation) {
            $source->loadMissing('member');
            $member = $source->member;
            $referenceYear = ($source->payment_date ?? $source->created_at)?->format('Y') ?? now()->format('Y');

            return [
                'cotisation_fondatrice', $source->payment_date ?? $source->created_at,
                trim(($member?->given_name ?? '') . ' ' . ($member?->family_name ?? '')),
                'Cotisation fondatrice (200 €)',
                $source->payment_method ?: 'cash',
                sprintf('FCO-%s-%03d', $referenceYear, $source->getKey()),
                $source->member_id,
            ];
        }

        throw new \InvalidArgumentException('Unsupported income source for receipt generation.');
    }

    private function receiptStatus(Model $source): string
    {
        if ($source instanceof FoundingCotisation && (float) $source->amount <= 0) {
            return 'cancelled';
        }

        return match ($source->status ?? 'confirmed') {
            'cancelled' => 'cancelled',
            'pending' => 'draft',
            default => 'confirmed',
        };
    }
}
