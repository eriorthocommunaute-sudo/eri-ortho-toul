<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.login') }} · {{ __('app.community_short') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="login-page">
    <section class="login-brand">
        <a class="brand" href="{{ route('home') }}">
            <span class="cross">
                <img src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="" style="width:95%;height:95%;object-fit:contain;display:block;margin:auto;">
            </span>
            <span><b>OCM</b><small>{{ __('app.community_short') }}</small></span>
        </a>

        <div>
            <p class="eyebrow">{{ __('app.secure_space') }}</p>
            <h2>{{ __('app.login_brand_title') }}</h2>
            <p>{{ __('app.welcome_text') }}</p>
        </div>

        <div class="brand-foot">
            <span>{{ __('app.community_short') }} · {{ now()->year }}</span>
            <span>{{ __('app.footer_note') }}</span>
        </div>
    </section>

    <div class="login-card-wrap">
        <div class="login-card">
            <div class="login-lang">
                @foreach (['fr' => 'FR', 'ti' => 'ትግርኛ'] as $locale => $label)
                    <form method="post" action="{{ route('locale.update', $locale) }}">
                        @csrf
                        <button type="submit" class="lang {{ app()->getLocale() === $locale ? 'active' : '' }}">{{ $label }}</button>
                    </form>
                @endforeach
            </div>

            <h1>{{ __('app.login_heading') }}</h1>
            <p class="muted">{{ __('app.login_subtitle') }}</p>

            <form method="post" action="{{ route('login.store') }}">
                @csrf

                <label>
                    {{ __('app.email') }}
                    <input name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                </label>

                <label>
                    {{ __('app.password') }}
                    <input name="password" type="password" required autocomplete="current-password">
                </label>

                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror

                <label class="remember">
                    <input type="checkbox" name="remember" value="1"> {{ __('app.remember_me') }}
                </label>

                <button type="submit">{{ __('app.login') }}</button>
            </form>

            <p class="note" style="margin: 16px 0 0">
                <a href="{{ route('home') }}">{{ __('app.back_home') }}</a>
            </p>
        </div>
    </div>
</body>
</html>