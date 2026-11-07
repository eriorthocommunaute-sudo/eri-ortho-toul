<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use App\Models\AuditLog;
use App\Models\ContributionNotice;
use App\Models\Member;
use App\Services\MemberFinancialHistory;
use App\Services\ReferenceNumberService;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class MemberController extends Controller
{
    public function index(Request $request): View
    {
        $members = Member::query()->search($request->string('q')->toString())->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))->orderByReference()->paginate(20)->withQueryString();

        return view('members.index', compact('members'));
    }

    public function create(): View
    {
        abort_unless(request()->user()->hasPermission('members.create'), 403);

        return view('members.form', ['member' => new Member]);
    }

    public function store(MemberRequest $request, ReferenceNumberService $references): RedirectResponse
    {
        $member = Member::create([...$request->validated(), 'member_code' => $references->next('MEM'), 'created_by' => $request->user()->id, 'updated_by' => $request->user()->id]);
        $this->audit($request, 'created', $member, null, $member->getAttributes());

        return redirect()->route('members.show', $member)->with('success', __('app.member_created'));
    }

    public function show(Member $member): View
    {
        $history = new MemberFinancialHistory($member);
        $monthlyHistory = $history->getMonthlyHistory()->take(12);
        $summary = $history->getSummaryCards();
        $donationHistory = $history->getDonationHistory()->take(10);
        $donationSummary = $history->getDonationSummary();

        return view('members.show', compact('member', 'monthlyHistory', 'summary', 'donationHistory', 'donationSummary'));
    }

    public function financialHistory(Request $request, Member $member): View
    {
        $year = $request->filled('year') ? $request->integer('year') : 2026; // Default to 2026
        $selectedStatus = $request->filled('status') ? $request->string('status') : null;
        $donationYear = $request->filled('donation_year') ? $request->integer('donation_year') : now()->year;

        $history = new MemberFinancialHistory($member, $year, $selectedStatus, $donationYear);
        $monthlyHistory = $history->getMonthlyHistory();
        $summary = $history->getSummaryCards();
        $donationHistory = $history->getDonationHistory();
        $donationSummary = $history->getDonationSummary();
        $cotisationYears = $history->getCotisationAvailableYears();
        $donationYears = $history->getDonationAvailableYears();

        return view('members.financial-history', compact(
            'member',
            'monthlyHistory',
            'summary',
            'donationHistory',
            'donationSummary',
            'cotisationYears',
            'donationYears',
            'year',
            'selectedStatus',
            'donationYear'
        ));
    }

    public function exportFinancialHistory(Request $request, Member $member): Response
    {
        $year = $request->filled('year') ? $request->integer('year') : null;
        $status = $request->filled('status') ? $request->string('status') : null;

        $history = new MemberFinancialHistory($member, $year, $status);
        $csv = $history->exportToCsv();

        $filename = sprintf(
            'historique-financier-%s-%s.csv',
            $member->member_code,
            $year ?? 'all'
        );

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    public function edit(Member $member): View
    {
        abort_unless(request()->user()->hasPermission('members.update'), 403);

        return view('members.form', compact('member'));
    }

    public function update(MemberRequest $request, Member $member): RedirectResponse
    {
        $before = $member->getOriginal();
        $member->update([...$request->validated(), 'updated_by' => $request->user()->id]);
        $this->audit($request, 'updated', $member, $before, $member->fresh()->getAttributes());

        return redirect()->route('members.show', $member)->with('success', __('app.member_updated'));
    }

    public function destroy(Request $request, Member $member): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('members.delete'), 403);

        $hasReceipts = $member->receipts()->exists();
        $hasNotices = ContributionNotice::where('member_id', $member->id)->exists();

        if ($hasReceipts || $hasNotices) {
            $member->update([
                'status' => 'former',
                'restricted_notes' => trim(($member->restricted_notes ? $member->restricted_notes . PHP_EOL : '') . '[' . now()->format('d/m/Y') . '] ' . __('app.member_archived_note')),
                'updated_by' => $request->user()->id,
            ]);

            return redirect()->route('members.index')->with('success', __('app.member_archived_instead'));
        }

        $before = $member->getAttributes();

        try {
            $member->delete();
        } catch (QueryException) {
            $member->update([
                'status' => 'former',
                'updated_by' => $request->user()->id,
            ]);

            return redirect()->route('members.index')->with('success', __('app.member_archived_instead'));
        }

        $this->audit($request, 'deleted', $member, $before, null);

        return redirect()->route('members.index')->with('success', __('app.member_deleted'));
    }

    private function audit(Request $request, string $action, Member $member, ?array $before, ?array $after): void
    {
        AuditLog::create(['actor_id' => $request->user()->id, 'action' => "member.{$action}", 'subject_type' => Member::class, 'subject_id' => $member->id, 'before_values' => $before, 'after_values' => $after, 'occurred_at' => now()]);
    }

    public function print(Request $request): View
    {
        $members = Member::query()->search($request->string('q')->toString())->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))->orderByReference()->get();

        return view('members.print', compact('members'));
    }
}
