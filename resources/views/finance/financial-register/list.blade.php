@extends('layouts.app')
@section('content')
<div class="financial-registers-list">
    <div class="flex items-center justify-between mb-4" style="gap: 1rem; flex-wrap: wrap;">
        <div>
            <p class="eyebrow">{{ __('app.management') }}</p>
            <h1>{{ __('app.financial_register') }}</h1>
        </div>
        <a href="{{ route('financial-register.index') }}" class="button">
            {{ __('app.generate_new_register') }}
        </a>
    </div>

    @if (count($registers) === 0)
        <section class="panel">
            <p class="muted">{{ __('app.no_data') }}</p>
        </section>
    @else
        <section class="panel tablepanel">
            <div class="scroll">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('app.reference') }}</th>
                            <th>{{ __('app.title') }}</th>
                            <th>{{ __('app.period') }}</th>
                            <th>{{ __('app.type') }}</th>
                            <th>{{ __('app.created') }}</th>
                            <th>{{ __('app.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registers as $register)
                            <tr>
                                <td>
                                    <strong>{{ $register['reference'] }}</strong>
                                    @if (($register['version'] ?? 1) > 1)
                                        <span class="status-badge status-draft">v{{ $register['version'] }}</span>
                                    @endif
                                </td>
                                <td>{{ $register['title'] ?? $register['period_label'] ?? '' }}</td>
                                <td>
                                    @if (($register['period_type'] ?? '') === 'monthly')
                                        {{ $register['period_label'] ?? '' }}
                                    @else
                                        {{ $register['period_label'] ?? '' }}
                                    @endif
                                </td>
                                <td>
                                    @if (($register['period_type'] ?? '') === 'monthly')
                                        {{ __('app.monthly') }}
                                    @else
                                        {{ __('app.quarterly') }}
                                    @endif
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($register['updated_at'] ?? $register['created_at'])->format('d/m/Y') }}
                                </td>
                                <td>
                                    <a href="{{ route('financial-register.preview', $register['reference']) }}" class="button">{{ __('app.preview') }}</a>
                                    <a href="{{ route('financial-register.download', $register['reference']) }}" class="button">{{ __('app.download') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>
@endsection
