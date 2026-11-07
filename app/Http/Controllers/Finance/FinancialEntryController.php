<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FinancialEntry;
use App\Models\FoundingCotisation;
use App\Services\FinancialRegisterGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class FinancialEntryController extends Controller
{
    protected FinancialRegisterGenerator $generator;

    public function __construct(FinancialRegisterGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function index(Request $request): View
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        // Generate the same aggregated rows used by the official register.
        $rows = $this->generator->generateAggregatedEntries($dateFrom, $dateTo);

        // Apply the page filters after aggregation.
        $rows = $rows
            ->filter(function (array $row) use ($request) {
                if ($request->filled('type') && $row['type'] !== $request->input('type')) {
                    return false;
                }

                if ($request->filled('category') && $row['category'] !== $request->input('category')) {
                    return false;
                }

                return true;
            })
            ->sortByDesc('entry_date')
            ->values();

        $summaryIncome = round((float) $rows->where('type', 'income')->sum('amount'), 2);
        $summaryExpense = round((float) $rows->where('type', 'expense')->sum('amount'), 2);
        $summaryCount = $rows->count();

        // Adapt generated rows to the properties expected by the existing Blade view.
        $displayRows = $rows->map(function (array $row) {
            return (object) [
                'reference' => $row['source_reference'],
                'entry_date' => \Carbon\Carbon::parse($row['entry_date']),
                'type' => $row['type'],
                'category' => $row['category'],
                'description' => $row['notes'] ?: $row['category_label'],
                'amount' => (float) $row['amount'],
                'is_income' => $row['type'] === 'income',
                'creator' => null,
                'source_type' => $row['source_code'] === 'COT'
                    ? 'Agrégation mensuelle'
                    : $row['source_code'],
            ];
        });

        $perPage = 30;
        $page = LengthAwarePaginator::resolveCurrentPage();

        $entries = new LengthAwarePaginator(
            $displayRows->forPage($page, $perPage)->values(),
            $displayRows->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $categories = [
            'cotisation', 'offrande', 'don', 'achat',
            'transport', 'indemnite', 'autre',
        ];

        return view('finance.financial_entries.index', compact(
            'entries',
            'categories',
            'summaryIncome',
            'summaryExpense',
            'summaryCount'
        ));
    }

    public function create(): View
    {
        return view('finance.financial_entries.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Manual entries not typically created directly - they come from other modules
        abort(403, __('app.manual_entry_not_allowed'));
    }

    public function show(FinancialEntry $financialEntry): View
    {
        return view('finance.financial_entries.show', ['entry' => $financialEntry]);
    }

    public function edit(FinancialEntry $financialEntry): View
    {
        abort_unless($financialEntry->source_type === 'manual', 403, __('app.auto_entry_not_editable'));
        return view('finance.financial_entries.edit', ['financialEntry' => $financialEntry]);
    }

    public function update(Request $request, FinancialEntry $financialEntry): RedirectResponse
    {
        abort_unless($financialEntry->source_type === 'manual', 403, __('app.auto_entry_not_editable'));
        // Update logic here if needed
        return redirect()->route('financial-entries.show', $financialEntry)->with('success', __('app.entry_updated'));
    }

    public function destroy(Request $request, FinancialEntry $financialEntry): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('finance.record'), 403);
        abort_unless($financialEntry->source_type === 'manual', 403, __('app.auto_entry_not_deletable'));

        $financialEntry->delete();
        return redirect()->route('financial-entries.index')->with('success', __('app.entry_deleted'));
    }

    public function report(Request $request): View
    {
        $dateFrom = $request->date('date_from') ?? now()->startOfYear();
        $dateTo = $request->date('date_to') ?? now()->endOfYear();

        $income = FinancialEntry::income()->forPeriod($dateFrom->toDateString(), $dateTo->toDateString());
        $expenses = FinancialEntry::expense()->forPeriod($dateFrom->toDateString(), $dateTo->toDateString());

        // Founding cotisations (200€) income within the period (payment_date, fallback created_at).
        $foundingRows = FoundingCotisation::all()
            ->map(fn (FoundingCotisation $f) => [
                'date' => ($f->payment_date ?? $f->created_at)->copy()->startOfDay(),
                'amount' => (float) $f->amount,
            ])
            ->filter(fn (array $f) => $f['date']->between($dateFrom->copy()->startOfDay(), $dateTo->copy()->endOfDay()))
            ->values();
        $foundingTotal = (float) $foundingRows->sum('amount');

        $totalIncome = (float) $income->sum('amount') + $foundingTotal;
        $totalExpense = (float) $expenses->sum('amount');

        $incomeByCategory = $income->selectRaw('category, sum(amount) as total')->groupBy('category')->pluck('total', 'category');
        if ($foundingTotal > 0) {
            $incomeByCategory->put('founding_cotisations', $foundingTotal);
        }
        $expenseByCategory = $expenses->selectRaw('category, sum(amount) as total')->groupBy('category')->pluck('total', 'category');

        $monthly = FinancialEntry::forPeriod($dateFrom->toDateString(), $dateTo->toDateString())
            ->get(['type', 'entry_date', 'amount'])
            ->concat($foundingRows->map(fn (array $f) => (object) [
                'type' => 'income',
                'entry_date' => $f['date'],
                'amount' => $f['amount'],
            ]))
            ->groupBy(fn ($entry) => $entry->entry_date->format('Y-m'))
            ->sortKeys()
            ->map(function ($entries, string $key): object {
                [$year, $month] = explode('-', $key);

                return (object) [
                    'year' => (int) $year,
                    'month' => (int) $month,
                    'income' => (float) $entries->where('type', 'income')->sum('amount'),
                    'expense' => (float) $entries->where('type', 'expense')->sum('amount'),
                ];
            })
            ->values();

        return view('finance.financial_entries.report', compact(
            'dateFrom', 'dateTo', 'totalIncome', 'totalExpense',
            'incomeByCategory', 'expenseByCategory', 'monthly'
        ));
    }
}