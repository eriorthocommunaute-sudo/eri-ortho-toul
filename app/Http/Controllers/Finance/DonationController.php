<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\DonationRequest;
use App\Models\Donation;
use App\Services\ReferenceNumberService;
use App\Services\IncomeReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DonationController extends Controller
{
    public function index(Request $request): View
    {
        $donations = Donation::query()
            ->when($request->filled('donor_name'), fn ($q) => $q->where('donor_name', 'like', "%{$request->string('donor_name')}%"))
            ->when($request->filled('year'), fn ($q) => $q->whereYear('donation_date', $request->integer('year')))
            ->orderByDesc('donation_date')
            ->paginate(20)
            ->withQueryString();

        $years = range((int) now()->format('Y'), 2020);

        return view('finance.donations.index', compact('donations', 'years'));
    }

    public function create(): View
    {
        return view('finance.donations.create');
    }

    public function store(DonationRequest $request, ReferenceNumberService $references, IncomeReceiptService $receipts): RedirectResponse
    {
        $data = $request->validated();
        $data['reference'] = $references->next('DON');
        $data['financial_reference'] = $references->next('RF');
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        DB::transaction(function () use ($data, $receipts) {
            $donation = Donation::create($data);

            $purpose = $data['purpose'] ?? 'Libre';

            \App\Models\FinancialEntry::create([
                'reference' => $data['financial_reference'],
                'entry_date' => $data['donation_date'],
                'type' => 'income',
                'category' => 'don',
                'source_type' => Donation::class,
                'source_id' => $donation->id,
                'description' => "Don: {$data['donor_name']} - {$purpose}",
                'amount' => $data['amount'],
                'notes' => $data['notes'],
                'created_by' => $data['created_by'],
                'updated_by' => $data['updated_by'],
            ]);

            // Receipt only references this donation and its existing RF entry.
            $receipts->sync($donation);
        });

        return redirect()->route('donations.index')->with('success', __('app.donation_created'));
    }

    public function show(Donation $donation): View
    {
        return view('finance.donations.show', compact('donation'));
    }

    public function edit(Donation $donation): View
    {
        return view('finance.donations.edit', compact('donation'));
    }

    public function update(DonationRequest $request, Donation $donation, IncomeReceiptService $receipts): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        DB::transaction(function () use ($donation, $data, $receipts) {
            $donation->update($data);

        $purpose = $data['purpose'] ?? 'Libre';

        \App\Models\FinancialEntry::where('source_type', Donation::class)
            ->where('source_id', $donation->id)
            ->update([
                'amount' => $data['amount'],
                'description' => "Don: {$data['donor_name']} - {$purpose}",
                'notes' => $data['notes'],
            ]);
            $receipts->sync($donation->fresh());
        });

        return redirect()->route('donations.show', $donation)->with('success', __('app.donation_updated'));
    }

    public function destroy(Request $request, Donation $donation): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('finance.record'), 403);

        \App\Models\FinancialEntry::where('source_type', Donation::class)
            ->where('source_id', $donation->id)
            ->delete();

        $donation->delete();

        return redirect()->route('donations.index')->with('success', __('app.donation_deleted'));
    }
}