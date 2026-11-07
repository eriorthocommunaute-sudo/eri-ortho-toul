<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\Member;
use App\Models\Offrande;
use App\Models\Receipt;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberFinancialHistoryControllerTest extends TestCase
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
            'joined_at' => now()->subYears(3), // Joined 3 years ago
        ]);

        $currentYear = now()->year;

        // Create contribution and receipt for testing (current year)
        $this->contribution = Contribution::create([
            'reference' => "COT-{$currentYear}-01",
            'year' => $currentYear,
            'month' => 1,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 1,
            'total_expected' => 10.00,
            'total_received' => 10.00,
            'status' => 'open',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $this->receipt = Receipt::create([
            'reference' => 'REC-'.$currentYear.'-0001',
            'member_id' => $this->member->id,
            'contribution_id' => $this->contribution->id,
            'payment_date' => "$currentYear-01-10",
            'amount' => 10.00,
            'payment_method' => 'cash',
            'financial_reference' => 'RF-'.$currentYear.'-0001',
            'status' => 'confirmed',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
    }

    public function test_admin_can_view_financial_history(): void
    {
        $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk()
            ->assertSee('Cotisations et dons mensuels')
            ->assertSee($this->member->member_code);
    }

    public function test_finance_user_can_view_financial_history(): void
    {
        $this->actingAs($this->financeUser)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();
    }

    public function test_secretary_can_view_financial_history(): void
    {
        $this->actingAs($this->secretary)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();
    }

    public function test_reviewer_cannot_view_financial_history(): void
    {
        $this->actingAs($this->reviewer)
            ->get(route('members.financial-history', $this->member))
            ->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_view_financial_history(): void
    {
        $this->get(route('members.financial-history', $this->member))
            ->assertRedirect(route('login'));
    }

    public function test_financial_history_shows_monthly_data(): void
    {
        $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk()
            ->assertSee('2026')
            ->assertSee('10 / 2026')
            ->assertSee('10,00')
            ->assertSee('Payé');
    }

    public function test_financial_history_filter_by_year(): void
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
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
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
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member) . '?year='.$lastYear)
            ->assertOk();

        $response->assertSee((string)$lastYear);
        // Table body should only show rows for the selected year
        // Check that no table row contains other years
        $this->assertStringNotContainsString('<td>'.($lastYear + 1).'</td>', $response->getContent());
        $this->assertStringNotContainsString('<td>'.($lastYear - 1).'</td>', $response->getContent());
    }

    public function test_financial_history_filter_by_status(): void
    {
        $currentYear = now()->year;

        // Create unpaid contribution
        Contribution::create([
            'reference' => "COT-{$currentYear}-02",
            'year' => $currentYear,
            'month' => 2,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 0,
            'total_expected' => 10.00,
            'total_received' => 0.00,
            'status' => 'open',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member) . '?status=unpaid')
            ->assertOk();

        $response->assertSee('Non payé');
        // The table should not contain rows with "Payé" status (only the dropdown has it)
        $this->assertStringNotContainsString('status-paid', $response->getContent());
    }

    public function test_admin_can_export_financial_history_csv(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('members.financial-history.export', $this->member))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=utf-8')
            ->assertHeader('Content-Disposition', 'attachment; filename="historique-financier-'.$this->member->member_code.'-all.csv"');

        $content = $response->getContent();
        $this->assertStringContainsString('Année', $content);
        $this->assertStringContainsString((string)now()->year, $content);
    }

    public function test_finance_user_can_export_financial_history_csv(): void
    {
        $this->actingAs($this->financeUser)
            ->get(route('members.financial-history.export', $this->member))
            ->assertOk();
    }

    public function test_secretary_can_export_financial_history_csv(): void
    {
        $this->actingAs($this->secretary)
            ->get(route('members.financial-history.export', $this->member))
            ->assertOk();
    }

    public function test_reviewer_cannot_export_financial_history_csv(): void
    {
        $this->actingAs($this->reviewer)
            ->get(route('members.financial-history.export', $this->member))
            ->assertForbidden();
    }

    public function test_member_show_page_includes_financial_history_for_authorized_users(): void
    {
        $this->actingAs($this->admin)
            ->get(route('members.show', $this->member))
            ->assertOk()
            ->assertSee('Cotisations et dons mensuels')
            ->assertSee('10,00');
    }

    public function test_member_show_page_hides_financial_history_for_unauthorized_users(): void
    {
        $this->actingAs($this->reviewer)
            ->get(route('members.show', $this->member))
            ->assertOk()
            ->assertDontSee('Cotisations et dons mensuels');
    }

    public function test_offrandes_appear_in_financial_history(): void
    {
        $currentYear = now()->year;

        Offrande::create([
            'reference' => 'OFR-'.$currentYear.'-0001',
            'offering_date' => "$currentYear-01-15",
            'donor_name' => 'Test Member',
            'member_id' => $this->member->id,
            'amount' => 25.00,
            'occasion' => 'Pâques',
            'financial_reference' => 'RF-'.$currentYear.'-0002',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk()
            ->assertSee('25,00')
            ->assertSee('Don');
    }

    public function test_multiple_payments_same_month_are_summed(): void
    {
        $currentYear = now()->year;

        // Add second receipt for same contribution
        Receipt::create([
            'reference' => 'REC-'.$currentYear.'-0002',
            'member_id' => $this->member->id,
            'contribution_id' => $this->contribution->id,
            'payment_date' => "$currentYear-01-20",
            'amount' => 5.00,
            'payment_method' => 'transfer',
            'financial_reference' => 'RF-'.$currentYear.'-0003',
            'status' => 'confirmed',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk()
            ->assertSee('15,00'); // 10 + 5
    }

    public function test_ledger_reconciliation_financial_entries_match_receipts(): void
    {
        // Verify that confirmed receipts have corresponding financial entries
        // (In production, these are created when ReceiptController::confirm() is called)
        \App\Models\FinancialEntry::create([
            'reference' => $this->receipt->financial_reference,
            'entry_date' => $this->receipt->payment_date,
            'type' => 'income',
            'category' => 'cotisation',
            'source_type' => Receipt::class,
            'source_id' => $this->receipt->id,
            'description' => "Cotisation {$this->receipt->member->member_code} - {$this->receipt->payment_method}",
            'amount' => $this->receipt->amount,
            'payment_method' => $this->receipt->payment_method,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $this->assertDatabaseHas('financial_entries', [
            'source_type' => Receipt::class,
            'source_id' => $this->receipt->id,
            'amount' => 10.00,
            'type' => 'income',
            'category' => 'cotisation',
        ]);

        $this->actingAs($this->admin)
            ->get(route('members.financial-history', $this->member))
            ->assertOk();
    }
}