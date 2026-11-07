<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\EventRequest;
use App\Models\Event;
use App\Services\ReferenceNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $events = Event::query()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('starts_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('starts_at', '<=', $request->date('date_to')))
            ->orderBy('starts_at')
            ->paginate(20)
            ->withQueryString();

        $types = ['liturgy', 'meeting', 'celebration', 'formation', 'charity', 'other'];
        $statuses = ['planned', 'confirmed', 'completed', 'cancelled'];

        return view('events.index', compact('events', 'types', 'statuses'));
    }

    public function create(): View
    {
        return view('events.create');
    }

    public function store(EventRequest $request, ReferenceNumberService $references): RedirectResponse
    {
        $data = $request->validated();
        $data['reference'] = $references->next('EVT');
        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        $event = Event::create($data);

        return redirect()->route('events.show', $event)->with('success', __('app.event_created'));
    }

    public function show(Event $event): View
    {
        return view('events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        return view('events.edit', compact('event'));
    }

    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $event->update($data);

        return redirect()->route('events.show', $event)->with('success', __('app.event_updated'));
    }

    public function destroy(Request $request, Event $event): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('events.manage'), 403);
        $event->delete();

        return redirect()->route('events.index')->with('success', __('app.event_deleted'));
    }
}