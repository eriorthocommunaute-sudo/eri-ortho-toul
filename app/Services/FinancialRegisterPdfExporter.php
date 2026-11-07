<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Exports the official bilingual "Registre financier" template to PDF.
 *
 * The association ships an Excel workbook that is the exact source of the
 * reference register PDF (page setup, colours, fonts, column widths, the
 * Orthodox cross artwork, the source-reference guide and the footer).
 * To guarantee a pixel-faithful result we let Excel itself render the
 * workbook through COM automation instead of re-implementing the layout.
 *
 * The title, period metadata and generated financial rows are written to a
 * throw-away copy of the official workbook before Excel renders the PDF.
 */
class FinancialRegisterPdfExporter
{
    public const TEMPLATE = 'templates/financial-register/Registre_Financier_2026_Tigrigna_French.xlsx';

    public const SCRIPT = 'templates/financial-register/export-register.ps1';

    public const DISK = 'local';

    public const DIRECTORY = 'financial-registers';

    public const MAX_ENTRIES = 200;

    public function templatePath(): string
    {
        return resource_path(self::TEMPLATE);
    }

    public function scriptPath(): string
    {
        return resource_path(self::SCRIPT);
    }

    /**
     * The Excel COM pipeline only runs on Windows with Excel installed.
     */
    public function isAvailable(): bool
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return false;
        }

        return is_file($this->templatePath()) && is_file($this->scriptPath());
    }

    /**
     * Render a populated register PDF and return its absolute path, or null when
     * the PDF engine is unavailable.
     *
     * @param array $values Header lines plus Entries, OpeningBalance, TotalIncome, TotalExpense, ClosingBalance
     * @param string $reference Document reference (RF-YYYY-MM)
     * @param int $visibleRows Number of register entry lines to show (0 = all)
     */
    public function export(array $values, string $reference, int $visibleRows = 0): ?string
    {
        if (! $this->isAvailable() || count($values['Entries'] ?? []) > self::MAX_ENTRIES) {
            return null;
        }

        $targetDir = storage_path('app/private/' . self::DIRECTORY);

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $target = $targetDir . '/' . $reference . '.pdf';

        $payload = [
            'Title' => (string) ($values['Title'] ?? ''),
            'Subtitle' => (string) ($values['Subtitle'] ?? ''),
            'DocLine' => (string) ($values['DocLine'] ?? ''),
            'RefPrefix' => (string) ($values['RefPrefix'] ?? ''),
            'Entries' => array_values($values['Entries'] ?? []),
            'OpeningBalance' => (float) ($values['OpeningBalance'] ?? 0),
            'TotalIncome' => (float) ($values['TotalIncome'] ?? 0),
            'TotalExpense' => (float) ($values['TotalExpense'] ?? 0),
            'ClosingBalance' => (float) ($values['ClosingBalance'] ?? 0),
            'SourceTotals' => $values['SourceTotals'] ?? [],
            'Year' => (int) ($values['Year'] ?? date('Y')),
            'BlankData' => (bool) ($values['BlankData'] ?? false),
        ];

        $valuesFile = $targetDir . '/' . $reference . '.values.json';
        File::put($valuesFile, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $process = new Process([
            'powershell',
            '-NoProfile',
            '-NonInteractive',
            '-ExecutionPolicy',
            'Bypass',
            '-File',
            $this->scriptPath(),
            '-TemplatePath',
            $this->templatePath(),
            '-OutputPath',
            $target,
            '-ValuesPath',
            $valuesFile,
            '-VisibleRows',
            (string) $visibleRows,
        ]);

        $process->setTimeout(180);

        try {
            $process->run();
        } finally {
            File::delete($valuesFile);
        }

        if (! $process->isSuccessful() || ! is_file($target)) {
            return null;
        }

        return $target;
    }

    /**
     * Persist the generated PDF and its metadata inside the document storage.
     *
     * @return array{path: string, version: int}
     */
    public function store(string $reference, string $absolutePath, array $meta): array
    {
        $relative = self::DIRECTORY . '/' . $reference . '.pdf';

        Storage::disk(self::DISK)->put($relative, file_get_contents($absolutePath));

        $metaPath = self::DIRECTORY . '/' . $reference . '.meta.json';

        $version = 1;
        $createdAt = now()->toDateTimeString();

        if (Storage::disk(self::DISK)->exists($metaPath)) {
            $previous = json_decode(Storage::disk(self::DISK)->get($metaPath), true) ?: [];
            $version = ((int) ($previous['version'] ?? 1)) + 1;
            $createdAt = $previous['created_at'] ?? $createdAt;
        }

        $meta['version'] = $version;
        $meta['created_at'] = $createdAt;
        $meta['updated_at'] = now()->toDateTimeString();

        Storage::disk(self::DISK)->put($metaPath, json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return ['path' => $relative, 'version' => $version];
    }

    /**
     * Absolute path of an already stored register PDF.
     */
    public function storedPath(string $reference): ?string
    {
        $relative = self::DIRECTORY . '/' . $reference . '.pdf';

        if (! Storage::disk(self::DISK)->exists($relative)) {
            return null;
        }

        return Storage::disk(self::DISK)->path($relative);
    }
}
