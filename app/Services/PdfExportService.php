<?php

namespace App\Services;

use Illuminate\Support\Facades\View;

class PdfExportService
{
    /**
     * Generate a PDF-ready HTML response for the given view.
     * The view should include @media print CSS and auto-print JavaScript.
     */
    public function generate(string $view, array $data = []): \Illuminate\Http\Response
    {
        $html = View::make($view, $data)->render();

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="export.pdf"');
    }

    /**
     * Get the URL for a printable view that auto-triggers print dialog.
     */
    public function getPrintUrl(string $route, array $parameters = []): string
    {
        return route($route, $parameters);
    }

    /**
     * Available document templates for PDF export.
     */
    public function getTemplates(): array
    {
        return [
            'member' => [
                'label' => 'Fiche membre',
                'route' => 'members.print',
                'description' => 'Imprimer la liste des membres',
            ],
            'receipt' => [
                'label' => 'Reçu de cotisation',
                'route' => 'receipts.print',
                'description' => 'Imprimer un reçu individuel',
            ],
            'contribution_sheet' => [
                'label' => 'Feuille de cotisation',
                'route' => 'contributions.show',
                'description' => 'Détail d\'une période de cotisation',
            ],
            'financial_report' => [
                'label' => 'Rapport financier',
                'route' => 'financial-entries.report',
                'description' => 'Rapport mensuel/annuel',
            ],
            'member_list' => [
                'label' => 'Liste des membres',
                'route' => 'members.print',
                'description' => 'Registre complet des membres',
            ],
            'purchase_request' => [
                'label' => 'Demande d\'achat (DA)',
                'route' => 'purchase-requests.show',
                'description' => 'Document de demande d\'achat',
            ],
            'exceptional_demand' => [
                'label' => 'Demande exceptionnelle (EXC)',
                'route' => 'exceptional-demands.show',
                'description' => 'Document de demande exceptionnelle',
            ],
            'meeting_minute' => [
                'label' => 'Procès-verbal (PV)',
                'route' => 'meeting-minutes.show',
                'description' => 'Compte-rendu de réunion',
            ],
            'equipment_sheet' => [
                'label' => 'Fiche équipement (EQP)',
                'route' => 'equipment.show',
                'description' => 'Fiche d\'équipement',
            ],
            'transport_sheet' => [
                'label' => 'Fiche transport (TRP)',
                'route' => 'priest-transports.show',
                'description' => 'Fiche de transport du prêtre',
            ],
            'indemnity_sheet' => [
                'label' => 'Fiche indemnité (PRE)',
                'route' => 'priest-indemnities.show',
                'description' => 'Fiche d\'indemnité du prêtre',
            ],
            'event_sheet' => [
                'label' => 'Fiche événement (EVT)',
                'route' => 'events.show',
                'description' => 'Fiche d\'événement',
            ],
            'offrande_sheet' => [
                'label' => 'Fiche offrande (OFF)',
                'route' => 'offrandes.show',
                'description' => 'Fiche d\'offrande',
            ],
            'donation_sheet' => [
                'label' => 'Fiche don (DON)',
                'route' => 'donations.show',
                'description' => 'Fiche d\'un don',
            ],
            'financial_register' => [
                'label' => 'Registre financier mensuel / trimestriel',
                'route' => 'financial-register.index',
                'description' => 'Registre financier vierge conforme au modèle officiel (bilingue)',
            ],
        ];
    }
}