<?php

namespace App\Http\Controllers;

use App\Services\FinancialRegisterGenerator;
use App\Services\FinancialRegisterPdfExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class FinancialRegisterController extends Controller
{
    protected $generator;

    protected $exporter;

    public function __construct(FinancialRegisterGenerator $generator, FinancialRegisterPdfExporter $exporter)
    {
        $this->generator = $generator;
        $this->exporter = $exporter;
    }

    /**
     * Show the financial register generation form.
     */
    public function index(): View
    {
        return view('finance.financial-register.create', [
            'years' => range((int) now()->format('Y') - 2, (int) now()->format('Y') + 3),
            'months' => $this->getMonthsForYear((int) now()->format('Y')),
            'quarters' => [1, 2, 3, 4],
            'currentYear' => (int) now()->format('Y'),
            'currentMonth' => (int) now()->format('n'),
        ]);
    }

    /**
     * Generate the monthly financial register.
     */
    public function generateMonthly(Request $request): Response
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'between:2020,2100'],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);
        $year = (int) $validated['year'];
        $month = (int) $validated['month'];
        $blankRegister = $request->boolean('blank_register');

        $register = $this->generator->generateMonthlyRegister($year, $month);

        if ($blankRegister) {
            $register['entries'] = collect();
            $register['opening_balance'] = null;
            $register['total_income'] = null;
            $register['total_expense'] = null;
            $register['closing_balance'] = null;
            $register['source_totals'] = collect();
            $register['empty_rows_count'] = FinancialRegisterGenerator::MONTHLY_ROWS;
            $register['visible_rows'] = FinancialRegisterGenerator::MONTHLY_ROWS;
            $register['blank_register'] = true;
        }

        return $this->deliver($register, 'finance.financial-register.print-monthly', ! $blankRegister);
    }

    /**
     * Generate the quarterly financial register.
     */
    public function generateQuarterly(Request $request): Response
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'between:2020,2100'],
            'quarter' => ['required', 'integer', 'between:1,4'],
        ]);
        $year = (int) $validated['year'];
        $quarter = (int) $validated['quarter'];

        $register = $this->generator->generateQuarterlyRegister($year, $quarter);

        return $this->deliver($register, 'finance.financial-register.print-quarterly');
    }

    /**
     * Render the register as PDF (Excel engine) or fall back to the printable
     * HTML view, then persist it inside the document storage.
     */
    protected function deliver(array $register, string $fallbackView, bool $store = true): Response
    {
        $register['sourceGuide'] = $this->generator->getSourceGuide();
        $exportReference = $register['reference'] . (($register['blank_register'] ?? false) ? '-blank' : '');

        $pdf = $this->exporter->export([
            'Title' => $register['title'],
            'DocLine' => $register['doc_line'],
            'Subtitle' => $register['subtitle'],
            'RefPrefix' => $register['reference'],
            'Entries' => $register['entries']->map(fn (array $entry) => [
                'line_reference' => $entry['line_reference'],
                'entry_date' => $entry['entry_date'],
                'source_reference' => $entry['source_reference'],
                'source_code' => $entry['source_code'],
                'category_label' => $entry['category_label'],
                'type' => $entry['type'],
                'amount' => $entry['amount'],
                'running_balance' => $entry['running_balance'],
                'payment_method' => $entry['payment_method'],
                'notes' => $entry['notes'],
            ])->all(),
            'OpeningBalance' => $register['opening_balance'],
            'TotalIncome' => $register['total_income'],
            'TotalExpense' => $register['total_expense'],
            'ClosingBalance' => $register['closing_balance'],
            'SourceTotals' => $register['source_totals']->all(),
            'Year' => $register['year'],
            'BlankData' => (bool) ($register['blank_register'] ?? false),
        ], $exportReference, (int) ($register['visible_rows'] ?? 0));

        if ($pdf !== null && $store) {
            $this->exporter->store($register['reference'], $pdf, [
                'reference' => $register['reference'],
                'title' => $register['title'],
                'period_label' => $register['period_label'],
                'period_label_ti' => $register['period_label_ti'],
                'period_start' => $register['period_start']->format('Y-m-d'),
                'period_end' => $register['period_end']->format('Y-m-d'),
                'period_type' => $register['period_type'],
                'year' => $register['year'],
                'month' => $register['month'] ?? null,
                'quarter' => $register['quarter'] ?? null,
                'total_income' => $register['total_income'],
                'total_expense' => $register['total_expense'],
                'opening_balance' => $register['opening_balance'],
                'closing_balance' => $register['closing_balance'],
                'generated_by' => auth()->id(),
                'generator' => 'excel-com',
            ]);

            return response()->file($pdf, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $register['reference'] . '.pdf"',
            ]);
        }

        if ($pdf !== null) {
            return response()->file($pdf, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $exportReference . '.pdf"',
            ]);
        }

        // Excel automation unavailable: serve the printable HTML template.
        $html = view($fallbackView, $register)->render();

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="' . $exportReference . '.html"')
            ->header('Cache-Control', 'private, must-revalidate, max-age=0');
    }

    /**
     * List all generated financial registers.
     */
    public function listRegisters(): View
    {
        $registers = [];

        $disk = Storage::disk(FinancialRegisterPdfExporter::DISK);

        foreach ($disk->files(FinancialRegisterPdfExporter::DIRECTORY) as $file) {
            if (! str_ends_with($file, '.meta.json')) {
                continue;
            }

            $meta = json_decode($disk->get($file), true);

            if (is_array($meta)) {
                $registers[] = $meta;
            }
        }

        usort($registers, function ($a, $b) {
            return ($b['updated_at'] ?? '') <=> ($a['updated_at'] ?? '');
        });

        return view('finance.financial-register.list', ['registers' => $registers]);
    }

    /**
     * Preview a stored register (inline PDF).
     */
    public function preview(string $reference): Response
    {
        $path = $this->exporter->storedPath($reference);

        if ($path === null) {
            abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $reference . '.pdf"',
        ]);
    }

    /**
     * Download a stored register.
     */
    public function download(string $reference): Response
    {
        $path = $this->exporter->storedPath($reference);

        if ($path === null) {
            abort(404);
        }

        return response()->download($path, $reference . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    protected function getMonthsForYear(int $year): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[] = $m;
        }
        return $months;
    }
}
