<?php

namespace App\Http\Controllers;

use App\Models\Contribution;
use App\Models\Event;
use App\Models\ExceptionalDemand;
use App\Models\FinancialEntry;
use App\Models\FoundingCotisation;
use App\Models\Member;
use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $currentYear = (int) now()->format('Y');
        $currentMonth = (int) now()->format('m');

        $totalMembers = Member::count();
        $activeMembers = Member::where('status', 'active')->count();

        $currentContribution = Contribution::forPeriod($currentYear, $currentMonth)->first();
        $contributionProgress = $currentContribution?->progress_percent ?? 0;
        $contributionReceived = (float) ($currentContribution?->total_received ?? 0);
        $contributionExpected = (float) ($currentContribution?->total_expected ?? 0);

        $monthlyIncome = (float) FinancialEntry::income()
            ->forPeriod(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString())
            ->sum('amount');
        $monthlyExpense = (float) FinancialEntry::expense()
            ->forPeriod(now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString())
            ->sum('amount');

        $allTimeIncome = (float) FinancialEntry::income()->sum('amount');
        $allTimeExpense = (float) FinancialEntry::expense()->sum('amount');

        $foundingCotisationsIncome = (float) FoundingCotisation::sum('amount');
        $totalIncome = $allTimeIncome + $foundingCotisationsIncome;

        $pendingPurchaseRequests = PurchaseRequest::where('status', 'submitted')->count();
        $pendingExceptionalDemands = ExceptionalDemand::whereIn('status', ['submitted', 'under_review'])->count();
        $pendingApprovals = $pendingPurchaseRequests + $pendingExceptionalDemands;

        $upcomingCount = Event::upcoming()
            ->where('starts_at', '<=', now()->addDays(30))
            ->count();

        $chartStart = now()->startOfMonth()->subMonths(5);
        $chartEntries = FinancialEntry::query()
            ->whereBetween('entry_date', [$chartStart->toDateString(), now()->endOfMonth()->toDateString()])
            ->get(['entry_date', 'type', 'amount']);

        $chartSeries = collect(range(0, 5))->map(function (int $offset) use ($chartStart, $chartEntries): array {
            $month = $chartStart->copy()->addMonths($offset);
            $key = $month->format('Y-m');
            $monthEntries = $chartEntries->filter(fn (FinancialEntry $entry) => $entry->entry_date->format('Y-m') === $key);

            return [
                'label' => __('app.months_short.'.((int) $month->format('n') - 1)),
                'income' => (float) $monthEntries->where('type', 'income')->sum('amount'),
                'expense' => (float) $monthEntries->where('type', 'expense')->sum('amount'),
            ];
        });

        $chartIncomeTotal = (float) $chartSeries->sum('income');
        $chartExpenseTotal = (float) $chartSeries->sum('expense');
        $chartMax = max((float) $chartSeries->max('income'), (float) $chartSeries->max('expense'), 1);

        $recentEntries = FinancialEntry::with('creator')
            ->latest('entry_date')
            ->latest('id')
            ->limit(8)
            ->get();

        return view('dashboard.index', compact(
            'totalMembers',
            'activeMembers',
            'currentContribution',
            'contributionProgress',
            'contributionReceived',
            'contributionExpected',
            'monthlyIncome',
            'monthlyExpense',
            'allTimeIncome',
            'allTimeExpense',
            'foundingCotisationsIncome',
            'totalIncome',
            'pendingApprovals',
            'upcomingCount',
            'chartSeries',
            'chartIncomeTotal',
            'chartExpenseTotal',
            'chartMax',
            'recentEntries'
        ));
    }
}
