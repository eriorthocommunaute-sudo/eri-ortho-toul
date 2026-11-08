<!DOCTYPE html><html class="scroll-smooth" lang="{{ app()->getLocale() }}"><head>
<meta charset="utf-8">
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta content="web_standard" name="shell-type">
<title>Eritrean Orthodox Tewahedo Church Ba'ata Mariam Toulouse</title>
<link href="https://lh3.googleusercontent.com/aida-public/AB6AXuDZyxSnJIA0kGjo5EY3ukYIen4mgtBIXyZU7DGCaB9whLnHWqN-7TYQ2AKj3foQqDWeT_kfrp_pzAF_pVaJ2WMmrLB3ewomFtFj9WoZF6YXElijlUAGWc1NKqQT_1tQr0TdqqjeBRw_z-c5ZbELP3AM1ZISsN6jb0ijlcr089O_QEtjHh21eFVqf9dLW_zEd8DY3_yL2w35kdbQbyLVl2lfGitKcuMQOpfvHtLnmnG6PJ7aYgVNhfmR" rel="icon" type="image/png">
<link href="https://fonts.googleapis.com" rel="preconnect">
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect">
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&amp;family=Inter:wght@300;400;500;600;700&amp;family=Noto+Sans:wght@400;500;600;700&amp;display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
@vite('resources/css/home.css')
<style>
    html { scroll-behavior: smooth; }
    ::selection { background: #762B32; color: #F8F5EF; }
    .nav-scrolled {
      background-color: rgba(248, 245, 239, 0.95) !important;
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(185, 154, 98, 0.25);
      box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
    }
  </style>
</head>
<body class="bg-surface font-inter text-charcoal antialiased selection:bg-primary selection:text-surface">
<!-- 1. MINIMAL SANCTUARY HEADER & STICKY NAVIGATION -->
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-500 bg-transparent text-white" id="siteHeader">
<div class="max-w-7xl mx-auto px-6 lg:px-12 h-24 flex items-center justify-between">
<!-- Brand Logo + Sacred Title -->
<a class="flex items-center gap-3.5 group" href="#accueil">
<img alt="Croix Érythréenne Orthodoxe Tewahedo" class="h-11 w-auto object-contain transition-transform duration-300 group-hover:scale-105 filter drop-shadow" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDZyxSnJIA0kGjo5EY3ukYIen4mgtBIXyZU7DGCaB9whLnHWqN-7TYQ2AKj3foQqDWeT_kfrp_pzAF_pVaJ2WMmrLB3ewomFtFj9WoZF6YXElijlUAGWc1NKqQT_1tQr0TdqqjeBRw_z-c5ZbELP3AM1ZISsN6jb0ijlcr089O_QEtjHh21eFVqf9dLW_zEd8DY3_yL2w35kdbQbyLVl2lfGitKcuMQOpfvHtLnmnG6PJ7aYgVNhfmR">
<div class="flex flex-col">
<span class="font-ethiopic font-bold text-lg md:text-xl tracking-tight leading-tight header-brand-text">{{ __('home.brand_name') }}</span>
<span class="font-garamond text-xs md:text-sm tracking-widest uppercase opacity-80 header-brand-sub">{{ __('home.brand_sub') }}</span>
</div>
</a>
<!-- Desktop Smooth Anchor Navigation -->
<nav class="hidden lg:flex items-center gap-7 text-xs tracking-[0.16em] uppercase font-medium">
<a class="nav-item transition-colors hover:text-gold-accent opacity-90 hover:opacity-100" href="#accueil">{{ __('home.nav_home') }}</a>
<a class="nav-item transition-colors hover:text-gold-accent opacity-90 hover:opacity-100" href="#a-propos">{{ __('home.nav_about') }}</a>
<a class="nav-item transition-colors hover:text-gold-accent opacity-90 hover:opacity-100" href="#foi">{{ __('home.nav_faith') }}</a>
<a class="nav-item transition-colors hover:text-gold-accent opacity-90 hover:opacity-100" href="#celebrations">{{ __('home.nav_services') }}</a>
<a class="nav-item transition-colors hover:text-gold-accent opacity-90 hover:opacity-100" href="#actualites">{{ __('home.nav_news') }}</a>
<a class="nav-item transition-colors hover:text-gold-accent opacity-90 hover:opacity-100" href="#galerie">{{ __('home.nav_gallery') }}</a>
<a class="nav-item transition-colors hover:text-gold-accent opacity-90 hover:opacity-100" href="#contact">{{ __('home.nav_contact') }}</a>
</nav>
<!-- Right Language & Refined Donation Action -->
<div class="hidden sm:flex items-center gap-4">
<div class="text-[11px] tracking-wider uppercase font-medium px-2.5 py-1 rounded border border-white/20 lang-box flex items-center gap-1.5 backdrop-blur-sm">
<form action="{{ route('locale.update', 'ti') }}" class="contents" method="post">@csrf<button class="{{ app()->getLocale() === 'ti' ? 'text-gold-accent font-semibold' : 'opacity-80 hover:opacity-100 cursor-pointer' }}" type="submit">ትግርኛ</button></form>
<span class="opacity-40">|</span>
<form action="{{ route('locale.update', 'fr') }}" class="contents" method="post">@csrf<button class="{{ app()->getLocale() === 'fr' ? 'text-gold-accent font-semibold' : 'opacity-80 hover:opacity-100 cursor-pointer' }}" type="submit">FR</button></form>
</div>
</div>
<!-- Mobile Hamburger Toggle -->
<button aria-label="Menu" class="lg:hidden p-2 rounded-lg text-current focus:outline-none" id="mobileMenuBtn">
<span class="material-symbols-outlined text-3xl">menu</span>
</button>
</div>
<!-- Mobile Drawer Navigation -->
<div class="hidden lg:hidden bg-surface text-charcoal border-b border-stone-light px-6 py-6 transition-all duration-300 shadow-xl" id="mobileDrawer">
<div class="flex flex-col gap-4 text-sm font-medium tracking-wider uppercase">
<a class="mobile-nav-link py-2 border-b border-stone-light/50 font-ethiopic text-primary" href="#accueil">{{ __('home.mobile_home') }}</a>
<a class="mobile-nav-link py-2 border-b border-stone-light/50 font-ethiopic" href="#a-propos">{{ __('home.mobile_about') }}</a>
<a class="mobile-nav-link py-2 border-b border-stone-light/50 font-ethiopic" href="#foi">{{ __('home.mobile_faith') }}</a>
<a class="mobile-nav-link py-2 border-b border-stone-light/50 font-ethiopic" href="#celebrations">{{ __('home.mobile_services') }}</a>
<a class="mobile-nav-link py-2 border-b border-stone-light/50 font-ethiopic" href="#actualites">{{ __('home.mobile_news') }}</a>
<a class="mobile-nav-link py-2 border-b border-stone-light/50 font-ethiopic" href="#galerie">{{ __('home.mobile_gallery') }}</a>
<a class="mobile-nav-link py-2 font-ethiopic" href="#contact">{{ __('home.mobile_contact') }}</a>
<div class="pt-3 flex items-center justify-between">
<span class="text-xs tracking-widest text-charcoal-muted uppercase">{{ __('home.lang_label') }}</span>
<div class="flex items-center gap-1.5 text-xs font-bold text-primary">
<form action="{{ route('locale.update', 'ti') }}" class="contents" method="post">@csrf<button class="{{ app()->getLocale() === 'ti' ? 'underline' : 'opacity-70 hover:opacity-100' }} cursor-pointer" type="submit">{{ __('home.lang_ti') }}</button></form>
<span class="text-charcoal-muted opacity-60">·</span>
<form action="{{ route('locale.update', 'fr') }}" class="contents" method="post">@csrf<button class="{{ app()->getLocale() === 'fr' ? 'underline' : 'opacity-70 hover:opacity-100' }} cursor-pointer" type="submit">{{ __('home.lang_fr') }}</button></form>
</div>
</div>
</div>
</div>
</header>
<!-- 2. HERO PHOTOGRAPHIC EDITORIAL EXPERIENCE (Sanctuary.us aesthetic) -->
<section class="relative w-full min-h-screen flex items-center justify-center overflow-hidden bg-charcoal" id="accueil">
<!-- Authentic Asmara Cathedral (Enda Mariam) Full-View High Resolution -->
<div class="absolute inset-0 z-0">
<img alt="Cathédrale Orthodoxe Érythréenne d'Asmara" class="w-full h-full object-cover object-center filter brightness-[0.88] contrast-[1.05]" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBDbt38kSokb1vViSUwwd3UIHFoZWGYpfkkfrK81wwwVQttdW_mxQUIRV1DXVgDT53lF0NhYpS-hQurGmBilHKOCFmmt0C-Bp1duc7dIAsl0r2Ir0UTkd9aK91JvVUY9nqkMlfPN4V7WRzfllKEXtgPQQa9SLQTz0B1hkLieOeUsiCEMBhq7aqpYlHas_JjiAhw9P9nmLd_70aFeSnzd5NtW_hSWrBGZmejNTmIyhmesN_GtyOh5BTS">
<!-- Soft Sanctuary Editorial Gradients: keeps towers and portal crisp while ensuring text contrast -->
<div class="absolute inset-0 bg-gradient-to-t from-charcoal via-black/45 to-black/35"></div>
<div class="absolute inset-0 bg-radial from-transparent via-black/25 to-black/60"></div>
</div>
<!-- Sacred Content Stage -->
<div class="relative z-10 max-w-5xl mx-auto px-6 text-center text-white flex flex-col items-center pt-28 pb-16">
<!-- Emblem heraldic mark -->
<div class="mb-5 p-3 rounded-full bg-white/10 backdrop-blur-md border border-white/20 inline-flex items-center justify-center transform hover:scale-105 transition-all">
<img alt="Croix Bénie" class="w-10 h-10 object-contain drop-shadow-lg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDZyxSnJIA0kGjo5EY3ukYIen4mgtBIXyZU7DGCaB9whLnHWqN-7TYQ2AKj3foQqDWeT_kfrp_pzAF_pVaJ2WMmrLB3ewomFtFj9WoZF6YXElijlUAGWc1NKqQT_1tQr0TdqqjeBRw_z-c5ZbELP3AM1ZISsN6jb0ijlcr089O_QEtjHh21eFVqf9dLW_zEd8DY3_yL2w35kdbQbyLVl2lfGitKcuMQOpfvHtLnmnG6PJ7aYgVNhfmR">
</div>
<!-- Sacred Subtitle pill -->
<div class="inline-flex items-center gap-2 px-4 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs tracking-[0.2em] uppercase text-stone-100 mb-6 font-garamond">
<span class="">{{ __('home.hero_pill_1') }}</span>
<span class="text-gold-accent">•</span>
<span class="">{{ __('home.hero_pill_2') }}</span>
</div>
<!-- Primary Editorial Tigrigna Headline -->
<h1 class="font-ethiopic text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white mb-3 drop-shadow-md">
        {{ __('home.hero_title') }}
      </h1>
<p class="font-ethiopic text-xl sm:text-2xl lg:text-3xl font-semibold text-gold-accent tracking-wide mb-3 drop-shadow">
        {{ __('home.hero_subtitle') }}
      </p>
<!-- French Subtitle -->
<p class="font-garamond italic text-xl sm:text-2xl text-stone-200 font-normal max-w-2xl mx-auto mb-5">
        {{ __('home.hero_fr_title') }}
      </p>
<!-- Welcome Note -->
<p class="font-inter text-sm sm:text-base text-stone-300 max-w-2xl mx-auto leading-relaxed font-light mb-10">
        {{ __('home.hero_text') }}
      </p>
<!-- Editorial Smooth Scroll Action CTAs -->
<div class="flex flex-col sm:flex-row items-center gap-4">
<a class="px-8 py-3.5 rounded-full bg-primary hover:bg-[#8c333a] text-white text-xs tracking-[0.2em] uppercase font-medium transition-all duration-300 shadow-lg border border-gold-accent/40 hover:scale-[1.02]" href="#a-propos">
          {{ __('home.hero_cta_1') }}
        </a>
<a class="px-8 py-3.5 rounded-full bg-white/15 hover:bg-white/25 text-white backdrop-blur-md text-xs tracking-[0.2em] uppercase font-medium transition-all duration-300 border border-white/25 hover:scale-[1.02]" href="#celebrations">
          {{ __('home.hero_cta_2') }}
        </a>
</div>
<!-- Architectural Heritage Attribution Pill -->
<div class="mt-14 inline-flex items-center gap-2 text-[11px] tracking-wider text-stone-300 font-garamond italic bg-black/30 backdrop-blur-sm px-4 py-1.5 rounded-full border border-white/10">
<span class="material-symbols-outlined text-xs text-gold-accent">architecture</span>
<span class="">{{ __('home.hero_heritage') }}</span>
</div>
</div>
</section>
<!-- 3. SECTION 03: WELCOME & CHURCH INTRODUCTION (#a-propos) -->
<section class="py-28 lg:py-36 bg-surface border-b border-stone-light" id="a-propos">
<div class="max-w-6xl mx-auto px-6 lg:px-12">
<!-- Section Header -->
<div class="text-center max-w-3xl mx-auto mb-16 lg:mb-24">
<span class="text-xs uppercase tracking-[0.25em] text-primary font-semibold block mb-3 font-garamond">
          {{ __('home.about_eyebrow') }}
        </span>
<h2 class="font-ethiopic text-3xl sm:text-4xl lg:text-5xl font-bold text-charcoal mb-4">
          {{ __('home.about_title') }}
        </h2>
<p class="font-garamond italic text-2xl text-charcoal-muted">
          {{ __('home.about_subtitle') }}
        </p>
<div class="w-16 h-px bg-gold-accent mx-auto mt-6"></div>
</div>
<!-- Spacious Two-Column Layout -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-start">
<!-- Left Column: Tigrigna Message & Sacred Heritage -->
<div class="lg:col-span-6 space-y-6">
<div class="p-8 lg:p-10 rounded-2xl bg-white border border-stone-light shadow-sm">
<div class="flex items-center gap-3 text-primary mb-5">
<span class="material-symbols-outlined text-2xl">church</span>
<span class="font-ethiopic font-semibold text-lg">{{ __('home.about_card_title') }}</span>
</div>
<p class="font-ethiopic text-base sm:text-lg text-charcoal leading-loose font-normal text-justify">
              {{ __('home.about_card_text') }}
            </p>
<div class="mt-8 pt-6 border-t border-stone-light flex items-center justify-between">
<span class="font-ethiopic text-sm text-charcoal-muted">{{ __('home.about_verse') }}</span>
<span class="font-garamond text-xs uppercase tracking-wider text-primary font-semibold">{{ __('home.about_verse_ref') }}</span>
</div>
</div>
<div class="p-6 rounded-xl bg-stone-light/60 border border-stone-light text-xs text-charcoal-muted leading-relaxed flex items-start gap-4">
<span class="material-symbols-outlined text-primary text-xl mt-0.5">verified</span>
<div class="text-justify">
              <span class="font-semibold text-charcoal block mb-1">{{ __('home.about_apostolic_title') }}</span>
              {{ __('home.about_apostolic_text') }}
            </div>
</div>
</div>
<!-- Right Column: French Pastoral Context & Spiritual Warmth -->
<div class="lg:col-span-6 space-y-6">
<div class="prose max-w-none text-charcoal-muted leading-relaxed font-light text-base lg:text-lg space-y-5">
<p class="text-justify">
              {{ __('home.about_fr_p_1') }}
            </p>
<p class="text-justify">
              {{ __('home.about_fr_p_2') }}
            </p>
</div>
<!-- Editorial Architecture & Fellowship Callout -->
<div class="mt-8 p-8 rounded-2xl bg-[#762B32] text-white relative overflow-hidden shadow-lg">
<div class="absolute -right-6 -bottom-6 opacity-10 pointer-events-none" style="right:-1.5rem;bottom:-1.5rem">
<img alt="" class="w-48 h-48 object-contain filter invert" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDZyxSnJIA0kGjo5EY3ukYIen4mgtBIXyZU7DGCaB9whLnHWqN-7TYQ2AKj3foQqDWeT_kfrp_pzAF_pVaJ2WMmrLB3ewomFtFj9WoZF6YXElijlUAGWc1NKqQT_1tQr0TdqqjeBRw_z-c5ZbELP3AM1ZISsN6jb0ijlcr089O_QEtjHh21eFVqf9dLW_zEd8DY3_yL2w35kdbQbyLVl2lfGitKcuMQOpfvHtLnmnG6PJ7aYgVNhfmR">
</div>
<span class="text-xs uppercase tracking-[0.2em] text-gold-accent font-semibold block mb-2 font-garamond">
              {{ __('home.about_callout_eyebrow') }}
            </span>
<h3 class="font-garamond italic text-2xl mb-3 text-white">
              {{ __('home.about_callout_quote') }}
            </h3>
<p class="text-xs sm:text-sm text-stone-200 font-light leading-relaxed mb-6 text-justify">
              {{ __('home.about_callout_text') }}
            </p>
<a class="inline-flex items-center gap-2 text-xs uppercase tracking-widest text-gold-accent font-semibold hover:text-white transition-colors" href="#contact">
<span class="">{{ __('home.about_callout_cta') }}</span>
<span class="material-symbols-outlined text-base">arrow_forward</span>
</a>
</div>
</div>
</div>
</div>
</section>
<!-- 4. SECTION 04: OUR FAITH AND VISION (#foi) -->
<section class="py-28 lg:py-36 bg-[#252525] text-white relative overflow-hidden" id="foi">
<!-- Emblem overlay background -->
<div class="absolute inset-0 flex items-center justify-center opacity-5 pointer-events-none">
<img alt="Croix" class="w-[600px] h-[600px] object-contain filter invert" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDZyxSnJIA0kGjo5EY3ukYIen4mgtBIXyZU7DGCaB9whLnHWqN-7TYQ2AKj3foQqDWeT_kfrp_pzAF_pVaJ2WMmrLB3ewomFtFj9WoZF6YXElijlUAGWc1NKqQT_1tQr0TdqqjeBRw_z-c5ZbELP3AM1ZISsN6jb0ijlcr089O_QEtjHh21eFVqf9dLW_zEd8DY3_yL2w35kdbQbyLVl2lfGitKcuMQOpfvHtLnmnG6PJ7aYgVNhfmR">
</div>
<div class="relative z-10 max-w-5xl mx-auto px-6 lg:px-12 text-center">
<!-- Heraldic Cross -->
<div class="mb-8 flex justify-center">
<img alt="Croix Bénie" class="w-16 h-16 object-contain drop-shadow-md" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDZyxSnJIA0kGjo5EY3ukYIen4mgtBIXyZU7DGCaB9whLnHWqN-7TYQ2AKj3foQqDWeT_kfrp_pzAF_pVaJ2WMmrLB3ewomFtFj9WoZF6YXElijlUAGWc1NKqQT_1tQr0TdqqjeBRw_z-c5ZbELP3AM1ZISsN6jb0ijlcr089O_QEtjHh21eFVqf9dLW_zEd8DY3_yL2w35kdbQbyLVl2lfGitKcuMQOpfvHtLnmnG6PJ7aYgVNhfmR">
</div>
<span class="text-xs uppercase tracking-[0.3em] text-gold-accent font-semibold block mb-4 font-garamond">
        {{ __('home.faith_eyebrow') }}
      </span>
<!-- Heroic Headline -->
<h2 class="font-ethiopic text-3xl sm:text-5xl font-bold tracking-tight text-white mb-2">
        {{ __('home.faith_title') }}
      </h2>
<p class="font-garamond italic text-2xl sm:text-3xl text-gold-accent mb-8">
        {{ __('home.faith_subtitle') }}
      </p>
<div class="w-20 h-px bg-gold-accent/50 mx-auto mb-10"></div>
<!-- Rich Editorial Manifesto -->
<div class="max-w-3xl mx-auto space-y-6 text-stone-300 font-light text-base sm:text-lg leading-relaxed">
<p class="text-justify">
          {!! __('home.faith_p_1') !!}
        </p>
<p class="text-justify">
          {{ __('home.faith_p_2') }}
        </p>
</div>
<!-- 3 Pillars Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-16 text-left">
<div class="p-6 rounded-xl bg-white/5 border border-white/10 hover:border-gold-accent/40 transition-colors">
<span class="text-gold-accent font-garamond text-xl font-bold block mb-2">{{ __('home.faith_pillar1_tag') }}</span>
<h4 class="text-white font-semibold text-sm mb-2">{{ __('home.faith_pillar1_title') }}</h4>
<p class="text-xs text-stone-400 leading-relaxed font-light">
            {{ __('home.faith_pillar1_text') }}
          </p>
</div>
<div class="p-6 rounded-xl bg-white/5 border border-white/10 hover:border-gold-accent/40 transition-colors">
<span class="text-gold-accent font-garamond text-xl font-bold block mb-2">{{ __('home.faith_pillar2_tag') }}</span>
<h4 class="text-white font-semibold text-sm mb-2">{{ __('home.faith_pillar2_title') }}</h4>
<p class="text-xs text-stone-400 leading-relaxed font-light">
            {{ __('home.faith_pillar2_text') }}
          </p>
</div>
<div class="p-6 rounded-xl bg-white/5 border border-white/10 hover:border-gold-accent/40 transition-colors">
<span class="text-gold-accent font-garamond text-xl font-bold block mb-2">{{ __('home.faith_pillar3_tag') }}</span>
<h4 class="text-white font-semibold text-sm mb-2">{{ __('home.faith_pillar3_title') }}</h4>
<p class="text-xs text-stone-400 leading-relaxed font-light">
            {{ __('home.faith_pillar3_text') }}
          </p>
</div>
</div>
</div>
</section>
<!-- 5. SECTION 05: LITURGICAL LIFE & CELEBRATIONS (#celebrations) -->
<section class="py-28 lg:py-36 bg-surface-low border-b border-stone-light" id="celebrations">
<div class="max-w-6xl mx-auto px-6 lg:px-12">
<div class="text-center max-w-3xl mx-auto mb-16 lg:mb-20">
<span class="text-xs uppercase tracking-[0.25em] text-primary font-semibold block mb-3 font-garamond">
          {{ __('home.services_eyebrow') }}
        </span>
<h2 class="font-ethiopic text-3xl sm:text-4xl lg:text-5xl font-bold text-charcoal mb-4">
          {{ __('home.services_title') }}
        </h2>
<p class="font-garamond italic text-2xl text-charcoal-muted">
          {{ __('home.services_subtitle') }}
        </p>
<div class="w-16 h-px bg-gold-accent mx-auto mt-6"></div>
</div>
<!-- Clean 3-Card Minimalist Sanctuary Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8">
<!-- 1. Sainte Liturgie (ቅዳሴ) -->
<div class="bg-white rounded-2xl p-8 border border-stone-light shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
<div>
<div class="flex items-center justify-between mb-6">
<span class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
<span class="material-symbols-outlined text-xl">church</span>
</span>
<span class="text-[11px] font-semibold uppercase tracking-wider px-3 py-1 rounded-full bg-stone-light text-charcoal">
                {{ __('home.service1_badge') }}
              </span>
</div>
<h3 class="font-ethiopic text-xl font-bold text-charcoal mb-1">{{ __('home.service1_title') }}</h3>
<h4 class="font-garamond italic text-lg text-primary mb-4">{{ __('home.service1_sub') }}</h4>
<p class="text-xs text-charcoal-muted leading-relaxed font-light mb-6 text-justify">
              {{ __('home.service1_text') }}
            </p>
<div class="space-y-2 py-3 border-t border-stone-light text-xs text-charcoal">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-base">schedule</span>
<span class="font-medium">{{ __('home.service1_time') }}</span>
</div>
<div class="flex items-center gap-2 text-charcoal-muted">
<span class="material-symbols-outlined text-base">language</span>
<span class="">{{ __('home.service1_lang') }}</span>
</div>
</div>
</div>
<div class="pt-6 border-t border-stone-light/60 mt-4">
<a class="text-xs uppercase tracking-widest font-semibold text-primary hover:text-gold-accent flex items-center gap-1.5 transition-colors" href="#contact">
<span class="">{{ __('home.service1_cta') }}</span>
<span class="material-symbols-outlined text-sm">north_east</span>
</a>
</div>
</div>
<!-- 2. Prière & Vigiles (ጸሎት) -->
<div class="bg-white rounded-2xl p-8 border border-stone-light shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
<div>
<div class="flex items-center justify-between mb-6">
<span class="w-10 h-10 rounded-full bg-gold-accent/15 text-gold-accent flex items-center justify-center">
<span class="material-symbols-outlined text-xl">wb_twilight</span>
</span>
<span class="text-[11px] font-semibold uppercase tracking-wider px-3 py-1 rounded-full bg-stone-light text-charcoal">
                {{ __('home.service2_badge') }}
              </span>
</div>
<h3 class="font-ethiopic text-xl font-bold text-charcoal mb-1">{{ __('home.service2_title') }}</h3>
<h4 class="font-garamond italic text-lg text-primary mb-4">{{ __('home.service2_sub') }}</h4>
<p class="text-xs text-charcoal-muted leading-relaxed font-light mb-6 text-justify">
              {{ __('home.service2_text') }}
            </p>
<div class="space-y-2 py-3 border-t border-stone-light text-xs text-charcoal">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-gold-accent text-base">schedule</span>
<span class="font-medium">{{ __('home.service2_time') }}</span>
</div>
<div class="flex items-center gap-2 text-charcoal-muted">
<span class="material-symbols-outlined text-base">person</span>
<span class="">{{ __('home.service2_extra') }}</span>
</div>
</div>
</div>
<div class="pt-6 border-t border-stone-light/60 mt-4">
<a class="text-xs uppercase tracking-widest font-semibold text-primary hover:text-gold-accent flex items-center gap-1.5 transition-colors" href="#contact">
<span class="">{{ __('home.service2_cta') }}</span>
<span class="material-symbols-outlined text-sm">north_east</span>
</a>
</div>
</div>
<!-- 3. Solennités & Fêtes (በዓላት) -->
<div class="bg-white rounded-2xl p-8 border border-stone-light shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
<div>
<div class="flex items-center justify-between mb-6">
<span class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
<span class="material-symbols-outlined text-xl">local_florist</span>
</span>
<span class="text-[11px] font-semibold uppercase tracking-wider px-3 py-1 rounded-full bg-gold-accent/20 text-charcoal">
                {{ __('home.service3_badge') }}
              </span>
</div>
<h3 class="font-ethiopic text-xl font-bold text-charcoal mb-1">{{ __('home.service3_title') }}</h3>
<h4 class="font-garamond italic text-lg text-primary mb-4">{{ __('home.service3_sub') }}</h4>
<p class="text-xs text-charcoal-muted leading-relaxed font-light mb-6 text-justify">
              {{ __('home.service3_text') }}
            </p>
<div class="space-y-2 py-3 border-t border-stone-light text-xs text-charcoal">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-base">event</span>
<span class="font-medium">{{ __('home.service3_time') }}</span>
</div>
<div class="flex items-center gap-2 text-charcoal-muted">
<span class="material-symbols-outlined text-base">checkroom</span>
<span class="">{{ __('home.service3_extra') }}</span>
</div>
</div>
</div>
<div class="pt-6 border-t border-stone-light/60 mt-4">
<a class="text-xs uppercase tracking-widest font-semibold text-primary hover:text-gold-accent flex items-center gap-1.5 transition-colors" href="#actualites">
<span class="">{{ __('home.service3_cta') }}</span>
<span class="material-symbols-outlined text-sm">north_east</span>
</a>
</div>
</div>
</div>
</div>
</section>
<!-- 6. SECTION 06: COMMUNITY LIFE & ANNOUNCEMENTS (#actualites) -->
<section class="py-28 lg:py-36 bg-surface border-b border-stone-light" id="actualites">
<div class="max-w-6xl mx-auto px-6 lg:px-12">
<div class="text-center max-w-3xl mx-auto mb-16 lg:mb-20">
<span class="text-xs uppercase tracking-[0.25em] text-primary font-semibold block mb-3 font-garamond">
          {{ __('home.news_eyebrow') }}
        </span>
<h2 class="font-ethiopic text-3xl sm:text-4xl lg:text-5xl font-bold text-charcoal mb-4">
          {{ __('home.news_title') }}
        </h2>
<p class="font-garamond italic text-2xl text-charcoal-muted">
          {{ __('home.news_subtitle') }}
        </p>
<div class="w-16 h-px bg-gold-accent mx-auto mt-6"></div>
</div>
<!-- Sanctuary Asymmetric Editorial Layout -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
<!-- Large Featured Announcement Card (7 cols) -->
<article class="lg:col-span-7 bg-white rounded-2xl overflow-hidden border border-stone-light shadow-sm flex flex-col justify-between group">
<div class="relative h-64 sm:h-80 overflow-hidden">
<img alt="Éducation chrétienne et catéchèse - École du dimanche de la communauté orthodoxe érythréenne" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBKEaEXYB29va7JYgaioF2U9jwyZ-Osyj_jGOT3hYguN9DZSJCqcQrvX_TOyWRg-qZ0HhgVNm5JPTG4DBSgSH4L_AuI_1H8Gw85neK10M1mdmD_rMBvAu25WKq0sZzcOqQW5HXvFY5nFpc2GBGA5-HCxTwigDoiHs3Cx4MTnuqkRZ3JWNUF8FO4vVbADc0txKS19CaL-l8nec0XoTOSbqqLcWiwEdwTt7MXeBO2-E4">
<span class="absolute top-4 left-4 bg-primary text-white text-[11px] font-semibold tracking-wider uppercase px-3 py-1 rounded-full shadow">
              {{ __('home.news1_badge') }}
            </span>
</div>
<div class="p-8 sm:p-10 flex-1 flex flex-col justify-between">
<div>
<span class="text-xs uppercase tracking-wider text-charcoal-muted block mb-2 font-garamond">
                {{ __('home.news1_meta') }}
              </span>
<h3 class="font-ethiopic text-2xl font-bold text-charcoal mb-2">
                {{ __('home.news1_title') }}
              </h3>
<h4 class="font-garamond italic text-lg text-primary mb-4">
                {{ __('home.news1_sub') }}
              </h4>
<p class="text-xs sm:text-sm text-charcoal-muted leading-relaxed font-light mb-6 text-justify">
                {{ __('home.news1_text') }}
              </p>
</div>
<div class="pt-4 border-t border-stone-light flex items-center justify-between">
<span class="text-xs font-semibold text-charcoal">{{ __('home.news1_when') }}</span>
<a class="text-xs uppercase tracking-widest font-semibold text-primary hover:text-gold-accent flex items-center gap-1 transition-colors" href="#contact">
<span class="">{{ __('home.news1_cta') }}</span>
<span class="material-symbols-outlined text-sm">arrow_forward</span>
</a>
</div>
</div>
</article>
<!-- Two Smaller Structured Editorial Blocks (5 cols) -->
<div class="lg:col-span-5 flex flex-col gap-8">
<!-- Block 1: Grand Carême -->
<article class="bg-white rounded-2xl p-7 border border-stone-light shadow-sm flex flex-col justify-between flex-1 group">
<div>
<div class="flex items-center justify-between mb-3">
<span class="text-[11px] font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded bg-gold-accent/15 text-charcoal font-garamond">
                  {{ __('home.news2_badge') }}
                </span>
<span class="text-xs text-charcoal-muted">{{ __('home.news2_date') }}</span>
</div>
<h3 class="font-ethiopic text-lg font-bold text-charcoal mb-1">
                {{ __('home.news2_title') }}
              </h3>
<p class="text-xs text-charcoal-muted leading-relaxed font-light mt-2 text-justify">
                {{ __('home.news2_text') }}
              </p>
</div>
<div class="pt-4 border-t border-stone-light/60 mt-4 flex items-center justify-between">
<a class="text-xs uppercase tracking-widest font-semibold text-primary hover:text-gold-accent flex items-center gap-1 transition-colors" href="#contact">
<span class="">{{ __('home.news2_cta') }}</span>
<span class="material-symbols-outlined text-sm">arrow_forward</span>
</a>
</div>
</article>
<!-- Block 2: Entraide & Solidarité -->
<article class="bg-white rounded-2xl p-7 border border-stone-light shadow-sm flex flex-col justify-between flex-1 group">
<div>
<div class="flex items-center justify-between mb-3">
<span class="text-[11px] font-semibold uppercase tracking-wider px-2.5 py-0.5 rounded bg-primary/10 text-primary font-garamond">
                  {{ __('home.news3_badge') }}
                </span>
<span class="text-xs text-charcoal-muted">{{ __('home.news3_date') }}</span>
</div>
<h3 class="font-ethiopic text-lg font-bold text-charcoal mb-1">
                {{ __('home.news3_title') }}
              </h3>
<p class="text-xs text-charcoal-muted leading-relaxed font-light mt-2 text-justify">
                {{ __('home.news3_text') }}
              </p>
</div>
<div class="pt-4 border-t border-stone-light/60 mt-4 flex items-center justify-between">
<a class="text-xs uppercase tracking-widest font-semibold text-primary hover:text-gold-accent flex items-center gap-1 transition-colors" href="#contact">
<span class="">{{ __('home.news3_cta') }}</span>
<span class="material-symbols-outlined text-sm">arrow_forward</span>
</a>
</div>
</article>
</div>
</div>
</div>
</section>
<!-- 7. SECTION 07: PHOTO GALLERY (#galerie) -->
<section class="py-28 lg:py-36 bg-surface-low border-b border-stone-light" id="galerie">
<div class="max-w-6xl mx-auto px-6 lg:px-12">
<div class="text-center max-w-3xl mx-auto mb-16 lg:mb-20">
<span class="text-xs uppercase tracking-[0.25em] text-primary font-semibold block mb-3 font-garamond">
          {{ __('home.gallery_eyebrow') }}
        </span>
<h2 class="font-ethiopic text-3xl sm:text-4xl lg:text-5xl font-bold text-charcoal mb-4">
          {{ __('home.gallery_title') }}
        </h2>
<p class="font-garamond italic text-2xl text-charcoal-muted">
          {{ __('home.gallery_subtitle') }}
        </p>
<div class="w-16 h-px bg-gold-accent mx-auto mt-6"></div>
</div>
<!-- Sanctuary Editorial Horizontal Composition Grid -->
<div class="grid grid-cols-1 md:grid-cols-12 gap-6">
<!-- Large Wide Item: Procession & Priests (7 cols) -->
<div class="md:col-span-7 h-80 sm:h-96 rounded-2xl overflow-hidden relative group shadow-sm">
<img alt="Ministres sacrés lors de la liturgie" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" src="https://lh3.googleusercontent.com/aida-public/AB6AXuALNa8IVKSfIOy8ZgFVQV4-WRivb92bYoVbOlO-nTClY96szT5TNXyRe9l8HokCJOxWvJKcJBcxUrrwgyqiAk-ytfckokvA7AAvrQHs8kILqtBdkpQAibVIpZfeHuhXtl81mcbVLpH1GRXwxWzII0CwPi_Hpb_EcJ8fhluI2NPugdsFmz6pDXI5L6g99ZhIDLmooIdPCmPyOYyNBM5Ml83PwdyN0VtE-rJILc0LeIg">
<div class="absolute inset-0 bg-gradient-to-t from-charcoal/90 via-charcoal/20 to-transparent flex flex-col justify-end p-6 sm:p-8 text-white">
<span class="font-garamond text-xs uppercase tracking-widest text-gold-accent mb-1">{{ __('home.gallery1_tag') }}</span>
<h4 class="font-ethiopic font-semibold text-lg sm:text-xl">{{ __('home.gallery1_title') }}</h4>
<p class="text-xs text-stone-300 font-light mt-1">{{ __('home.gallery1_text') }}</p>
</div>
</div>
<!-- Item 2: Liturgical Umbrella (5 cols) -->
<div class="md:col-span-5 h-80 sm:h-96 rounded-2xl overflow-hidden relative group shadow-sm">
<img alt="Ombrelle liturgique Dibab" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" src="https://lh3.googleusercontent.com/aida-public/AB6AXuD5XtVlWmjsTawghyqQLmjLrtMtNlMQ9du1UWATUeABIqDWyjXYjVDeu-D6zbohE0TCA6ZPzLL4OUi3B4a-sO-TpE5yvoy9hX4OAES42qJCsE_b6R0Wywohg-7_QQoJuGM0znSz8Kt2S_wjPT8egeSAhlXHfFVTKh-dvykPQYmV54p5dzRyUX7VV3sSUby3lwXwcETUbJWIIQgXqqIJtxxbvYieVGFELfv61EBjMYk">
<div class="absolute inset-0 bg-gradient-to-t from-charcoal/90 via-charcoal/20 to-transparent flex flex-col justify-end p-6 sm:p-8 text-white">
<span class="font-garamond text-xs uppercase tracking-widest text-gold-accent mb-1">{{ __('home.gallery2_tag') }}</span>
<h4 class="font-ethiopic font-semibold text-lg sm:text-xl">{{ __('home.gallery2_title') }}</h4>
<p class="text-xs text-stone-300 font-light mt-1">{{ __('home.gallery2_text') }}</p>
</div>
</div>
<!-- Item 3: Choir & Sistra (6 cols) -->
<div class="md:col-span-6 h-80 rounded-2xl overflow-hidden relative group shadow-sm">
<img alt="Authentic Eritrean Orthodox Tewahedo Church sacred liturgical chant and choir singing hymns of Saint Yared" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDbky9yJvPQ0D89a7797kcJ5PINUvkF1-C2yK0JgVnvIA6vHaRsNz8TNESQNiJfdjcDxqzgVOEsPKN89CqZ0jkZUICtu3ccsWic4jLEQidDOcLzS9ISfuNdYtgTSRiPKmCYsoRsvaQ3AAfH2MuHldAgnTqPnzlf9vBEZwyBz-0sHLihS1MF9kGFZH2y910qSDmOM3mDhiPdACgPOeWwLZK75qWuIFNzSOetpbp3EZY">
<div class="absolute inset-0 bg-gradient-to-t from-charcoal/90 via-charcoal/20 to-transparent flex flex-col justify-end p-6 sm:p-8 text-white">
<span class="font-garamond text-xs uppercase tracking-widest text-gold-accent mb-1">{{ __('home.gallery3_tag') }}</span>
<h4 class="font-ethiopic font-semibold text-lg">{{ __('home.gallery3_title') }}</h4>
<p class="text-xs text-stone-300 font-light mt-1">{{ __('home.gallery3_text') }}</p>
</div>
</div>
<!-- Item 4: Prayer & Sacred Scripture (6 cols) -->
<div class="md:col-span-6 h-80 rounded-2xl overflow-hidden relative group shadow-sm">
<img alt="Altar with Ge'ez cross and manuscripts" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBzUtpn5e9tYt9RnyFxyS5rxCmKqzrJiPgJkqCCzxdsG_dIZ_qsZKhgXQa-c4f7WGH7iz-XihVdsncPFQJ8wON_26LDqMPzkgxNtgRqx8dn5ezAdN1amsURT0LgRliRI6NYzYD4J3g93ENLwEaKEkn-0CAV2PUYNCM6umpM7ochqVw4injmSuFD9POm_-EyzH5fMPaSnTFeFA742iliI_FQxc82aFR5M9UmCxX0AJw">
<div class="absolute inset-0 bg-gradient-to-t from-charcoal/90 via-charcoal/20 to-transparent flex flex-col justify-end p-6 sm:p-8 text-white">
<span class="font-garamond text-xs uppercase tracking-widest text-gold-accent mb-1">{{ __('home.gallery4_tag') }}</span>
<h4 class="font-ethiopic font-semibold text-lg">{{ __('home.gallery4_title') }}</h4>
<p class="text-xs text-stone-300 font-light mt-1">{{ __('home.gallery4_text') }}</p>
</div>
</div>
</div>
</div>
</section>
<!-- 8. SECTION 08: VISIT & CONTACT (#contact) -->
<section class="py-28 lg:py-36 bg-surface" id="contact">
<div class="max-w-6xl mx-auto px-6 lg:px-12">
<!-- Headline -->
<div class="text-center max-w-3xl mx-auto mb-16 lg:mb-20">
<span class="text-xs uppercase tracking-[0.25em] text-primary font-semibold block mb-3 font-garamond">
          {{ __('home.contact_eyebrow') }}
        </span>
<h2 class="font-ethiopic text-3xl sm:text-4xl lg:text-5xl font-bold text-charcoal mb-4">
          {{ __('home.contact_title') }}
        </h2>
<p class="font-garamond italic text-2xl text-charcoal-muted">
          {{ __('home.contact_subtitle') }}
        </p>
<div class="w-16 h-px bg-gold-accent mx-auto mt-6"></div>
</div>
<!-- Sanctuary-Style 3 Clean Info Blocks -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
<!-- Info 1: Horaires -->
<div class="bg-white p-8 rounded-2xl border border-stone-light shadow-sm text-center">
<div class="w-12 h-12 rounded-full bg-primary/10 text-primary mx-auto flex items-center justify-center mb-4">
<span class="material-symbols-outlined text-2xl">schedule</span>
</div>
<h3 class="font-ethiopic text-lg font-bold text-charcoal mb-1">{{ __('home.contact_info1_title') }}</h3>
<h4 class="font-garamond italic text-sm text-primary mb-3">{{ __('home.contact_info1_sub') }}</h4>
<p class="text-xs text-charcoal-muted leading-relaxed font-light">
            {!! __('home.contact_info1_text') !!}
          </p>
</div>
<!-- Info 2: Lieu & Accès -->
<div class="bg-white p-8 rounded-2xl border border-stone-light shadow-sm text-center">
<div class="w-12 h-12 rounded-full bg-gold-accent/20 text-gold-accent mx-auto flex items-center justify-center mb-4">
<span class="material-symbols-outlined text-2xl">location_on</span>
</div>
<h3 class="font-ethiopic text-lg font-bold text-charcoal mb-1">{{ __('home.contact_info2_title') }}</h3>
<h4 class="font-garamond italic text-sm text-primary mb-3">{{ __('home.contact_info2_sub') }}</h4>
<p class="text-xs text-charcoal-muted leading-relaxed font-light">
            {!! __('home.contact_info2_text') !!}
          </p>
</div>
<!-- Info 3: Contact Pastoral -->
<div class="bg-white p-8 rounded-2xl border border-stone-light shadow-sm text-center">
<div class="w-12 h-12 rounded-full bg-primary/10 text-primary mx-auto flex items-center justify-center mb-4">
<span class="material-symbols-outlined text-2xl">mail</span>
</div>
<h3 class="font-ethiopic text-lg font-bold text-charcoal mb-1">{{ __('home.contact_info3_title') }}</h3>
<h4 class="font-garamond italic text-sm text-primary mb-3">{{ __('home.contact_info3_sub') }}</h4>
<p class="text-xs text-charcoal-muted leading-relaxed font-light">
            {!! __('home.contact_info3_text') !!}
          </p>
</div>
</div>
<!-- Map & Elegant Contact Form Grid -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-stretch">
<!-- Google Maps Styled Frame Placeholder (5 cols) -->
<div class="lg:col-span-5 rounded-2xl overflow-hidden border border-stone-light shadow-sm relative min-h-[380px] bg-stone-light/40 flex items-center justify-center text-center p-8">
<div class="absolute inset-0 bg-cover bg-center filter grayscale-[0.3] contrast-[0.95]" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuBAc-pguaepgj3jDEJxJ5FVYBkDPjUSnzOH5sFs-5jKBBaoKGqkXSGtB4lad9RJCEGyoK2CPpohqUZmiCjKXaAujRZfYJ_NEdN9OezTLYRbnTsiZZiYWjeds_XFT3iHab1JmcwfG_ceX1nowKX5aNogc64CYcrq1QiFzMs8QvZjuKbMS7g8wQTld7yU6SJVvU-8-TWJp-8lymPA7uzDvBY8-LzPkOeRCcwd8j60b5w');"></div>
<div class="absolute inset-0 bg-surface/75 backdrop-blur-[2px]"></div>
<div class="relative z-10 max-w-xs">
<div class="w-12 h-12 rounded-full bg-primary text-white mx-auto flex items-center justify-center mb-3 shadow">
<span class="material-symbols-outlined text-2xl">church</span>
</div>
<h4 class="font-ethiopic font-bold text-lg text-charcoal mb-1">{{ __('home.map_title') }}</h4>
<p class="font-garamond italic text-sm text-charcoal-muted mb-4">{{ __('home.map_sub') }}</p>
<a class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white text-charcoal text-xs font-medium uppercase tracking-wider shadow-sm hover:shadow border border-stone-light transition-all" href="https://maps.google.com/?q=115+Rue+Bonnat,+31400+Toulouse" rel="noopener noreferrer" target="_blank">
<span class="">{{ __('home.map_cta') }}</span>
<span class="material-symbols-outlined text-sm">open_in_new</span>
</a>
</div>
</div>
<!-- Reverent Sanctuary Form (7 cols) -->
<div class="lg:col-span-7 bg-white rounded-2xl p-8 sm:p-10 border border-stone-light shadow-sm">
<div class="mb-6">
<h3 class="font-ethiopic text-2xl font-bold text-charcoal mb-1">{{ __('home.form_title') }}</h3>
<p class="font-garamond italic text-base text-charcoal-muted">{{ __('home.form_sub') }}</p>
</div>
<form class="space-y-4" id="contactForm" onsubmit="event.preventDefault(); document.getElementById('formConfirm').classList.remove('hidden');">
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
<div>
<label class="block text-[11px] font-semibold uppercase tracking-wider text-charcoal-muted mb-1">{{ __('home.form_name') }}</label>
<input class="w-full px-4 py-2.5 rounded-lg bg-surface text-charcoal border border-stone-light text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="{{ __('home.form_name_ph') }}" required="" type="text">
</div>
<div>
<label class="block text-[11px] font-semibold uppercase tracking-wider text-charcoal-muted mb-1">{{ __('home.form_email') }}</label>
<input class="w-full px-4 py-2.5 rounded-lg bg-surface text-charcoal border border-stone-light text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all" placeholder="{{ __('home.form_email_ph') }}" required="" type="email">
</div>
</div>
<div>
<label class="block text-[11px] font-semibold uppercase tracking-wider text-charcoal-muted mb-1">{{ __('home.form_subject') }}</label>
<select class="w-full px-4 py-2.5 rounded-lg bg-surface text-charcoal border border-stone-light text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all">
<option value="general">{{ __('home.form_opt_general') }}</option>
<option value="liturgie">{{ __('home.form_opt_liturgy') }}</option>
<option value="bapteme">{{ __('home.form_opt_baptism') }}</option>
<option value="catechisme">{{ __('home.form_opt_catechism') }}</option>
<option value="don">{{ __('home.form_opt_don') }}</option>
</select>
</div>
<div>
<label class="block text-[11px] font-semibold uppercase tracking-wider text-charcoal-muted mb-1">{{ __('home.form_message') }}</label>
<textarea class="w-full px-4 py-2.5 rounded-lg bg-surface text-charcoal border border-stone-light text-xs focus:ring-1 focus:ring-primary focus:border-primary outline-none transition-all resize-none" placeholder="{{ __('home.form_message_ph') }}" required="" rows="4"></textarea>
</div>
<div class="hidden p-4 rounded-lg bg-[#EBF3ED] text-[#245934] text-xs flex items-center gap-2" id="formConfirm">
<span class="material-symbols-outlined text-base">check_circle</span>
<span class="">{{ __('home.form_success') }}</span>
</div>
<button class="w-full py-3.5 rounded-full bg-primary hover:bg-[#8d343c] text-white text-xs font-medium uppercase tracking-[0.2em] transition-all duration-300 shadow flex items-center justify-center gap-2" type="submit">
<span class="material-symbols-outlined text-base">send</span>
<span class="">{{ __('home.form_submit') }}</span>
</button>
</form>
</div>
</div>
</div>
</section>
<!-- 9. SECTION 09: INSTITUTIONAL SANCTUARY FOOTER -->
<footer class="w-full bg-[#762B32] text-[#F8F5EF] pt-20 pb-12 border-t border-gold-accent/30 relative overflow-hidden">
<div class="max-w-7xl mx-auto px-6 lg:px-12">
<!-- Top Emblem & Dignified Dedication -->
<div class="flex flex-col items-center text-center pb-14 border-b border-white/10">
<div class="mb-5 p-3 rounded-full bg-white/10 backdrop-blur-sm border border-white/20">
<img alt="Croix Officielle Érythréenne" class="h-14 w-auto object-contain filter drop-shadow" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDZyxSnJIA0kGjo5EY3ukYIen4mgtBIXyZU7DGCaB9whLnHWqN-7TYQ2AKj3foQqDWeT_kfrp_pzAF_pVaJ2WMmrLB3ewomFtFj9WoZF6YXElijlUAGWc1NKqQT_1tQr0TdqqjeBRw_z-c5ZbELP3AM1ZISsN6jb0ijlcr089O_QEtjHh21eFVqf9dLW_zEd8DY3_yL2w35kdbQbyLVl2lfGitKcuMQOpfvHtLnmnG6PJ7aYgVNhfmR">
</div>
<h3 class="font-ethiopic text-2xl sm:text-3xl font-bold tracking-tight text-white mb-2 max-w-4xl">
          {{ __('home.footer_title') }}
        </h3>
<p class="font-garamond italic text-lg sm:text-xl text-stone-200 font-normal max-w-3xl mb-4">
          {{ __('home.footer_sub') }}
        </p>
<p class="font-garamond text-xs sm:text-sm tracking-[0.25em] uppercase text-gold-accent font-semibold">
          {{ __('home.footer_motto') }}
        </p>
</div>
<!-- Links & Institutional Columns -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-10 py-14 border-b border-white/10 text-xs font-light">
<!-- Col 1: Navigation -->
<div>
<h4 class="font-garamond italic text-base text-white font-medium mb-4">{{ __('home.footer_nav') }}</h4>
<ul class="space-y-2 text-stone-300">
<li class=""><a class="hover:text-gold-accent transition-colors" href="#accueil">{{ __('home.mobile_home') }}</a></li>
<li class=""><a class="hover:text-gold-accent transition-colors" href="#a-propos">{{ __('home.mobile_about') }}</a></li>
<li class=""><a class="hover:text-gold-accent transition-colors" href="#foi">{{ __('home.mobile_faith') }}</a></li>
<li class=""><a class="hover:text-gold-accent transition-colors" href="#celebrations">{{ __('home.mobile_services') }}</a></li>
<li class=""><a class="hover:text-gold-accent transition-colors" href="#actualites">{{ __('home.mobile_news') }}</a></li>
</ul>
</div>
<!-- Col 2: Services liturgiques -->
<div>
<h4 class="font-garamond italic text-base text-white font-medium mb-4">{{ __('home.footer_ministries') }}</h4>
<ul class="space-y-2 text-stone-300">
<li class=""><a class="hover:text-gold-accent transition-colors" href="#celebrations">{{ __('home.footer_min_liturgy') }}</a></li>
<li class=""><a class="hover:text-gold-accent transition-colors" href="#actualites">{{ __('home.footer_min_school') }}</a></li>
<li class=""><a class="hover:text-gold-accent transition-colors" href="#celebrations">{{ __('home.footer_min_sacrament') }}</a></li>
<li class=""><a class="hover:text-gold-accent transition-colors" href="#foi">{{ __('home.footer_min_chants') }}</a></li>
<li class=""><a class="hover:text-gold-accent transition-colors" href="#contact">{{ __('home.footer_min_confession') }}</a></li>
</ul>
</div>
<!-- Col 3: Informations pratiques -->
<div>
<h4 class="font-garamond italic text-base text-white font-medium mb-4">{{ __('home.footer_location') }}</h4>
<p class="text-stone-300 leading-relaxed mb-3">
            {!! __('home.footer_address') !!}
          </p>
<div class="flex items-center gap-3 text-gold-accent">
<a aria-label="Email" class="hover:text-white transition-colors" href="mailto:eriorthocommunaute@gmail.com">
<span class="material-symbols-outlined text-lg">mail</span>
</a>
<a aria-label="Localisation" class="hover:text-white transition-colors" href="#contact">
<span class="material-symbols-outlined text-lg">location_on</span>
</a>
</div>
</div>
<!-- Col 4: Soutenir / Don -->
<div>
<h4 class="font-garamond italic text-base text-white font-medium mb-4">{{ __('home.footer_donate') }}</h4>
<p class="text-stone-300 leading-relaxed mb-4 text-justify">
            {{ __('home.footer_donate_text') }}
          </p>
<a class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-gold-accent text-charcoal text-[11px] font-semibold uppercase tracking-wider hover:bg-white transition-colors" href="#contact">
<span class="">{{ __('home.donate_short') }}</span>
<span class="material-symbols-outlined text-sm">favorite</span>
</a>
</div>
</div>
<!-- Copyright Bar -->
<div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-[11px] text-stone-400 font-light gap-4">
<p class="">{{ __('home.footer_copyright', ['year' => date('Y')]) }}</p>
<p class="font-garamond italic">{{ __('home.footer_note') }}</p>
</div>
</div>
</footer>
<!-- 10. JAVASCRIPT: STICKY HEADER, ACTIVE NAV OBSERVER & MOBILE MENU -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
      const header = document.getElementById('siteHeader');
      const mobileBtn = document.getElementById('mobileMenuBtn');
      const mobileDrawer = document.getElementById('mobileDrawer');
      const navLinks = document.querySelectorAll('.nav-item');
      const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');
      const brandText = document.querySelector('.header-brand-text');
      const brandSub = document.querySelector('.header-brand-sub');
      const langBox = document.querySelector('.lang-box');

      // Scroll observer for Sticky Header Transition (Transparent -> Sanctuary Warm Ivory)
      const onScroll = () => {
        if (window.scrollY > 50) {
          header.classList.add('nav-scrolled');
          header.classList.remove('text-white');
          header.classList.add('text-charcoal');
          if (langBox) {
            langBox.classList.remove('border-white/20');
            langBox.classList.add('border-stone-light');
          }
        } else {
          header.classList.remove('nav-scrolled');
          header.classList.remove('text-charcoal');
          header.classList.add('text-white');
          if (langBox) {
            langBox.classList.add('border-white/20');
            langBox.classList.remove('border-stone-light');
          }
        }
      };

      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();

      // Mobile Menu Toggle
      mobileBtn?.addEventListener('click', () => {
        mobileDrawer.classList.toggle('hidden');
      });

      mobileNavLinks.forEach(link => {
        link.addEventListener('click', () => {
          mobileDrawer.classList.add('hidden');
        });
      });

      // Active Section Highlighting
      const sections = document.querySelectorAll('section[id]');
      window.addEventListener('scroll', () => {
        const scrollY = window.pageYOffset + 120;
        sections.forEach(current => {
          const sectionHeight = current.offsetHeight;
          const sectionTop = current.offsetTop;
          const sectionId = current.getAttribute('id');
          if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
            navLinks.forEach(link => {
              if (link.getAttribute('href') === `#${sectionId}`) {
                link.classList.add('text-gold-accent', 'font-semibold');
              } else {
                link.classList.remove('text-gold-accent', 'font-semibold');
              }
            });
          }
        });
      }, { passive: true });
    });
  </script>


</body></html>
