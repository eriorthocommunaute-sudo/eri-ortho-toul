<?php

namespace Tests\Unit;

use App\Models\ReferenceSequence;
use App\Services\ReferenceNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReferenceNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_allocates_monotonic_monthly_references(): void
    {
        $service = app(ReferenceNumberService::class);
        $this->assertSame('MEM-2026-11-001', $service->next('MEM', 2026, 11));
        $this->assertSame('MEM-2026-11-002', $service->next('MEM', 2026, 11));
        $this->assertSame('MEM-2027-01-001', $service->next('MEM', 2027, 1));
        $this->assertSame(2, ReferenceSequence::count());
    }

    public function test_it_recovers_from_a_missing_sequence_row(): void
    {
        DB::table('members')->insert([
            'given_name' => 'Semere',
            'family_name' => 'Biniam',
            'display_name' => 'Semere Biniam',
            'member_code' => 'MEM-2026-11-050',
            'joined_at' => now()->toDateString(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('MEM-2026-11-051', app(ReferenceNumberService::class)->next('MEM', 2026, 11));
        $this->assertSame(51, ReferenceSequence::where('prefix', 'MEM')->where('sequence_year', 2026)->where('sequence_month', 11)->value('last_number'));
    }

    public function test_it_rewinds_a_sequence_that_lags_behind_existing_codes(): void
    {
        ReferenceSequence::create(['prefix' => 'MEM', 'sequence_year' => 2026, 'sequence_month' => 11, 'last_number' => 1]);

        DB::table('members')->insert([
            'given_name' => 'Semere',
            'family_name' => 'Biniam',
            'display_name' => 'Semere Biniam',
            'member_code' => 'MEM-2026-11-007',
            'joined_at' => now()->toDateString(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('MEM-2026-11-008', app(ReferenceNumberService::class)->next('MEM', 2026, 11));
    }

    public function test_it_allocates_monthly_purchase_request_references(): void
    {
        $service = app(ReferenceNumberService::class);
        $this->assertSame('DA-2026-09-001', $service->next('DA', 2026, 9));
        $this->assertSame('DA-2026-09-002', $service->next('DA', 2026, 9));
        $this->assertSame('DA-2026-10-001', $service->next('DA', 2026, 10));
        $this->assertSame('DA-2026-09-003', $service->next('DA', 2026, 9));
        $this->assertSame(2, ReferenceSequence::count());
    }

    public function test_da_references_use_the_current_year_and_month_by_default(): void
    {
        $this->assertSame(
            sprintf('DA-%s-%s-001', now()->format('Y'), now()->format('m')),
            app(ReferenceNumberService::class)->next('DA')
        );
    }
}
