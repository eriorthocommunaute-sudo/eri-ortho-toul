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
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberDonationHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminRole = Role::create(['name' => 'Administrator', 'slug' => 'administrator']);
        $this->financeRole = Role::create(['name' => 'Finance', 'slug' => 'finance']);
        $this->secretaryRole = Role::create(['name' => 'Secretary', 'slug' => 'secretary']);
        $this->reviewerRole = Role::create(['name' => 'Reviewer', 'slug' => 'reviewer']);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($this->adminRole);

        $this->financeUser = User::factory()->create();
        $this->financeUser->roles()->attach($this->financeRole);

        $this->secretary = User::factory()->create();
        $this->secretary->roles()->attach($this->secretaryRole);

        $this->reviewer = User::factory()->create();
        $this->reviewer->roles()->attach($this->reviewerRole);

        $this->member = Member::factory()->create([
            'given_name' => 'Test',
            'family_name' => 'Member',
            'status' => 'active',
            'joined_at' => now()->subYears(3),
        ]);

        $currentYear = now()->year;

        // Create contributions for both years
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
                    'created_by' => $this->admin->id,
                    'updated_by' => $this->admin->id,
                ]);
            }
        }

        // Create receipt for 2026 October (to test paid status)
        $contribOct2026 = Contribution::where('year', 2026)->where('month', 10)->first();
        Receipt::create([
            'reference' => 'REC-2026-0001',
            'member_id' => $this->member->id,
            'contribution_id' => $contribOct2026->id,
            'payment_date' => '2026-10-15',
            'amount' => 10.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-2026-0001',
            'status' => 'confirmed',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        // Create offrandes for both years
        $offrande2026 = Offrande::create([
            'reference' => 'OFR-2026-0001',
            'offering_date' => '2026-06-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 50.00,
            'occasion' => 'Pâques',
            'financial_reference' => 'RF-2026-0002',
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $offrande2027 = Offrande::create([
            'reference' => 'OFR-2027-0001',
            'offering_date' => '2027-03-15',
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 75.00,
            'occasion' => 'Noël',
            'financial_reference' => 'RF-2027-0001',
            'status' => 'confirmed',
            'payment_method' => 'transfer',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        // Create financial entries for offrandes
        FinancialEntry::create([
            'reference' => 'RF-2026-0002',
            'entry_date' => '2026-06-15',
            'type' => 'income',
            'category' => 'offrande',
            'source_type' => Offrande::class,
            'source_id' => $offrande2026->id,
            'description' => 'Offrande: Pâques - Membre ' . $offrande2026->member_id,
            'amount' => 50.00,
            'payment_method' => 'cash',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        FinancialEntry::create([
            'reference' => 'RF-2027-0001',
            'entry_date' => '2027-03-15',
            'type' => 'income',
            'category' => 'offrande',
            'source_type' => Offrande::class,
            'source_id' => $offrande2027->id,
            'description' => 'Offrande: Noël - Membre ' . $offrande2027->member_id,
            'amount' => 75.00,
            'payment_method' => 'transfer',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        // Create donations where donor matches member name
        $donation2026 = Donation::create([
            'reference' => 'DON-2026-0001',
            'donation_date' => '2026-07-20',
            'donor_name' => 'Test Member',
            'donor_type' => 'individual',
            'amount' => 100.00,
            'purpose' => 'Don général',
            'financial_reference' => 'RF-2026-0003',
            'status' => 'confirmed',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $donation2027 = Donation::create([
            'reference' => 'DON-2027-0001',
            'donation_date' => '2027-02-10',
            'donor_name' => 'Test Member',
            'donor_type' => 'individual',
            'amount' => 200.00,
            'purpose' => 'Don spécial',
            'financial_reference' => 'RF-2027-0002',
            'status' => 'confirmed',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        // Create financial entries for donations
        FinancialEntry::create([
            'reference' => 'RF-2026-0003',
            'entry_date' => '2026-07-20',
            'type' => 'income',
            'category' => 'don',
            'source_type' => Donation::class,
            'source_id' => $donation2026->id,
            'description' => 'Don: Test Member - Don général',
            'amount' => 100.00,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        FinancialEntry::create([
            'reference' => 'RF-2027-0002',
            'entry_date' => '2027-02-10',
            'type' => 'income',
            'category' => 'don',
            'source_type' => Donation::class,
            'source_id' => $donation2027->id,
            'description' => 'Don: Test Member - Don spécial',
            'amount' => 200.00,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
    }

    public function test_financial_history_shows_cotisation_table_with_2026_default(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();

        // Default year should be 2026
        $response->assertSee('2026');
        // Should show Oct, Nov, Dec for 2026
        $response->assertSee('10 / 2026');
        $response->assertSee('11 / 2026');
        $response->assertSee('12 / 2026');
    }

    public function test_cotisation_year_filter_works(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member) . '?year=2027')
            ->assertOk();

        // Should show all 12 months for 2027
        for ($month = 1; $month <= 12; $month++) {
            $response->assertSee("{$month} / 2027");
        }
    }

    public function test_donation_year_filter_works_independently(): void
    {
        // Test: cotisation year = 2026, donation year = 2027
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member) . '?year=2026&donation_year=2027')
            ->assertOk();

        // Cotisation should show 2026 (Oct-Dec)
        $response->assertSee('10 / 2026');
        $response->assertSee('12 / 2026');

        // Donation table should show 2027 donation (OFR-2027-0001)
        $response->assertSee('OFR-2027-0001');
        $response->assertSee('75,00');
        $response->assertSee('Noël');

        // Should NOT show 2026 donation
        $response->assertDontSee('OFR-2026-0001');
    }

    public function test_donation_table_shows_correct_columns(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();

        // Check donation table headers
        $response->assertSee('Référence du don');
        $response->assertSee('Date du don');
        $response->assertSee('Donateur');
        $response->assertSee('Catégorie');
        $response->assertSee('Montant (€)');
        $response->assertSee('Mode de paiement');
        $response->assertSee('Commentaire');
        $response->assertSee('Reçu');
        $response->assertSee('Actions');
    }

    public function test_donation_summary_cards_display(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();

        // Should show donation summary
        $response->assertSee('Total des dons de l\'année');
        $response->assertSee('Nombre de dons');
        $response->assertSee('Don moyen');
        // 2026: 1 offrande (50) + 1 donation (100) = 150 total, 2 count, 75 average
        $response->assertSee('150,00');
        $response->assertSee('2');
        $response->assertSee('75,00');
    }

    public function test_donation_category_breakdown_displayed(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();

        $response->assertSee('Dons par catégorie');
        $response->assertSee('Pâques');
        $response->assertSee('Don général');
    }

    public function test_empty_donation_year_shows_empty_message(): void
    {
        // 2025 has no donations
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member) . '?donation_year=2025')
            ->assertOk();

        $response->assertSee('Aucun don enregistré pour cette année');
    }

    public function test_donation_links_to_offrande_and_donation_details(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();

        // Should have links to offrandes.show and donations.show
        $response->assertSee(route('offrandes.show', 1)); // OFR-2026-0001
        $response->assertSee(route('donations.show', 1)); // DON-2026-0001
    }

    public function test_receipt_link_in_cotisation_table(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();

        // October 2026 has a receipt, should have view link
        $response->assertSee('receipts/1');
    }

    public function test_financial_entry_link_in_donation_table(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();

        // Donations with financial_reference should have link
        $response->assertSee('RF-2026-0002'); // Offrande financial reference
        $response->assertSee('RF-2026-0003'); // Donation financial reference
    }

    public function test_unauthorized_user_cannot_view(): void
    {
        $this->actingAs($this->reviewer)
            ->get(route('members.financial-history', $this->member))
            ->assertForbidden();
    }

    public function test_member_show_page_includes_donation_history(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.show', $this->member))
            ->assertOk();

        // Should show donation history section
        $response->assertSee('Historique des dons par année');
        $response->assertSee('Total des dons de l\'année');
        $response->assertSee('OFR-2026-0001');
        $response->assertSee('DON-2026-0001');
    }

    public function test_changing_year_does_not_create_financial_entries(): void
    {
        $initialCount = FinancialEntry::count();

        $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member) . '?year=2027&donation_year=2027')
            ->assertOk();

        $this->assertEquals($initialCount, FinancialEntry::count());
    }

    public function test_donation_totals_match_financial_ledger(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();

        // Total in UI should match sum of confirmed financial entries
        $ledgerTotal = FinancialEntry::where('source_type', Offrande::class)
            ->where('source_id', 1)
            ->where('type', 'income')
            ->sum('amount');

        $ledgerTotal += FinancialEntry::where('source_type', Donation::class)
            ->where('source_id', 1)
            ->where('type', 'income')
            ->sum('amount');

        // 50 (offrande) + 100 (donation) = 150
        $this->assertEquals(150.00, $ledgerTotal);
        $response->assertSee('150,00');
    }
}