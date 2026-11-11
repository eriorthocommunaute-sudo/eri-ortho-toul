<?php

namespace Tests\Feature;

use App\Models\FinancialEntry;
use App\Models\Role;
use App\Models\User;
use App\Services\FinancialRegisterGenerator;
use App\Services\FinancialRegisterPdfExporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FinancialRegisterTest extends TestCase
{
    use RefreshDatabase;

    protected $financeRole;

    protected $financeUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->financeRole = Role::create(['name' => 'Finance', 'slug' => 'finance']);
        $this->financeUser = User::factory()->create();
        $this->financeUser->roles()->attach($this->financeRole);
    }

    /**
     * Swap the Excel exporter for a no-op so the controller tests stay fast
     * and exercise the printable HTML fallback.
     */
    protected function fakeExporter(): void
    {
        $fake = new class extends FinancialRegisterPdfExporter
        {
            public function isAvailable(): bool
            {
                return false;
            }

            public function export(array $values, string $reference, int $visibleRows = 0): ?string
            {
                return null;
            }
        };

        $this->app->instance(FinancialRegisterPdfExporter::class, $fake);
    }

    /** @test */
    public function it_builds_a_blank_monthly_register_for_october_2026()
    {
        $register = app(FinancialRegisterGenerator::class)->generateMonthlyRegister(2026, 10);

        $this->assertSame('RF-2026-10', $register['reference']);
        $this->assertSame('2026-10-01', $register['period_start']->format('Y-m-d'));
        $this->assertSame('2026-10-31', $register['period_end']->format('Y-m-d'));
        $this->assertSame('Octobre 2026', $register['period_label']);
        $this->assertStringContainsString('Registre financier, Octobre 2026', $register['title']);
        $this->assertStringContainsString('መዝገብ ገንዘብ : ጥቅምቲ 2026', $register['title']);

        // Five-line bilingual header.
        $this->assertSame('Communauté Orthodoxe Érythréenne de Toulouse', $register['assoc_fr']);
        $this->assertStringContainsString('ኦርቶዶክስ ተዋሕዶ ቤተ ክርስቲያን', $register['assoc_ti']);
        $this->assertStringContainsString('DOCUMENT ASSOCIATIF / ሰነድ ማሕበር', $register['doc_line']);
        $this->assertStringContainsString('RF-2026-10', $register['doc_line']);
        $this->assertStringContainsString('Registre des recettes et dépenses', $register['subtitle']);
        $this->assertStringContainsString('መዝገብ እቶትን ወጻኢታትን', $register['subtitle']);
        $this->assertStringContainsString('01/10/2026', $register['subtitle']);
        $this->assertStringContainsString('31/10/2026', $register['subtitle']);

        // Blank template: the reserved monthly COT line only, zeroed totals.
        $this->assertCount(1, $register['entries']);
        $this->assertSame('COT-2026-10', $register['entries'][0]['source_reference']);
        $this->assertSame(0.0, $register['total_income']);
        $this->assertSame(0.0, $register['total_expense']);
        $this->assertSame(0.0, $register['closing_balance']);

        // The monthly register keeps 20 blank entry lines.
        $this->assertSame(20, $register['visible_rows']);
        $this->assertSame(20, $register['empty_rows_count']);
    }

    /** @test */
    public function it_builds_a_blank_quarterly_register_for_q4_2026()
    {
        $register = app(FinancialRegisterGenerator::class)->generateQuarterlyRegister(2026, 4);

        $this->assertSame('RF-2026-T4', $register['reference']);
        $this->assertSame('2026-10-01', $register['period_start']->format('Y-m-d'));
        $this->assertSame('2026-12-31', $register['period_end']->format('Y-m-d'));
        $this->assertStringContainsString('Octobre–Décembre 2026', $register['period_label']);
        $this->assertStringContainsString('Registre financier, T4 2026', $register['title']);
        $this->assertStringContainsString('RF-2026-T4', $register['doc_line']);
        $this->assertStringContainsString('01/10/2026', $register['subtitle']);
        $this->assertStringContainsString('31/12/2026', $register['subtitle']);

        $this->assertCount(0, $register['entries']);
        $this->assertSame(0.0, $register['total_income']);
        $this->assertSame(0.0, $register['closing_balance']);

        // The quarterly register keeps 50 blank entry lines.
        $this->assertSame(50, $register['visible_rows']);
        $this->assertSame(50, $register['empty_rows_count']);
    }

    /** @test */
    public function it_generates_a_monthly_register_document()
    {
        $this->fakeExporter();

        $response = $this->actingAs($this->financeUser)
            ->post('/financial-register/generate-monthly', [
                'year' => 2026,
                'month' => 10,
            ]);

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('RF-2026-10', $content);
        $this->assertStringContainsString('Octobre 2026', $content);

        // The entry lines are numbered inside the register period.
        $this->assertStringContainsString('RF-2026-10-01', $content);
        $this->assertStringContainsString('RF-2026-10-20', $content);
        $this->assertStringNotContainsString('RF-2026-0001', $content);
    }

    /** @test */
    public function it_generates_a_quarterly_register_document()
    {
        $this->fakeExporter();

        $response = $this->actingAs($this->financeUser)
            ->post('/financial-register/generate-quarterly', [
                'year' => 2026,
                'quarter' => 4,
            ]);

        $response->assertStatus(200);

        $content = $response->getContent();
        $this->assertStringContainsString('RF-2026-T4', $content);
        $this->assertStringContainsString('Décembre 2026', $content);

        // The entry lines are numbered inside the register period.
        $this->assertStringContainsString('RF-2026-T4-01', $content);
        $this->assertStringContainsString('RF-2026-T4-50', $content);
        $this->assertStringNotContainsString('RF-2026-0001', $content);
    }

    /** @test */
    public function it_does_not_include_transaction_data_in_blank_templates()
    {
        $this->fakeExporter();

        $response = $this->actingAs($this->financeUser)
            ->post('/financial-register/generate-monthly', [
                'year' => 2026,
                'month' => 10,
                'blank_register' => 1,
            ]);

        $response->assertStatus(200);
        $content = $response->getContent();

        // The blank template carries no ledger rows and no calculated amounts.
        $this->assertStringNotContainsString('COT-2026-10', $content);
        $this->assertStringNotContainsString('20,00', $content);
        $this->assertStringNotContainsString('10,00', $content);
    }

    /** @test */
    public function it_does_not_create_edit_or_delete_ledger_transactions()
    {
        $this->fakeExporter();

        $before = FinancialEntry::count();

        $this->actingAs($this->financeUser)
            ->post('/financial-register/generate-monthly', ['year' => 2026, 'month' => 10])
            ->assertStatus(200);

        $this->actingAs($this->financeUser)
            ->post('/financial-register/generate-quarterly', ['year' => 2026, 'quarter' => 4])
            ->assertStatus(200);

        $this->assertSame($before, FinancialEntry::count());
    }

    /** @test */
    public function it_restricts_register_generation_to_authorized_roles()
    {
        $this->fakeExporter();

        $reviewerRole = Role::create(['name' => 'Reviewer', 'slug' => 'reviewer']);
        $reviewer = User::factory()->create();
        $reviewer->roles()->attach($reviewerRole);

        $this->actingAs($reviewer)
            ->post('/financial-register/generate-monthly', ['year' => 2026, 'month' => 10])
            ->assertForbidden();
    }

    /** @test */
    public function it_exports_the_register_as_a_real_pdf_using_the_excel_template()
    {
        $exporter = app(FinancialRegisterPdfExporter::class);

        if (! $exporter->isAvailable()) {
            $this->markTestSkipped('Excel template/automation is not available on this host.');
        }

        $response = $this->actingAs($this->financeUser)
            ->post('/financial-register/generate-monthly', ['year' => 2026, 'month' => 10]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');

        // The document is stored in the document storage with its reference.
        $relative = 'financial-registers/RF-2026-10.pdf';
        $disk = Storage::disk(FinancialRegisterPdfExporter::DISK);

        $this->assertTrue($disk->exists($relative));

        $content = $disk->get($relative);
        $this->assertStringStartsWith('%PDF', $content);
        $this->assertGreaterThan(100000, strlen($content));

        // Metadata is kept for the document listing.
        $this->assertTrue($disk->exists('financial-registers/RF-2026-10.meta.json'));
    }

    /** @test */
    public function it_limits_the_number_of_entry_lines_in_the_generated_registers()
    {
        $exporter = app(FinancialRegisterPdfExporter::class);

        if (! $exporter->isAvailable()) {
            $this->markTestSkipped('Excel template/automation is not available on this host.');
        }

        $disk = Storage::disk(FinancialRegisterPdfExporter::DISK);

        $pageCount = function (string $binary): int {
            // "/Type /Page" entries (not "/Type /Pages") give the page count.
            return preg_match_all('#/Type\s*/Page[^s]#', $binary);
        };

        // Monthly register: 20 entry lines -> a single page.
        $this->actingAs($this->financeUser)
            ->post('/financial-register/generate-monthly', ['year' => 2026, 'month' => 10])
            ->assertStatus(200);

        $this->assertSame(1, $pageCount($disk->get('financial-registers/RF-2026-10.pdf')));

        // Quarterly register: 50 entry lines -> two pages.
        $this->actingAs($this->financeUser)
            ->post('/financial-register/generate-quarterly', ['year' => 2026, 'quarter' => 4])
            ->assertStatus(200);

        $this->assertSame(2, $pageCount($disk->get('financial-registers/RF-2026-T4.pdf')));
    }
}
