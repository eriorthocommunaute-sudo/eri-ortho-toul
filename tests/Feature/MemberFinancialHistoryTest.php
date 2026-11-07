<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\Member;
use App\Models\Offrande;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\User;
use App\Services\MemberFinancialHistory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberFinancialHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create(['name' => 'Administrator', 'slug' => 'administrator']);
        $this->user = User::factory()->create();
        $this->user->roles()->attach($this->role);

        // Use a fixed date for consistent testing
        $this->member = Member::factory()->create([
            'given_name' => 'Test',
            'family_name' => 'Member',
            'status' => 'active',
            'joined_at' => Carbon::create(2023, 1, 15),
        ]);
    }

    public function test_monthly_history_includes_expected_and_paid_contributions(): void
    {
        // Create contribution for January 2024
        $contribution = Contribution::create([
            'reference' => 'COT-2024-01',
            'year' => 2024,
            'month' => 1,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 1,
            'total_expected' => 10.00,
            'total_received' => 10.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Create confirmed receipt for January
        $receipt = Receipt::create([
            'reference' => 'REC-2024-0001',
            'member_id' => $this->member->id,
            'contribution_id' => $contribution->id,
            'payment_date' => '2024-01-10',
            'amount' => 10.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-2024-0001',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member);
        $monthly = $history->getMonthlyHistory();

        $jan = $monthly->firstWhere(fn ($m) => $m['year'] === 2024 && $m['month'] === 1);

        $this->assertNotNull($jan);
        $this->assertEquals(10.00, $jan['expected_amount']);
        $this->assertEquals(10.00, $jan['paid_amount']);
        $this->assertEquals('paid', $jan['status']);
    }

    public function test_partial_payment_shows_partial_status(): void
    {
        $contribution = Contribution::create([
            'reference' => 'COT-2024-02',
            'year' => 2024,
            'month' => 2,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 1,
            'total_expected' => 10.00,
            'total_received' => 5.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Receipt::create([
            'reference' => 'REC-2024-0002',
            'member_id' => $this->member->id,
            'contribution_id' => $contribution->id,
            'payment_date' => '2024-02-10',
            'amount' => 5.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-2024-0002',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member);
        $monthly = $history->getMonthlyHistory();

        $feb = $monthly->firstWhere(fn ($m) => $m['year'] === 2024 && $m['month'] === 2);

        $this->assertEquals('partial', $feb['status']);
        $this->assertEquals(5.00, $feb['paid_amount']);
    }

    public function test_unpaid_month_shows_unpaid_status(): void
    {
        $contribution = Contribution::create([
            'reference' => 'COT-2024-03',
            'year' => 2024,
            'month' => 3,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 0,
            'total_expected' => 10.00,
            'total_received' => 0.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member);
        $monthly = $history->getMonthlyHistory();

        $mar = $monthly->firstWhere(fn ($m) => $m['year'] === 2024 && $m['month'] === 3);

        $this->assertEquals('unpaid', $mar['status']);
        $this->assertEquals(0.00, $mar['paid_amount']);
    }

    public function test_offrandes_are_separate_from_contributions(): void
    {
        $contribution = Contribution::create([
            'reference' => 'COT-2024-04',
            'year' => 2024,
            'month' => 4,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 0,
            'total_expected' => 10.00,
            'total_received' => 0.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Add an offrande for April
        Offrande::create([
            'reference' => 'OFR-2024-0001',
            'offering_date' => '2024-04-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 25.00,
            'occasion' => 'Pâques',
            'financial_reference' => 'RF-2024-0003',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member);
        $monthly = $history->getMonthlyHistory();

        $apr = $monthly->firstWhere(fn ($m) => $m['year'] === 2024 && $m['month'] === 4);

        $this->assertEquals(10.00, $apr['expected_amount']);
        $this->assertEquals(0.00, $apr['paid_amount']);
        $this->assertEquals(25.00, $apr['offrande_amount']);
        $this->assertEquals(25.00, $apr['total_paid']);
        $this->assertEquals('unpaid', $apr['status']); // Contribution unpaid, but has offrande
    }

    public function test_multiple_payments_in_same_month_are_summed(): void
    {
        $contribution = Contribution::create([
            'reference' => 'COT-2024-05',
            'year' => 2024,
            'month' => 5,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 1,
            'total_expected' => 10.00,
            'total_received' => 10.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Receipt::create([
            'reference' => 'REC-2024-0003',
            'member_id' => $this->member->id,
            'contribution_id' => $contribution->id,
            'payment_date' => '2024-05-05',
            'amount' => 5.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-2024-0004',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Receipt::create([
            'reference' => 'REC-2024-0004',
            'member_id' => $this->member->id,
            'contribution_id' => $contribution->id,
            'payment_date' => '2024-05-20',
            'amount' => 5.00,
            'payment_method' => 'transfer',
            'financial_reference' => 'RF-2024-0005',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member);
        $monthly = $history->getMonthlyHistory();

        $may = $monthly->firstWhere(fn ($m) => $m['year'] === 2024 && $m['month'] === 5);

        $this->assertEquals(10.00, $may['paid_amount']);
        $this->assertEquals(2, $may['receipt_count']);
        $this->assertEquals('paid', $may['status']);
    }

    public function test_summary_cards_calculate_correctly(): void
    {
        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create contributions for last year and current year
        for ($month = 1; $month <= 3; $month++) {
            Contribution::create([
                'reference' => "COT-{$lastYear}-{$month}",
                'year' => $lastYear,
                'month' => $month,
                'amount_per_member' => 10.00,
                'expected_count' => 1,
                'received_count' => 1,
                'total_expected' => 10.00,
                'total_received' => 10.00,
                'status' => 'open',
                'created_by' => $this->user->id,
                'updated_by' => $this->user->id,
            ]);
        }

        for ($month = 1; $month <= 3; $month++) {
            Contribution::create([
                'reference' => "COT-{$currentYear}-{$month}",
                'year' => $currentYear,
                'month' => $month,
                'amount_per_member' => 10.00,
                'expected_count' => 1,
                'received_count' => 1,
                'total_expected' => 10.00,
                'total_received' => 10.00,
                'status' => 'open',
                'created_by' => $this->user->id,
                'updated_by' => $this->user->id,
            ]);
        }

        // Create receipts for current year Jan and Feb (March unpaid)
        $contribJan = Contribution::where('year', $currentYear)->where('month', 1)->first();
        $contribFeb = Contribution::where('year', $currentYear)->where('month', 2)->first();

        Receipt::create([
            'reference' => 'REC-'.$currentYear.'-0005',
            'member_id' => $this->member->id,
            'contribution_id' => $contribJan->id,
            'payment_date' => "$currentYear-01-10",
            'amount' => 10.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-'.$currentYear.'-0006',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Receipt::create([
            'reference' => 'REC-'.$currentYear.'-0006',
            'member_id' => $this->member->id,
            'contribution_id' => $contribFeb->id,
            'payment_date' => "$currentYear-02-10",
            'amount' => 10.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-'.$currentYear.'-0007',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Add offrandes for current year
        Offrande::create([
            'reference' => 'OFR-'.$currentYear.'-0002',
            'offering_date' => "$currentYear-01-20",
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 50.00,
            'financial_reference' => 'RF-'.$currentYear.'-0008',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member);
        $summary = $history->getSummaryCards();

        // Current year: 2 contributions paid (Jan, Feb) = 20, 1 offrande = 50
        $this->assertEquals(20.00, $summary['this_year_contributions_paid']);
        $this->assertEquals(50.00, $summary['this_year_offrandes_total']);
        $this->assertEquals(70.00, $summary['this_year_total_paid']);
        $this->assertEquals(10.00, $summary['this_year_outstanding']); // March expected but not paid
    }

    public function test_filter_by_year(): void
    {
        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        // Create contribution for last year
        $contribLastYear = Contribution::create([
            'reference' => "COT-{$lastYear}-12",
            'year' => $lastYear,
            'month' => 12,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 1,
            'total_expected' => 10.00,
            'total_received' => 10.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Create contribution for current year
        $contribThisYear = Contribution::create([
            'reference' => "COT-{$currentYear}-01",
            'year' => $currentYear,
            'month' => 1,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 1,
            'total_expected' => 10.00,
            'total_received' => 10.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Receipt::create([
            'reference' => 'REC-'.$lastYear.'-0001',
            'member_id' => $this->member->id,
            'contribution_id' => $contribLastYear->id,
            'payment_date' => "$lastYear-12-10",
            'amount' => 10.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-'.$lastYear.'-0001',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Receipt::create([
            'reference' => 'REC-'.$currentYear.'-0007',
            'member_id' => $this->member->id,
            'contribution_id' => $contribThisYear->id,
            'payment_date' => "$currentYear-01-10",
            'amount' => 10.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-'.$currentYear.'-0009',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member, $lastYear);
        $monthly = $history->getMonthlyHistory();

        // Should only include last year (12 months)
        $this->assertTrue($monthly->every(fn ($m) => $m['year'] === $lastYear));
        $this->assertCount(12, $monthly);
        $this->assertEquals($lastYear, $monthly->first()['year']);
    }

    public function test_filter_by_status(): void
    {
        $currentYear = now()->year;

        // Paid contribution - use October (month 10) which is visible in 2026
        $contribPaid = Contribution::create([
            'reference' => "COT-{$currentYear}-10",
            'year' => $currentYear,
            'month' => 10,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 1,
            'total_expected' => 10.00,
            'total_received' => 10.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Receipt::create([
            'reference' => 'REC-'.$currentYear.'-0008',
            'member_id' => $this->member->id,
            'contribution_id' => $contribPaid->id,
            'payment_date' => "$currentYear-10-10",
            'amount' => 10.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-'.$currentYear.'-0010',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Unpaid contribution - use November (month 11) which is visible in 2026
        $contribUnpaid = Contribution::create([
            'reference' => "COT-{$currentYear}-11",
            'year' => $currentYear,
            'month' => 11,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 0,
            'total_expected' => 10.00,
            'total_received' => 0.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Filter for paid - should include the paid month
        $history = new MemberFinancialHistory($this->member, null, 'paid');
        $monthly = $history->getMonthlyHistory();

        // Should have at least the paid month (there may be other paid months from other years)
        $paidMonths = $monthly->where('status', 'paid');
        $this->assertGreaterThanOrEqual(1, $paidMonths->count());
        $this->assertTrue($paidMonths->contains(fn ($m) => $m['year'] === $currentYear && $m['month'] === 10));

        // Filter for unpaid - should include the unpaid month
        $history = new MemberFinancialHistory($this->member, null, 'unpaid');
        $monthly = $history->getMonthlyHistory();

        $unpaidMonths = $monthly->where('status', 'unpaid');
        $this->assertGreaterThanOrEqual(1, $unpaidMonths->count());
        $this->assertTrue($unpaidMonths->contains(fn ($m) => $m['year'] === $currentYear && $m['month'] === 11));
    }

    public function test_export_csv_contains_expected_data(): void
    {
        $currentYear = now()->year;

        $contribution = Contribution::create([
            'reference' => "COT-{$currentYear}-10",
            'year' => $currentYear,
            'month' => 10,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 1,
            'total_expected' => 10.00,
            'total_received' => 10.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Receipt::create([
            'reference' => 'REC-'.$currentYear.'-0009',
            'member_id' => $this->member->id,
            'contribution_id' => $contribution->id,
            'payment_date' => "$currentYear-10-10",
            'amount' => 10.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-'.$currentYear.'-0011',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member);
        $csv = $history->exportToCsv();

        $this->assertStringContainsString('Année', $csv); // French header
        $this->assertStringContainsString((string)$currentYear, $csv);
        $this->assertStringContainsString('10', $csv);
        $this->assertStringContainsString('10,00', $csv); // French number format
        $this->assertStringContainsString('Payé', $csv);
    }

    public function test_does_not_include_future_months(): void
    {
        $futureYear = now()->year + 1;
        $futureMonth = 6;

        Contribution::create([
            'reference' => "COT-{$futureYear}-{$futureMonth}",
            'year' => $futureYear,
            'month' => $futureMonth,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 0,
            'total_expected' => 10.00,
            'total_received' => 0.00,
            'status' => 'open',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member);
        $monthly = $history->getMonthlyHistory();

        // Should not include future months
        $future = $monthly->firstWhere(fn ($m) => $m['year'] === $futureYear && $m['month'] === $futureMonth);
        $this->assertNull($future);
    }

    public function test_does_not_include_months_before_joined(): void
    {
        $history = new MemberFinancialHistory($this->member);
        $monthly = $history->getMonthlyHistory();

        // Member joined Jan 15, 2023, so Dec 2022 should not appear
        $dec2022 = $monthly->firstWhere(fn ($m) => $m['year'] === 2022 && $m['month'] === 12);
        $this->assertNull($dec2022);
    }
}