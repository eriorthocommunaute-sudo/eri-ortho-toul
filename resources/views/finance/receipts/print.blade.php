<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ($blankReceipt ?? false) ? 'Reçu vierge' : 'Reçu ' . $receipt->reference }}</title>
    <style>
        @page { size: A4; margin: 11mm 13.5mm 6mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #252525; font-family: Arial, Helvetica, sans-serif; font-size: 12px; }
        .receipt { max-width: 760px; margin: 0 auto; padding: 16px; border: 1px solid #e7e3da; }

        .header { position: relative; }
        .header .logo { position: absolute; left: 17px; top: -2px; width: 86px; height: 86px; object-fit: contain; }
        .hdr-block { padding-left: 105px; text-align: center; }
        .hdr-ti { margin: 0 0 8px; color: #a98654; font-size: 17px; font-weight: bold; line-height: 1.2; }
        .hdr-fr { margin: 0 0 8px; color: #223645; font-weight: bold; font-size: 16px; line-height: 1.3; text-transform: uppercase; }
        .hdr-fr .l2 { display: block; font-size: 12.5px; margin-top: 8px; }
        .hdr-sub { margin: 0; color: #677479; font-size: 10.5px; letter-spacing: 1px; text-transform: uppercase; line-height: 1.3; }
        .hdr-sub .sep { margin: 0 6px; }
        .title { margin: 18px 0 0; text-align: center; color: #223645; font-size: 26px; font-weight: bold; line-height: 1.15; }
        .title .sep { color: #a98654; font-weight: normal; font-size: 20px; margin: 0 6px; }
        .title .ti { color: #a98654; font-size: 20px; }
        .rule { height: 3px; background: #a98654; margin-top: 13px; }

        .intro { text-align: center; color: #2e383f; font-size: 11.5px; margin: 13px 0 0; }
        .intro p { margin: 0 0 19px; }
        .intro p:last-child { margin-bottom: 0; }

        table { width: 100%; border-collapse: collapse; margin-top: 18.5px; }
        td { border: 1px solid #d9d4cc; padding: 9px 10px; vertical-align: top; }
        td:first-child { width: 30%; background: #f1f0ea; }
        .lbl { display: block; }
        .lbl .fr { display: block; color: #223645; font-weight: bold; font-size: 11.5px; text-transform: uppercase; letter-spacing: .2px; line-height: 1.25; }
        .lbl .ti { display: block; color: #a98654; font-style: italic; font-size: 11.5px; margin-top: 4px; line-height: 1.25; }
        .value { color: #223645; font-size: 12px; line-height: 1.35; }

        .amount-block { margin-top: 17px; text-align: center; }
        .amount-label { color: #223645; font-weight: bold; font-size: 11.5px; text-transform: uppercase; letter-spacing: .2px; }
        .amount-label .sep { color: #a98654; font-weight: normal; margin: 0 5px; }
        .amount-label .ti { color: #a98654; font-style: italic; text-transform: none; letter-spacing: 0; }
        .amount-box { margin-top: 11px; border: 2px solid #a98654; background: #f5f3ed; padding: 31px 20px; }
        .amount-line { display: inline-block; width: 164px; border-bottom: 1px solid #bebebe; line-height: 0; margin-right: 10px; }
        .euro { color: #223645; font-weight: bold; font-size: 21px; }
        .amount-num { color: #223645; font-weight: bold; font-size: 21px; }

        .note { margin-top: 19px; }
        .note-label { color: #223645; font-weight: bold; font-size: 11.5px; text-transform: uppercase; letter-spacing: .2px; }
        .note-label .ti { color: #a98654; font-style: italic; text-transform: none; letter-spacing: 0; }
        .note-box { margin-top: 7px; border: 1px solid #d9d4cc; background: #fff; min-height: 65px; padding: 8px 10px; font-size: 12px; color: #223645; }

        .signature { display: flex; justify-content: space-between; padding: 0 13%; margin-top: 80px; }
        .sig-col { width: 38%; text-align: center; }
        .sig-line { border-top: 1px solid #8a9397; }
        .sig-fr { display: block; margin-top: 7px; color: #223645; font-weight: bold; font-size: 9.5px; text-transform: uppercase; letter-spacing: .3px; }
        .sig-ti { display: block; margin-top: 2px; color: #a98654; font-style: italic; font-size: 11px; }

        .footer { margin-top: 46px; text-align: center; color: #677479; font-size: 10.5px; }
        .actions { text-align: center; margin: 18px; }
        .actions button, .actions a { padding: 9px 16px; }
        @media print {
            .no-print { display: none !important; }
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .receipt { border: 0; padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
@php
    $blankReceipt = $blankReceipt ?? false;
    $typeLabels = [
        'cotisation' => 'Cotisation / ኣበርክቶ',
        'cotisation_fondatrice' => 'Cotisation fondatrice de 200 € / ናይ መስረቲ ኣበርክቶ 200 €',
        'don' => 'Don / ውህበት',
        'offrande' => 'Offrande / መባእ',
    ];
    $payer = $blankReceipt ? '' : $receipt->payer_name;
    if (! $blankReceipt && $receipt->member) {
        $payer = trim(($receipt->member->title ? $receipt->member->title . ' ' : '') . $receipt->member->given_name . ' ' . $receipt->member->family_name);
    }
@endphp
<div class="receipt">
    <header class="header">
        <img class="logo" src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="">
        <div class="hdr-block">
            <p class="hdr-ti">ኦርቶዶክስ ተዋሕዶ ቤተ ክርስቲያን ኤርትራ ባእታ ማርያም ቱሉዝ ፈረንሳ</p>
            <p class="hdr-fr">Communauté Orthodoxe Érythréenne<span class="l2">de Toulouse et de ses environs</span></p>
            <p class="hdr-sub">Document associatif <span class="sep">|</span> ሰነድ ማሕበር</p>
        </div>
        <div class="title">REÇU <span class="sep">/</span> <span class="ti">ቅብሊት</span></div>
    </header>
    <div class="rule"></div>
    <div class="intro">
        <p>La communauté certifie avoir reçu le montant indiqué ci-dessous.</p>
        <p>ማሕበር ኣብ ታሕቲ ዝተጠቕሰ ገንዘብ ከም ዝተቐበለት ተረጋግጽ።</p>
    </div>
    <table>
        <tr>
            <td><span class="lbl"><span class="fr">N° Reçu</span><span class="ti">ቁ. ቅብሊት</span></span></td>
            <td>@if(! $blankReceipt)<span class="value">{{ $receipt->reference }}</span>@endif</td>
        </tr>
        <tr>
            <td><span class="lbl"><span class="fr">Type de recette</span><span class="ti">ዓይነት ኣታዊ</span></span></td>
            <td>@if(! $blankReceipt)<span class="value">{{ $typeLabels[$receipt->receipt_type ?? 'cotisation'] ?? 'Cotisation / ኣበርክቶ' }}</span>@endif</td>
        </tr>
        <tr>
            <td><span class="lbl"><span class="fr">Titre / Nom</span><span class="ti">ማዕረግ / ሽም</span></span></td>
            <td>@if(! $blankReceipt)<span class="value">{{ $payer ?: 'Anonyme / ዘይተገልጸ' }}</span>@endif</td>
        </tr>
        @if($blankReceipt || $receipt->member)
        <tr>
            <td><span class="lbl"><span class="fr">Identifiant membre</span><span class="ti">መለለዪ ኣባል</span></span></td>
            <td>@if(! $blankReceipt)<span class="value">{{ $receipt->member->member_code }}</span>@endif</td>
        </tr>
        @endif
        <tr>
            <td><span class="lbl"><span class="fr">Date de paiement</span><span class="ti">ዕለት ክፍሊት</span></span></td>
            <td>@if(! $blankReceipt)<span class="value">{{ $receipt->payment_date->format('d/m/Y') }}</span>@endif</td>
        </tr>
        <tr>
            <td><span class="lbl"><span class="fr">Motif</span><span class="ti">ምኽንያት</span></span></td>
            <td>@if(! $blankReceipt)<span class="value">{{ $receipt->description ?: ($receipt->contribution?->reference ?? 'Contribution à la communauté') }}</span>@endif</td>
        </tr>
        <tr>
            <td><span class="lbl"><span class="fr">Mode de paiement</span><span class="ti">ኣገባብ ክፍሊት</span></span></td>
            <td>@if(! $blankReceipt)<span class="value">{{ __('app.'.$receipt->payment_method) }}</span>@endif</td>
        </tr>
        <tr>
            <td><span class="lbl"><span class="fr">Référence financière</span><span class="ti">መወከሲ ገንዘብ</span></span></td>
            <td>@if(! $blankReceipt)<span class="value">{{ $receipt->financial_reference }}</span>@endif</td>
        </tr>
    </table>
    <div class="amount-block">
        <div class="amount-label">Montant reçu <span class="sep">/</span> <span class="ti">ዝተቐበልናዮ መጠን</span></div>
        <div class="amount-box">
            @if($blankReceipt)<span class="amount-line"></span><span class="euro">€</span>@else<strong class="amount-num">{{ number_format($receipt->amount, 2, ',', ' ') }} €</strong>@endif
        </div>
    </div>
    <div class="note">
        <div class="note-label">Note <span class="sep">/</span> <span class="ti">መብርሂ</span></div>
        <div class="note-box">@if(! $blankReceipt && $receipt->notes){{ $receipt->notes }}@endif</div>
    </div>
    <div class="signature">
        <div class="sig-col"><div class="sig-line"></div><span class="sig-fr">Responsable</span><span class="sig-ti">ሓላፊ</span></div>
        <div class="sig-col"><div class="sig-line"></div><span class="sig-fr">Signature</span><span class="sig-ti">ፊርማ</span></div>
    </div>
    <div class="footer">Communauté Orthodoxe Érythréenne de Toulouse et de ses environs</div>
</div>
<div class="actions no-print">
    <button type="button" onclick="window.print()">Imprimer / ሕተም</button>
    <a href="{{ route('receipts.index') }}">Retour / ተመለስ</a>
</div>
<script>window.addEventListener('load', () => window.print());</script>
</body>
</html>
