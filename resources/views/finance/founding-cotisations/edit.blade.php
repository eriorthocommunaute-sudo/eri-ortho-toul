@extends('layouts.app')
@section('content')
<h1>{{ __('app.edit_founding_cotisation') }}</h1>

<form method="POST" action="{{ route('founding-cotisations.update', $foundingCotisation) }}">
    @csrf @method('PUT')
    <div class="grid">
        <label>{{ __('app.member') }}
            <select name="member_id" required>
                <option value="">{{ __('app.select_member') }}</option>
                @foreach($members as $member)
                    <option value="{{ $member->id }}" @selected(old('member_id', $foundingCotisation->member_id) == $member->id)>{{ $member->given_name }} {{ $member->family_name }} ({{ $member->member_code }})</option>
                @endforeach
            </select>
            @error('member_id')<small style="color: red;">{{ $message }}</small>@enderror
        </label>

        <label>{{ __('app.amount') }} (€)
            <input type="number" name="amount" value="{{ old('amount', $foundingCotisation->amount) }}" min="0" max="200" step="1" required oninput="document.getElementById('remaining').value = 200 - this.value">
            @error('amount')<small style="color: red;">{{ $message }}</small>@enderror
        </label>

        <label>{{ __('app.remaining') }} (€)
            <input type="number" id="remaining" name="remaining" value="{{ old('remaining', $foundingCotisation->remaining) }}" readonly style="background: #f5f5f5;">
        </label>

        <label>{{ __('app.payment_date') }}
            <input type="date" name="payment_date" value="{{ old('payment_date', $foundingCotisation->payment_date?->format('Y-m-d')) }}">
            @error('payment_date')<small style="color: red;">{{ $message }}</small>@enderror
        </label>

        <label>{{ __('app.payment_method') }}
            <select name="payment_method">
                <option value="">{{ __('app.select') }}</option>
                <option value="cash" @selected(old('payment_method', $foundingCotisation->payment_method) == 'cash')>{{ __('app.cash') }}</option>
                <option value="transfer" @selected(old('payment_method', $foundingCotisation->payment_method) == 'transfer')>{{ __('app.transfer') }}</option>
                <option value="check" @selected(old('payment_method', $foundingCotisation->payment_method) == 'check')>{{ __('app.check') }}</option>
            </select>
        </label>

        <label>{{ __('app.notes') }}
            <textarea name="notes" rows="2">{{ old('notes', $foundingCotisation->notes) }}</textarea>
        </label>

        <label style="grid-column: 1 / -1;">{{ __('app.motif') }} *
            <textarea name="motif" rows="2" required placeholder="{{ __('app.motif_required') }}">{{ old('motif') }}</textarea>
            @error('motif')<small style="color: red;">{{ $message }}</small>@enderror
        </label>

        <p>
            <button>{{ __('app.save') }}</button>
            <a class="button" href="{{ route('founding-cotisations.index') }}">{{ __('app.cancel') }}</a>
        </p>
    </div>
</form>

<section class="panel" style="margin-top: 2rem;">
    <h2>{{ __('app.modification_history') }}</h2>
    @if ($foundingCotisation->histories->isEmpty())
        <p class="muted">{{ __('app.founding_cotisation_history_empty') }}</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>{{ __('app.date') }}</th>
                    <th>{{ __('app.modified_by') }}</th>
                    <th>{{ __('app.motif') }}</th>
                    <th>{{ __('app.changes') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($foundingCotisation->histories as $history)
                    @php
                        $changes = [];
                        foreach ($history->before_values ?? [] as $key => $oldValue) {
                            $newValue = $history->after_values[$key] ?? null;
                            if ((string) $oldValue !== (string) $newValue) {
                                $changes[] = $key . ': ' . ($oldValue ?? '—') . ' → ' . ($newValue ?? '—');
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $history->occurred_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ $history->user?->name ?? '—' }}</td>
                        <td style="text-align: left;">{{ $history->motif }}</td>
                        <td style="text-align: left;">
                            @forelse ($changes as $change)
                                <div>{{ $change }}</div>
                            @empty
                                —
                            @endforelse
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>
@endsection
