<?php

namespace App\Http\Controllers;

use App\Models\FinancialEntry;
use App\Models\FoundingCotisation;
use App\Models\Member;
use App\Services\PdfExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class PdfExportController extends Controller
{
    public function index(PdfExportService $pdf): View
    {
        return view('pdf.index', ['templates' => $pdf->getTemplates()]);
    }

    public function printView(Request $request, string $template, $id = null): View
    {
        $viewMap = [
            'member_list' => 'members.print',
            'receipt' => 'finance.receipts.print',
            'contribution_sheet' => 'finance.contributions.show',
            'financial_report' => 'finance.financial_entries.report',
            'purchase_request' => 'purchases.requests.show',
            'exceptional_demand' => 'purchases.exceptional_demands.show',
            'meeting_minute' => 'secretary.minutes.show',
            'equipment_sheet' => 'logistics.equipment.show',
            'transport_sheet' => 'logistics.priest_transports.show',
            'indemnity_sheet' => 'logistics.priest_indemnities.show',
            'event_sheet' => 'events.show',
            'offrande_sheet' => 'finance.offrandes.show',
            'donation_sheet' => 'finance.donations.show',
        ];

        $view = $viewMap[$template] ?? 'members.print';
        $modelClass = $this->getModelClass($template);

        if ($id && $modelClass) {
            $model = $modelClass::findOrFail($id);
            return view($view, [$this->getVariableName($template) => $model]);
        }

        if ($template === 'member_list') {
            $members = Member::orderBy('family_name')->orderBy('given_name')->get();
            return view($view, compact('members'));
        }

        if ($template === 'financial_report') {
            $dateFrom = Carbon::parse($request->input('date_from', now()->startOfYear()->toDateString()))->startOfDay();
            $dateTo = Carbon::parse($request->input('date_to', now()->endOfYear()->toDateString()))->endOfDay();
            $entries = FinancialEntry::forPeriod($dateFrom->toDateString(), $dateTo->toDateString())->get();

            // Founding cotisations (200€) income within the period.
            $foundingRows = FoundingCotisation::all()
                ->map(fn (FoundingCotisation $f) => [
                    'date' => ($f->payment_date ?? $f->created_at)->copy()->startOfDay(),
                    'amount' => (float) $f->amount,
                ])
                ->filter(fn (array $f) => $f['date']->between($dateFrom, $dateTo))
                ->values();
            $foundingTotal = (float) $foundingRows->sum('amount');

            $totalIncome = $entries->where('type', 'income')->sum('amount') + $foundingTotal;
            $totalExpense = $entries->where('type', 'expense')->sum('amount');
            $balance = $totalIncome - $totalExpense;
            $incomeByCategory = $entries->where('type', 'income')
                ->groupBy('category')
                ->map(fn ($group) => $group->sum('amount'));
            if ($foundingTotal > 0) {
                $incomeByCategory->put('founding_cotisations', $foundingTotal);
            }
            $expenseByCategory = $entries->where('type', 'expense')
                ->groupBy('category')
                ->map(fn ($group) => $group->sum('amount'));
            $monthly = $entries
                ->concat($foundingRows->map(fn (array $f) => (object) [
                    'type' => 'income',
                    'entry_date' => $f['date'],
                    'amount' => $f['amount'],
                ]))
                ->groupBy(fn ($e) => Carbon::parse($e->entry_date)->format('Y-m'))
                ->sortKeys()
                ->map(function ($group, $key) {
                    [$year, $month] = explode('-', $key);
                    return (object) [
                        'year' => (int) $year,
                        'month' => (int) $month,
                        'income' => $group->where('type', 'income')->sum('amount'),
                        'expense' => $group->where('type', 'expense')->sum('amount'),
                    ];
                })
                ->values();
            return view($view, compact('dateFrom', 'dateTo', 'totalIncome', 'totalExpense', 'balance', 'incomeByCategory', 'expenseByCategory', 'monthly'));
        }

        abort(404);
    }

    private function getModelClass(string $template): ?string
    {
        return match ($template) {
            'receipt' => \App\Models\Receipt::class,
            'contribution_sheet' => \App\Models\Contribution::class,
            'purchase_request' => \App\Models\PurchaseRequest::class,
            'exceptional_demand' => \App\Models\ExceptionalDemand::class,
            'meeting_minute' => \App\Models\MeetingMinute::class,
            'equipment_sheet' => \App\Models\Equipment::class,
            'transport_sheet' => \App\Models\PriestTransport::class,
            'indemnity_sheet' => \App\Models\PriestIndemnity::class,
            'event_sheet' => \App\Models\Event::class,
            'offrande_sheet' => \App\Models\Offrande::class,
            'donation_sheet' => \App\Models\Donation::class,
            default => null,
        };
    }

    private function getVariableName(string $template): string
    {
        return match ($template) {
            'receipt' => 'receipt',
            'contribution_sheet' => 'contribution',
            'purchase_request' => 'purchaseRequest',
            'exceptional_demand' => 'demand',
            'meeting_minute' => 'minute',
            'equipment_sheet' => 'equipment',
            'transport_sheet' => 'transport',
            'indemnity_sheet' => 'indemnity',
            'event_sheet' => 'event',
            'offrande_sheet' => 'offrande',
            'donation_sheet' => 'donation',
            default => 'model',
        };
    }
}