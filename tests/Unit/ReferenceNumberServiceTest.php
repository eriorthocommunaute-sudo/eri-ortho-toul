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

    public function test_it_allocates_monotonic_yearly_references(): void
    {
        $service = app(ReferenceNumberService::class);
        $this->assertSame('MEM-2026-001', $service->next('mem', 2026));
        $this->assertSame('MEM-2026-002', $service->next('MEM', 2026));
        $this->assertSame('MEM-2027-001', $service->next('MEM', 2027));
        $this->assertSame(2, ReferenceSequence::count());
    }

    public function test_it_recovers_from_a_missing_sequence_row(): void
    {
        DB::table('members')->insert([
            'given_name' => 'Semere',
            'family_name' => 'Biniam',
            'display_name' => 'Semere Biniam',
            'member_code' => 'MEM-2026-050',
            'joined_at' => now()->toDateString(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('MEM-2026-051', app(ReferenceNumberService::class)->next('MEM', 2026));
        $this->assertSame(51, ReferenceSequence::where('prefix', 'MEM')->where('sequence_year', 2026)->value('last_number'));
    }

    public function test_it_rewinds_a_sequence_that_lags_behind_existing_codes(): void
    {
        ReferenceSequence::create(['prefix' => 'MEM', 'sequence_year' => 2026, 'last_number' => 1]);

        DB::table('members')->insert([
            'given_name' => 'Semere',
            'family_name' => 'Biniam',
            'display_name' => 'Semere Biniam',
            'member_code' => 'MEM-2026-007',
            'joined_at' => now()->toDateString(),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame('MEM-2026-008', app(ReferenceNumberService::class)->next('MEM', 2026));
    }
}
