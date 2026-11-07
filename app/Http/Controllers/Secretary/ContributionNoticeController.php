<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Http\Requests\Secretary\ContributionNoticeRequest;
use App\Models\Contribution;
use App\Models\ContributionNotice;
use App\Models\Member;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContributionNoticeController extends Controller
{
    public function index(Request $request): View
    {
        $notices = ContributionNotice::query()
            ->with('member', 'contribution')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('member_id'), fn ($q) => $q->where('member_id', $request->integer('member_id')))
            ->when($request->filled('contribution_id'), fn ($q) => $q->where('contribution_id', $request->integer('contribution_id')))
            ->orderByDesc('notice_date')
            ->paginate(20)
            ->withQueryString();

        $members = Member::where('status', 'active')->orderBy('family_name')->get();
        $contributions = Contribution::open()->orderByDesc('year')->orderByDesc('month')->get();

        return view('secretary.notices.index', compact('notices', 'members', 'contributions'));
    }

    public function show(ContributionNotice $notice): View
    {
        $notice->load('member', 'contribution');
        return view('secretary.notices.show', compact('notice'));
    }

    public function generate(Request $request, Contribution $contribution): RedirectResponse
    {
        $unpaidMembers = Member::where('status', 'active')
            ->whereDoesntHave('receipts', fn ($q) => $q->where('contribution_id', $contribution->id)->where('status', 'confirmed'))
            ->get();

        if ($unpaidMembers->isEmpty()) {
            return back()->with('error', __('app.all_paid'));
        }

        $generated = 0;
        foreach ($unpaidMembers as $member) {
            $existing = ContributionNotice::where('member_id', $member->id)
                ->where('contribution_id', $contribution->id)
                ->first();

            if (!$existing) {
                ContributionNotice::create([
                    'reference' => app(ReferenceNumberService::class)->next('AVI'),
                    'member_id' => $member->id,
                    'contribution_id' => $contribution->id,
                    'notice_date' => now()->toDateString(),
                    'due_date' => now()->addDays(15)->toDateString(),
                    'amount' => $contribution->amount_per_member,
                    'status' => 'generated',
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ]);
                $generated++;
            }
        }

        return back()->with('success', __('app.notices_generated', ['count' => $generated]));
    }

    public function send(Request $request, ContributionNotice $notice): RedirectResponse
    {
        if ($notice->status === 'sent') {
            return back()->with('error', __('app.already_sent'));
        }

        $notice->update([
            'status' => 'sent',
            'sent_date' => now()->toDateString(),
            'updated_by' => $request->user()->id,
        ]);

        return back()->with('success', __('app.notice_sent'));
    }
}