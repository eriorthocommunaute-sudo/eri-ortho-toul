<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Http\Requests\Secretary\MeetingMinuteRequest;
use App\Models\MeetingMinute;
use App\Models\Member;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeetingMinuteController extends Controller
{
    public function index(Request $request): View
    {
        $minutes = MeetingMinute::query()
            ->with('secretary', 'president')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('meeting_date')
            ->paginate(20)
            ->withQueryString();

        $types = ['board', 'general_assembly', 'bureau', 'committee', 'other'];
        $statuses = ['draft', 'approved', 'archived'];

        return view('secretary.minutes.index', compact('minutes', 'types', 'statuses'));
    }

    public function create(): View
    {
        $members = Member::where('status', 'active')->orderBy('family_name')->get();
        return view('secretary.minutes.create', compact('members'));
    }

    public function store(MeetingMinuteRequest $request, ReferenceNumberService $references): RedirectResponse
    {
        $data = $request->validated();
        $data['reference'] = $references->next('PV');
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $minute = MeetingMinute::create($data);

        return redirect()->route('meeting-minutes.show', $minute)->with('success', __('app.minute_created'));
    }

    public function show(MeetingMinute $minute): View
    {
        $minute->load('secretary', 'president');
        return view('secretary.minutes.show', compact('minute'));
    }

    public function edit(MeetingMinute $minute): View
    {
        abort_unless($minute->status === 'draft', 403, __('app.minute_not_editable'));

        $members = Member::where('status', 'active')->orderBy('family_name')->get();
        return view('secretary.minutes.edit', compact('minute', 'members'));
    }

    public function update(MeetingMinuteRequest $request, MeetingMinute $minute): RedirectResponse
    {
        abort_unless($minute->status === 'draft', 403, __('app.minute_not_editable'));

        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $minute->update($data);

        return redirect()->route('meeting-minutes.show', $minute)->with('success', __('app.minute_updated'));
    }

    public function approve(Request $request, MeetingMinute $minute): RedirectResponse
    {
        abort_unless($minute->status === 'draft', 403, __('app.minute_not_approvable'));

        $minute->update([
            'status' => 'approved',
            'president_id' => $request->user()->id,
            'approved_at' => now(),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('meeting-minutes.show', $minute)->with('success', __('app.minute_approved'));
    }

    public function destroy(Request $request, MeetingMinute $minute): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('secretary.manage'), 403);
        abort_unless($minute->status === 'draft', 403);

        $minute->delete();

        return redirect()->route('meeting-minutes.index')->with('success', __('app.minute_deleted'));
    }
}