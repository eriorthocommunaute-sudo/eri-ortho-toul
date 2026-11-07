<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\FoundingCotisation;
use App\Models\FoundingCotisationHistory;
use App\Models\Member;
use App\Services\IncomeReceiptService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FoundingCotisationController extends Controller
{
    public function index(Request $request): View
    {
        $foundingCotisations = FoundingCotisation::with('member')
            ->join('members', 'members.id', '=', 'founding_cotisations.member_id')
            ->select('founding_cotisations.*')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->whereHas('member', fn ($q) => $q->search($search->toString()));
            })
            ->orderBy('members.member_code')
            ->paginate(20)
            ->withQueryString();

        return view('finance.founding-cotisations.index', compact('foundingCotisations'));
    }

    public function create(): View
    {
        $members = Member::where('status', 'active')
            ->whereDoesntHave('foundingCotisations')
            ->orderBy('member_code')
            ->get();

        return view('finance.founding-cotisations.create', compact('members'));
    }

    public function store(Request $request, IncomeReceiptService $receipts): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0|max:200',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'payment_date' => 'nullable|date',
        ]);

        $validated['remaining'] = 200 - $validated['amount'];
        $validated['created_by'] = $request->user()->id;
        $validated['updated_by'] = $request->user()->id;

        DB::transaction(function () use ($validated, $receipts) {
            $foundingCotisation = FoundingCotisation::create($validated);
            if ((float) $foundingCotisation->amount > 0) {
                $receipts->sync($foundingCotisation);
            }
        });

        return redirect()->route('founding-cotisations.index')->with('success', __('app.founding_cotisation_created'));
    }

    public function edit(FoundingCotisation $foundingCotisation): View
    {
        $foundingCotisation->load('histories.user');

        $members = Member::where('status', 'active')
            ->whereDoesntHave('foundingCotisations', fn ($q) => $q->where('id', '!=', $foundingCotisation->id))
            ->orderBy('member_code')
            ->get();

        return view('finance.founding-cotisations.edit', compact('foundingCotisation', 'members'));
    }

    public function update(Request $request, FoundingCotisation $foundingCotisation, IncomeReceiptService $receipts): RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => 'required|exists:members,id',
            'amount' => 'required|numeric|min:0|max:200',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'payment_date' => 'nullable|date',
            'motif' => 'required|string|max:1000',
        ]);

        $motif = $validated['motif'];
        unset($validated['motif']);

        $before = Arr::except($foundingCotisation->getAttributes(), ['created_at', 'updated_at']);

        $validated['remaining'] = 200 - $validated['amount'];
        $validated['updated_by'] = $request->user()->id;

        DB::transaction(function () use ($foundingCotisation, $validated, $motif, $request, $before, $receipts) {
            $foundingCotisation->update($validated);
            $foundingCotisation->refresh();
            $receipts->sync($foundingCotisation);

            $after = Arr::except($foundingCotisation->getAttributes(), ['created_at', 'updated_at']);
            FoundingCotisationHistory::create([
                'founding_cotisation_id' => $foundingCotisation->id,
                'user_id' => $request->user()->id,
                'motif' => $motif,
                'before_values' => $before,
                'after_values' => $after,
                'occurred_at' => now(),
            ]);
        });

        return redirect()->route('founding-cotisations.index')->with('success', __('app.founding_cotisation_updated'));
    }

    public function destroy(Request $request, FoundingCotisation $foundingCotisation): RedirectResponse
    {
        return redirect()->route('founding-cotisations.edit', $foundingCotisation)->with('error', __('app.founding_cotisation_delete_forbidden'));
    }

    public function register(): View
    {
        $members = Member::where('status', 'active')
            ->with('foundingCotisations')
            ->orderBy('member_code')
            ->get()
            ->map(function ($member) {
                $founding = $member->foundingCotisations->first();
                $paid = $founding ? (float) $founding->amount : 0;
                $remaining = $founding ? (float) $founding->remaining : 200;

                return [
                    'member' => $member,
                    'founding' => $founding,
                    'paid_amount' => $paid,
                    'remaining' => $remaining,
                ];
            });

        return view('finance.founding-cotisations.register', compact('members'));
    }
}
