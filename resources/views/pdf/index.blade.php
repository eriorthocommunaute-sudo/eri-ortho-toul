@extends('layouts.app')
@section('content')
<h1>{{ __('app.pdf_export') }}</h1>

<p style="margin-bottom: 1.5rem; color: #64748b;">{{ __('app.pdf_export_description') }}</p>

{{-- Financial register generator --}}
<section class="panel" style="margin-bottom: 2rem;">
    <div class="panelhead">
        <div>
            <h2>{{ __('app.financial_register') }} / መዝገብ ገንዘብ</h2>
            <p class="muted">{{ __('app.select_period_type_and_year') }}</p>
        </div>
        <a class="button" href="{{ route('financial-register.index') }}">{{ __('app.generate_new_register') }}</a>
    </div>

    <p style="margin: .5rem 0 1rem; color: #64748b; font-size: .9rem;">
        {{ __('app.financial_register_description') }}
    </p>

    <div class="flex gap-2" style="flex-wrap: wrap;">
        <a class="button" href="{{ route('financial-register.list') }}">{{ __('app.financial_register_documents') }}</a>
    </div>
</section>

<div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1rem;">
    @foreach($templates as $key => $template)
    <div class="card" style="padding: 1.5rem; background: white; border-radius: .5rem; border: 1px solid #e2e8f0;">
        <h3 style="margin: 0 0 .5rem;">{{ $template['label'] }}</h3>
        <p style="margin: 0 0 1rem; color: #64748b; font-size: .9rem;">{{ $template['description'] }}</p>
        
        @if($key === 'financial_register')
        <a class="button" href="{{ route('financial-register.index') }}">{{ __('app.open_print') }}</a>
        @elseif(in_array($key, ['member_list', 'financial_report']))
        <a class="button" href="{{ route('pdf.print', ['template' => $key]) }}" target="_blank">{{ __('app.open_print') }}</a>
        @else
        <p style="margin: 0; font-size: .85rem; color: #94a3b8;">{{ __('app.open_from_detail') }}</p>
        @endif
    </div>
    @endforeach
</div>

<hr style="margin: 2rem 0; border-color: #e2e8f0;">

<h2>{{ __('app.how_to_pdf') }}</h2>
<ul style="line-height: 1.8; color: #475569;">
    <li><strong>{{ __('app.pdf_step1') }}</strong> - {{ __('app.pdf_step1_desc') }}</li>
    <li><strong>{{ __('app.pdf_step2') }}</strong> - {{ __('app.pdf_step2_desc') }}</li>
    <li><strong>{{ __('app.pdf_step3') }}</strong> - {{ __('app.pdf_step3_desc') }}</li>
    <li><strong>{{ __('app.pdf_step4') }}</strong> - {{ __('app.pdf_step4_desc') }}</li>
</ul>

<div style="margin-top: 2rem; padding: 1rem; background: #f0fdfa; border-radius: .5rem; border: 1px solid #86efac;">
    <strong style="color: #065f46;">{{ __('app.pdf_tip') }}</strong>
    <p style="margin: .5rem 0 0; color: #065f46; font-size: .9rem;">{{ __('app.pdf_tip_desc') }}</p>
</div>
@endsection