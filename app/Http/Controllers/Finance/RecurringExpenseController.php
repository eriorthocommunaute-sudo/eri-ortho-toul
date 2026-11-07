<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinancialEntry;
use App\Models\RecurringExpenseOccurrence;
use App\Models\RecurringExpenseTemplate;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RecurringExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $year = $request->integer('year') ?: now()->year;
        $month = $request->integer('month') ?: now()->month;

        $templates = RecurringExpenseTemplate::query()->orderBy('id')->get();
        $occurrences = RecurringExpenseOccurrence::query()
            ->with('template')
            ->where('period_year', $year)
            ->when($month >= 1 && $month <= 12, fn ($q) => $q->where('period_month', $month))
            ->orderBy('due_date')->get();

        return view('finance.recurring-expenses.index', compact('templates', 'occurrences', 'year', 'month'));
    }

    public function updateTemplate(Request $request, RecurringExpenseTemplate $template): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'due_day' => ['required', 'integer', 'between:1,28'],
            'beneficiary' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['nullable', Rule::in(['cash', 'transfer', 'check', 'other'])],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['updated_by'] = $request->user()->id;
        $template->update($data);

        return back()->with('success', 'Configuration mise à jour. Les obligations déjà générées restent inchangées.');
    }

    public function pay(Request $request, RecurringExpenseOccurrence $occurrence, ReferenceNumberService $references): RedirectResponse
    {
        $data = $request->validate([
            'paid_at' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'check', 'other'])],
            'payment_reference' => ['nullable', 'string', 'max:255'],
            'proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless(in_array($occurrence->status, ['pending', 'overdue'], true), 422, 'Cette obligation ne peut plus être payée.');

        DB::transaction(function () use ($request, $occurrence, $data, $references) {
            $locked = RecurringExpenseOccurrence::query()->lockForUpdate()->findOrFail($occurrence->id);
            abort_unless(in_array($locked->status, ['pending', 'overdue'], true), 422, 'Cette obligation a déjà été traitée.');

            $proofPath = $request->hasFile('proof')
                ? $request->file('proof')->store('finance/recurring-expenses', 'local')
                : null;

            $template = $locked->template;
            $paymentYear = \Carbon\Carbon::parse($data['paid_at'])->year;
            $financialReference = $references->next('RF', $paymentYear);
            $entry = FinancialEntry::create([
                'reference' => $financialReference,
                'entry_date' => $data['paid_at'],
                'type' => 'expense',
                'category' => $template->category,
                'source_type' => RecurringExpenseOccurrence::class,
                'source_id' => $locked->id,
                'description' => $template->name . ' - ' . sprintf('%02d/%04d', $locked->period_month, $locked->period_year),
                'amount' => $locked->amount_due,
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
                'updated_by' => $request->user()->id,
            ]);

            $locked->update([
                'amount_paid' => $locked->amount_due,
                'paid_at' => $data['paid_at'],
                'payment_method' => $data['payment_method'],
                'payment_reference' => $data['payment_reference'] ?? null,
                'proof_path' => $proofPath,
                'status' => 'paid',
                'financial_entry_id' => $entry->id,
                'paid_by' => $request->user()->id,
            ]);
        }, 3);

        return back()->with('success', 'Paiement enregistré et ajouté au registre financier.');
    }

    public function downloadProof(RecurringExpenseOccurrence $occurrence)
    {
        abort_unless($occurrence->proof_path && Storage::disk('local')->exists($occurrence->proof_path), 404);
        return Storage::disk('local')->download($occurrence->proof_path);
    }
}
