<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\Donation;
use App\Models\FinancialEntry;
use App\Models\Member;
use App\Models\Offrande;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\User;
use App\Services\MemberFinancialHistory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberDonationHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create(['name' => 'Administrator', 'slug' => 'administrator']);
        $this->user = User::factory()->create();
        $this->user->roles()->attach($this->role);

        $this->member = Member::factory()->create([
            'given_name' => 'Test',
            'family_name' => 'Member',
            'status' => 'active',
            'joined_at' => Carbon::create(2023, 1, 15),
        ]);
    }

    public function test_cotisation_table_2026_shows_only_oct_nov_dec(): void
    {
        // Create contributions for 2026 (all months)
        for ($month = 1; $month <= 12; $month++) {
            Contribution::create([
                'reference' => "COT-2026-{$month}",
                'year' => 2026,
                'month' => $month,
                'amount_per_member' => 10.00,
                'expected_count' => 1,
                'received_count' => 0,
                'total_expected' => 10.00,
                'total_received' => 0.00,
                'status' => 'open',
                'created_by' => $this->user->id,
                'updated_by' => $this->user->id,
            ]);
        }

        // Create contributions for 2027 (all months)
        for ($month = 1; $month <= 12; $month++) {
            Contribution::create([
                'reference' => "COT-2027-{$month}",
                'year' => 2027,
                'month' => $month,
                'amount_per_member' => 10.00,
                'expected_count' => 1,
                'received_count' => 0,
                'total_expected' => 10.00,
                'total_received' => 0.00,
                'status' => 'open',
                'created_by' => $this->user->id,
                'updated_by' => $this->user->id,
            ]);
        }

        $history = new MemberFinancialHistory($this->member, 2026);
        $monthly = $history->getMonthlyHistory();

        // Should only have Oct (10), Nov (11), Dec (12) for 2026
        $months2026 = $monthly->where('year', 2026);
        $this->assertCount(3, $months2026);
        $this->assertTrue($months2026->contains(fn ($m) => $m['month'] === 10));
        $this->assertTrue($months2026->contains(fn ($m) => $m['month'] === 11));
        $this->assertTrue($months2026->contains(fn ($m) => $m['month'] === 12));
        $this->assertFalse($months2026->contains(fn ($m) => $m['month'] < 10));
    }

    public function test_cotisation_table_2027_shows_all_12_months(): void
    {
        // Create contributions for 2027 (all months)
        for ($month = 1; $month <= 12; $month++) {
            Contribution::create([
                'reference' => "COT-2027-{$month}",
                'year' => 2027,
                'month' => $month,
                'amount_per_member' => 10.00,
                'expected_count' => 1,
                'received_count' => 0,
                'total_expected' => 10.00,
                'total_received' => 0.00,
                'status' => 'open',
                'created_by' => $this->user->id,
                'updated_by' => $this->user->id,
            ]);
        }

        $history = new MemberFinancialHistory($this->member, 2027);
        $monthly = $history->getMonthlyHistory();

        $months2027 = $monthly->where('year', 2027);
        $this->assertCount(12, $months2027);
        for ($month = 1; $month <= 12; $month++) {
            $this->assertTrue($months2027->contains(fn ($m) => $m['month'] === $month));
        }
    }

    public function test_donation_table_2026_shows_only_2026_donations(): void
    {
        // Create offrandes for 2026
        Offrande::create([
            'reference' => 'OFR-2026-0001',
            'offering_date' => '2026-06-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 50.00,
            'occasion' => 'Pâques',
            'financial_reference' => 'RF-2026-0001',
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Create offrandes for 2027
        Offrande::create([
            'reference' => 'OFR-2027-0001',
            'offering_date' => '2027-03-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 75.00,
            'occasion' => 'Noël',
            'financial_reference' => 'RF-2027-0001',
            'status' => 'confirmed',
            'payment_method' => 'transfer',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Create donations where donor matches member name
        Donation::create([
            'reference' => 'DON-2026-0001',
            'donation_date' => '2026-07-20',
            'donor_name' => 'Test Member',
            'donor_type' => 'individual',
            'amount' => 100.00,
            'purpose' => 'Don général',
            'financial_reference' => 'RF-2026-0002',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Donation::create([
            'reference' => 'DON-2027-0001',
            'donation_date' => '2027-02-10',
            'donor_name' => 'Test Member',
            'donor_type' => 'individual',
            'amount' => 200.00,
            'purpose' => 'Don spécial',
            'financial_reference' => 'RF-2027-0002',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member, null, null, 2026);
        $donationHistory = $history->getDonationHistory();

        // Should only have 2026 donations (1 offrande + 1 donation = 2)
        $this->assertCount(2, $donationHistory);
        $this->assertTrue($donationHistory->every(fn ($d) => $d['date']->year === 2026));
    }

    public function test_donation_table_2027_shows_only_2027_donations(): void
    {
        Offrande::create([
            'reference' => 'OFR-2026-0001',
            'offering_date' => '2026-06-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 50.00,
            'occasion' => 'Pâques',
            'financial_reference' => 'RF-2026-0001',
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Offrande::create([
            'reference' => 'OFR-2027-0001',
            'offering_date' => '2027-03-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 75.00,
            'occasion' => 'Noël',
            'financial_reference' => 'RF-2027-0001',
            'status' => 'confirmed',
            'payment_method' => 'transfer',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member, null, null, 2027);
        $donationHistory = $history->getDonationHistory();

        // Should only have 2027 donations
        $this->assertCount(1, $donationHistory);
        $this->assertEquals(2027, $donationHistory->first()['date']->year);
    }

    public function test_donation_summary_calculates_correctly(): void
    {
        Offrande::create([
            'reference' => 'OFR-2026-0001',
            'offering_date' => '2026-01-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 50.00,
            'occasion' => 'Pâques',
            'financial_reference' => 'RF-2026-0001',
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Offrande::create([
            'reference' => 'OFR-2026-0002',
            'offering_date' => '2026-06-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 100.00,
            'occasion' => 'Noël',
            'financial_reference' => 'RF-2026-0002',
            'status' => 'confirmed',
            'payment_method' => 'transfer',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Donation::create([
            'reference' => 'DON-2026-0001',
            'donation_date' => '2026-03-10',
            'donor_name' => 'Test Member',
            'donor_type' => 'individual',
            'amount' => 200.00,
            'purpose' => 'Don spécial',
            'financial_reference' => 'RF-2026-0003',
            'status' => 'confirmed',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member, null, null, 2026);
        $summary = $history->getDonationSummary();

        // 3 donations: 50 + 100 + 200 = 350
        $this->assertEquals(350.00, $summary['total_amount']);
        $this->assertEquals(3, $summary['count']);
        $this->assertEquals(350.00 / 3, $summary['average_amount']);

        // Check category breakdown
        $this->assertArrayHasKey('Pâques', $summary['by_category']);
        $this->assertArrayHasKey('Noël', $summary['by_category']);
        $this->assertArrayHasKey('Don spécial', $summary['by_category']);
    }

    public function test_pending_cancelled_donations_excluded_from_summary(): void
    {
        // Confirmed offrande
        Offrande::create([
            'reference' => 'OFR-2026-0001',
            'offering_date' => '2026-01-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 50.00,
            'occasion' => 'Pâques',
            'financial_reference' => 'RF-2026-0001',
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Pending offrande (should be excluded)
        Offrande::create([
            'reference' => 'OFR-2026-0002',
            'offering_date' => '2026-02-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 100.00,
            'occasion' => 'Noël',
            'financial_reference' => 'RF-2026-0002',
            'status' => 'pending',
            'payment_method' => 'transfer',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Cancelled offrande (should be excluded)
        Offrande::create([
            'reference' => 'OFR-2026-0003',
            'offering_date' => '2026-03-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 200.00,
            'occasion' => 'Autre',
            'financial_reference' => 'RF-2026-0003',
            'status' => 'cancelled',
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member, null, null, 2026);
        $summary = $history->getDonationSummary();

        // Only confirmed should be counted
        $this->assertEquals(50.00, $summary['total_amount']);
        $this->assertEquals(1, $summary['count']);
    }

    public function test_cotisation_and_donation_year_filters_work_independently(): void
    {
        // Create data for both years
        for ($year = 2026; $year <= 2027; $year++) {
            for ($month = 1; $month <= 12; $month++) {
                Contribution::create([
                    'reference' => "COT-{$year}-{$month}",
                    'year' => $year,
                    'month' => $month,
                    'amount_per_member' => 10.00,
                    'expected_count' => 1,
                    'received_count' => 0,
                    'total_expected' => 10.00,
                    'total_received' => 0.00,
                    'status' => 'open',
                    'created_by' => $this->user->id,
                    'updated_by' => $this->user->id,
                ]);
            }
        }

        Offrande::create([
            'reference' => 'OFR-2026-0001',
            'offering_date' => '2026-06-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 50.00,
            'occasion' => 'Pâques',
            'financial_reference' => 'RF-2026-0001',
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        Offrande::create([
            'reference' => 'OFR-2027-0001',
            'offering_date' => '2027-03-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 75.00,
            'occasion' => 'Noël',
            'financial_reference' => 'RF-2027-0001',
            'status' => 'confirmed',
            'payment_method' => 'transfer',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Test: cotisation year = 2026, donation year = 2027
        $history = new MemberFinancialHistory($this->member, 2026, null, 2027);
        $monthly = $history->getMonthlyHistory();
        $donations = $history->getDonationHistory();

        // Cotisation should show 2026 (Oct-Dec only)
        $months2026 = $monthly->where('year', 2026);
        $this->assertCount(3, $months2026);

        // Donations should show 2027
        $this->assertCount(1, $donations);
        $this->assertEquals(2027, $donations->first()['date']->year);

        // Test: cotisation year = 2027, donation year = 2026
        $history = new MemberFinancialHistory($this->member, 2027, null, 2026);
        $monthly = $history->getMonthlyHistory();
        $donations = $history->getDonationHistory();

        // Cotisation should show 2027 (all 12 months)
        $months2027 = $monthly->where('year', 2027);
        $this->assertCount(12, $months2027);

        // Donations should show 2026
        $this->assertCount(1, $donations);
        $this->assertEquals(2026, $donations->first()['date']->year);
    }

    public function test_empty_donation_year_shows_empty_message(): void
    {
        // No donations for 2027
        $history = new MemberFinancialHistory($this->member, null, null, 2027);
        $donationHistory = $history->getDonationHistory();

        $this->assertCount(0, $donationHistory);
        $summary = $history->getDonationSummary();
        $this->assertEquals(0, $summary['count']);
        $this->assertEquals(0, $summary['total_amount']);
    }

    public function test_no_duplicate_financial_entries_created(): void
    {
        // Verify that viewing history doesn't create financial entries
        $initialCount = FinancialEntry::count();

        $history = new MemberFinancialHistory($this->member);
        $history->getMonthlyHistory();
        $history->getDonationHistory();

        $this->assertEquals($initialCount, FinancialEntry::count());
    }

    public function test_donation_linked_to_financial_ledger(): void
    {
        $offrande = Offrande::create([
            'reference' => 'OFR-2026-0001',
            'offering_date' => '2026-06-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 50.00,
            'occasion' => 'Pâques',
            'financial_reference' => 'RF-2026-0001',
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        // Create corresponding financial entry (normally done by controller)
        FinancialEntry::create([
            'reference' => 'RF-2026-0001',
            'entry_date' => '2026-06-15',
            'type' => 'income',
            'category' => 'offrande',
            'source_type' => Offrande::class,
            'source_id' => $offrande->id,
            'description' => 'Offrande: Pâques - Membre ' . $offrande->member_id,
            'amount' => 50.00,
            'payment_method' => 'cash',
            'created_by' => $this->user->id,
            'updated_by' => $this->user->id,
        ]);

        $history = new MemberFinancialHistory($this->member, null, null, 2026);
        $donationHistory = $history->getDonationHistory();

        $this->assertCount(1, $donationHistory);
        $donation = $donationHistory->first();

        // Should have financial reference
        $this->assertEquals('RF-2026-0001', $donation['financial_reference']);

        // Should have corresponding financial entry
        $this->assertDatabaseHas('financial_entries', [
            'reference' => 'RF-2026-0001',
            'source_type' => Offrande::class,
            'amount' => 50.00,
            'type' => 'income',
            'category' => 'offrande',
        ]);
    }
}