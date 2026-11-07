<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.community_short') }} · {{ __('app.tagline') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="landing">
    <header class="landing-nav">
        <a class="brand" href="{{ route('home') }}">
            <span class="cross">
                <img src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="" style="width:95%;height:95%;object-fit:contain;display:block;">
            </span>
            <span><b>OCM</b><small>{{ __('app.community_short') }}</small></span>
        </a>

        <div class="landing-actions">
            @foreach (['fr' => 'FR', 'ti' => 'ትግርኛ'] as $locale => $label)
                <form method="post" action="{{ route('locale.update', $locale) }}">
                    @csrf
                    <button type="submit" class="lang {{ app()->getLocale() === $locale ? 'active' : '' }}">{{ $label }}</button>
                </form>
            @endforeach
            @auth
                <a class="button" href="{{ route('dashboard') }}">{{ __('app.open_dashboard') }}</a>
            @else
                <a class="button" href="{{ route('login') }}">{{ __('app.login') }}</a>
            @endauth
        </div>
    </header>

    <main class="landing-main">
        <div class="landing-hero">
            <p class="eyebrow">{{ __('app.landing_badge') }}</p>
            <h1>{{ __('app.landing_title') }}</h1>
            <p>{{ __('app.landing_text') }}</p>

            <div class="landing-cta">
                @auth
                    <a class="button" href="{{ route('dashboard') }}">{{ __('app.open_dashboard') }}</a>
                @else
                    <a class="button" href="{{ route('login') }}">{{ __('app.login') }}</a>
                @endauth
            </div>

            <p class="note">{{ __('app.private_system') }}</p>
        </div>

        <aside class="landing-card">
            <span class="landing-mark">
                <img src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="" style="width:95%;height:95%;object-fit:contain;display:block;">
            </span>
            <h2>{{ __('app.landing_panel_title') }}</h2>
            <p>{{ __('app.landing_panel_text') }}</p>
            <ul class="landing-list">
                <li><span class="dot"></span>{{ __('app.landing_feature_members') }}</li>
                <li><span class="dot"></span>{{ __('app.landing_feature_finance') }}</li>
                <li><span class="dot"></span>{{ __('app.landing_feature_events') }}</li>
                <li><span class="dot"></span>{{ __('app.landing_feature_docs') }}</li>
            </ul>
        </aside>
    </main>

    <footer class="app-footer" style="max-width: 1120px; margin: 0 auto">
        <span>{{ __('app.community_short') }} · {{ now()->year }}</span>
        <span>{{ __('app.landing_note') }}</span>
    </footer>
</body>
</html>