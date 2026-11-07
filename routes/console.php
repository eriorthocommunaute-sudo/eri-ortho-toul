<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('members:import-excel {--file= : Path to JSON file} {--dry-run : Preview without saving}', function ($file = null, $dryRun = false) {
    $file = $file ?? base_path('members_import.json');
    
    if (!file_exists($file)) {
        $this->error("File not found: {$file}");
        return 1;
    }

    $json = file_get_contents($file);
    $members = json_decode($json, true);

    if (!$members) {
        $this->error('Invalid JSON file');
        return 1;
    }

    $this->info("Found " . count($members) . " members to import");

    $imported = 0;
    $skipped = 0;
    $errors = 0;

    $admin = \App\Models\User::where('email', config('app.initial_admin_email', 'admin@example.test'))->first();
    $adminId = $admin?->id;

    $bar = $this->output->createProgressBar(count($members));
    $bar->start();

    foreach ($members as $data) {
        try {
            $memberCode = $data['member_code'] ?? null;

            $existing = $memberCode ? \App\Models\Member::where('member_code', $memberCode)->first() : null;

            if ($existing) {
                $skipped++;
                $bar->advance();
                continue;
            }

            if (!$dryRun) {
                \Illuminate\Support\Facades\DB::transaction(function () use ($data, $memberCode, $adminId, &$imported) {
                    $memberData = [
                        'title' => $data['title'],
                        'given_name' => $data['given_name'],
                        'family_name' => $data['family_name'],
                        'display_name' => $data['display_name'],
                        'phone' => $data['phone'],
                        'email' => $data['email'],
                        'address' => $data['address'],
                        'joined_at' => $data['joined_at'],
                        'status' => $data['status'],
                        'restricted_notes' => $data['restricted_notes'],
                        'created_by' => $adminId,
                        'updated_by' => $adminId,
                    ];

                    if ($memberCode) {
                        $memberData['member_code'] = $memberCode;
                    } else {
                        $memberData['member_code'] = app(\App\Services\ReferenceNumberService::class)->next('MEM');
                    }

                    \App\Models\Member::create($memberData);
                    $imported++;
                });
            } else {
                $imported++;
            }

            $bar->advance();
        } catch (\Throwable $e) {
            $errors++;
            $bar->advance();
            $memberCode = $data['member_code'] ?? 'unknown';
            $this->error("\nError importing {$memberCode}: {$e->getMessage()}");
        }
    }

    $bar->finish();
    $this->newLine(2);

    $this->info("Import completed!");
    $this->table(['Metric', 'Count'], [
        ['Imported', $imported],
        ['Skipped (existing)', $skipped],
        ['Errors', $errors],
    ]);

    return 0;
})->purpose('Import members from the Excel register (converted to JSON)');

Schedule::command('finance:generate-recurring-expenses')
    ->monthlyOn(1, '01:00')
    ->withoutOverlapping();