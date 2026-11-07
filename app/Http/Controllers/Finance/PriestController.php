<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Priest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PriestController extends Controller
{
    public function index(Request $request): View
    {
        $priests = Priest::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->string('search') . '%'))
            ->when($request->filled('status'), fn ($q) => match ($request->string('status')) {
                'active' => $q->where('is_active', true),
                'inactive' => $q->where('is_active', false),
                default => $q,
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('finance.priests.index', compact('priests'));
    }

    public function create(): View
    {
        return view('finance.priests.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:priests,name'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $data['is_active'] = true;
        Priest::create($data);

        return redirect()->route('priests.index')->with('success', __('app.priest_created'));
    }

    public function edit(Priest $priest): View
    {
        return view('finance.priests.edit', compact('priest'));
    }

    public function update(Request $request, Priest $priest): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:priests,name,' . $priest->id],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $priest->update($data);

        return redirect()->route('priests.index')->with('success', __('app.priest_updated'));
    }

    public function destroy(Request $request, Priest $priest): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('finance.record'), 403);

        $priest->delete();

        return redirect()->route('priests.index')->with('success', __('app.priest_deleted'));
    }
}
