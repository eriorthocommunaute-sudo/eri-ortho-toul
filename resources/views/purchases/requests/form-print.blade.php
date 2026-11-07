<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>DEMANDE D'ACHAT ET DE DÉPENSE / ሕቶ ግዝኣትን ወጻኢታትን</title>
<style>
@page { size: A4 portrait; margin: 7mm; }
* { box-sizing: border-box; }
body { margin:0; color:#243447; font:9.5pt Arial, Calibri, sans-serif; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
.screen { max-width:210mm; margin:20px auto; }
.sheet { height:282mm; min-height:0; page-break-after:always; break-after:page; overflow:hidden; }
.sheet:last-child { page-break-after:auto; break-after:auto; }
.header { background:#F6F1E7; padding:5mm 7mm 3mm; text-align:center; position:relative; margin-bottom:3mm; }
.cross { position:absolute; top:4mm; width:15mm; height:15mm; object-fit:contain; }
.cross.left { left:7mm; } .cross.right { right:7mm; }
.assoc { font: bold 12.5pt Georgia,serif; color:#243447; margin:0 20mm 1mm; }
.assoc-ti { font: bold 9pt Georgia,serif; color:#243447; margin-bottom:1.5mm; }
.title { font:bold 11.5pt Arial,sans-serif; color:#7A2E2E; margin-bottom:1mm; }
.docline { font:italic 8.5pt Arial,sans-serif; color:#243447; }
.refline { margin-top:2.5mm; font-size:8.5pt; color:#243447; }
.section { margin:2.5mm 0 1.2mm; color:#7A2E2E; font-weight:bold; font-size:9.5pt; border-bottom:1px solid #D8D0C0; padding-bottom:0.8mm; }
table { width:100%; border-collapse:collapse; table-layout:fixed; margin-bottom:2.5mm; }
td,th { border:1px solid #B9B2A3; padding:1.8mm 2mm; vertical-align:top; }
th { background:#243447; color:#fff; font-size:8.5pt; }
.label { background:#F6F1E7; width:29%; font-weight:bold; font-size:7.8pt; }
.ti { display:block; color:#7A2E2E; font-weight:normal; font-size:7.2pt; margin-top:0.5mm; }
.blank { min-height:6.5mm; }
.blank.tall { min-height:13mm; }
.item-row td { height:8.5mm; }
.amount { font-weight:bold; }
.category-table { margin-top:1mm; margin-bottom:2.5mm; }
.category-table td { width:50%; padding:1.5mm 2mm; font-size:7.8pt; vertical-align:middle; }
.category-option { display:inline-block; margin-right:1.5mm; font-size:11pt; }
.category-label { font-weight:normal; }
.category-ti { display:block; color:#7A2E2E; font-size:6.8pt; margin-left:5mm; margin-top:0.3mm; }
.signature { height:13mm; }
.footer { border-top:1px solid #D8D0C0; padding-top:1.5mm; margin-top:2.5mm; text-align:center; font-size:7pt; color:#243447; }
.no-print { text-align:center; margin:15px; }
button { background:#243447; color:#fff; border:0; border-radius:4px; padding:10px 18px; }
@media print { .screen { margin:0; max-width:none; } .no-print { display:none; } .sheet { height:282mm; min-height:0; overflow:hidden; break-after:page; } .sheet:last-child { break-after:auto; } }
</style>
</head>
<body>
<div class="screen">
@foreach($references as $reference)
<section class="sheet">
    <header class="header">
        <img class="cross left" src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="Croix orthodoxe">
        <img class="cross right" src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="Croix orthodoxe">
        <div class="assoc">Communauté Orthodoxe Érythréenne de Toulouse</div>
        <div class="assoc-ti">ኦርቶዶክስ ተዋሕዶ ቤተ ክርስቲያን ኤርትራ ባእታ ማርያም ቱሉዝ ፈረንሳ</div>
        <div class="title">DEMANDE D'ACHAT ET DE DÉPENSE / ሕቶ ግዝኣትን ወጻኢታትን</div>
        <div class="docline">DOCUMENT ASSOCIATIF / ሰነድ ማሕበር</div>
        <div class="refline">{{ $reference }} · Date : ____ / ____ / ______</div>
    </header>

    <div class="section">DEMANDE D’ACHAT / ሕቶ ግዝኣት</div>
    <table>
        <tr><td class="label">Demandeur / ሓታቲ</td><td class="blank"></td><td class="label">Fonction / responsabilité<span class="ti">ሓላፍነት</span></td><td class="blank"></td></tr>
        <tr><td class="label">Téléphone / ስልኪ</td><td class="blank"></td><td class="label">Objet de la demande<span class="ti">ዕላማ ናይቲ ሕቶ</span></td><td class="blank"></td></tr>
        <tr><td class="label">Motif / utilisation<span class="ti">ምኽንያትን ኣጠቓቕማን</span></td><td colspan="3" class="blank tall"></td></tr>
    </table>

    <div class="section">CATÉGORIE DE LA DEMANDE / ምድብ ናይቲ ሕቶ</div>
    <table class="category-table">
        <tr>
            <td><span class="category-option">☐</span><span class="category-label">Matériel et équipements</span><span class="category-ti">ኣቕሑን መሳርሒታትን</span></td>
            <td><span class="category-option">☐</span><span class="category-label">Matériel d'entretien</span><span class="category-ti">ናውቲ ጽሬት</span></td>
        </tr>
        <tr>
            <td><span class="category-option">☐</span><span class="category-label">Entretien et réparations</span><span class="category-ti">ጽገናን ምሕዳስን</span></td>
            <td><span class="category-option">☐</span><span class="category-label">Transport et déplacements</span><span class="category-ti">መጓዓዝያን ጉዕዞን</span></td>
        </tr>
        <tr>
            <td><span class="category-option">☐</span><span class="category-label">Billets de train</span><span class="category-ti">ቲኬት ባቡር</span></td>
            <td><span class="category-option">☐</span><span class="category-label">Services et prestations</span><span class="category-ti">ኣገልግሎታትን ሞያዊ ስራሓትን</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="category-option">☐</span><span class="category-label">Autres dépenses</span><span class="category-ti">ካልኦት ወጻኢታት</span></td>
        </tr>
    </table>

    <div class="section">DÉTAIL DES ACHATS / ዝርዝር ግዝኣት</div>
    <table>
        <thead><tr><th style="width:46%">Désignation / መግለጺ</th><th style="width:14%">Quantité / ብዝሒ</th><th style="width:20%">Prix unitaire (€) / ዋጋ ንሓደ</th><th style="width:20%">Total (€) / ጠቕላላ</th></tr></thead>
        <tbody>@for($i=0;$i<6;$i++)<tr class="item-row"><td>&nbsp;</td><td></td><td></td><td></td></tr>@endfor</tbody>
    </table>
    <table>
        <tr><td class="label">Montant demandé / ዝተሓተተ መጠን</td><td class="blank amount">________________ €</td></tr>
    </table>

    <div class="section">APPROBATION / ውሳነ</div>
    <table>
        <tr><td class="label">Décision / ውሳነ</td><td class="blank">☐ Approuvé &nbsp;&nbsp; ☐ Refusé &nbsp;&nbsp; ☐ Avec modification</td></tr>
        <tr><td class="label">Montant autorisé / ዝተፈቕደ መጠን</td><td class="blank">________________ €</td></tr>
        <tr><td class="label">Responsable autorisant / ዘፍቅድ ሓላፊ</td><td class="blank"></td></tr>
        <tr><td class="label">Signature / ፊርማ</td><td class="signature"></td></tr>
    </table>
    <div class="footer">Communauté Orthodoxe Érythréenne de Toulouse · {{ $reference }} · Page 1/2</div>
</section>

<section class="sheet">
    <header class="header">
        <img class="cross left" src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="Croix orthodoxe">
        <img class="cross right" src="{{ asset('images/logo_OTEC_TLS_500X500.png') }}" alt="Croix orthodoxe">
        <div class="assoc">Communauté Orthodoxe Érythréenne de Toulouse</div>
        <div class="assoc-ti">ኦርቶዶክስ ተዋሕዶ ቤተ ክርስቲያን ኤርትራ ባእታ ማርያም ቱሉዝ ፈረንሳ</div>
        <div class="title">DEMANDE D'ACHAT ET DE DÉPENSE / ሕቶ ግዝኣትን ወጻኢታትን</div>
        <div class="docline">DÉCAISSEMENT ET JUSTIFICATIF / ክፍሊትን መረጋገጽን</div>
        <div class="refline">{{ $reference }}</div>
    </header>

    <div class="section">DÉCAISSEMENT / ክፍሊት</div>
    <table>
        <tr><td class="label">Montant versé / ዝተኸፍለ መጠን</td><td class="blank">________________ €</td></tr>
        <tr><td class="label">Date du paiement / ዕለት ክፍሊት</td><td class="blank">____ / ____ / ______</td></tr>
        <tr><td class="label">Mode / ኣገባብ ክፍሊት</td><td class="blank">☐ Espèces &nbsp;&nbsp; ☐ Virement &nbsp;&nbsp; ☐ Chèque &nbsp;&nbsp; ☐ Autre</td></tr>
        <tr><td class="label">Signature du demandeur / ፊርማ ሓታቲ</td><td class="signature"></td></tr>
        <tr><td class="label">Signature du trésorier / ፊርማ ተሓዚ ገንዘብ</td><td class="signature"></td></tr>
    </table>

    <div class="section">JUSTIFICATIF / መረጋገጺ</div>
    <table>
        <tr><td class="label">Référence RF / መወከሲ ሰነድ ሒሳብ</td><td class="blank"></td></tr>
        <tr><td class="label">Document / ሰነድ</td><td class="blank">☐ Facture &nbsp;&nbsp; ☐ Ticket &nbsp;&nbsp; ☐ Reçu &nbsp;&nbsp; ☐ Autre</td></tr>
        <tr><td class="label">Montant réellement dépensé</td><td class="blank">________________ €</td></tr>
        <tr><td class="label">Montant restant / ዝተረፈ መጠን</td><td class="blank">________________ €</td></tr>
        <tr><td class="label">Date remise justificatif / ዕለት መቕረቢ መረጋገጺ</td><td class="blank">____ / ____ / ______</td></tr>
        <tr><td class="label">Signature du demandeur / ፊርማ ሓታቲ</td><td class="signature"></td></tr>
        <tr><td class="label">Signature du trésorier / ፊርማ ተሓዚ ገንዘብ</td><td class="signature"></td></tr>
    </table>
    <div class="footer">À imprimer en deux exemplaires : 1 pour le dossier financier, 1 pour le demandeur.<br>ብኽልተ ቅዳሓት ክትሕተም ኣለዋ። · {{ $reference }} · Page 2/2</div>
</section>
@endforeach
</div>
<div class="no-print"><button onclick="window.print()">Imprimer</button> <a href="{{ route('purchase-requests.index') }}">Retour</a></div>
<script>window.onload = () => setTimeout(() => window.print(), 500);</script>
</body>
</html>
