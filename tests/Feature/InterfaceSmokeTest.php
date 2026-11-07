<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterfaceSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        $role = Role::create(['name' => 'Administrator', 'slug' => 'administrator']);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    public function test_authenticated_pages_render_with_the_new_layout(): void
    {
        $user = $this->administrator();

        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee(__('app.financial_overview'));
        $this->actingAs($user)->get(route('members.index'))->assertOk()->assertSee(__('app.add_member'));
        $this->actingAs($user)->get(route('receipts.index'))->assertOk()->assertSee(__('app.receipts'));
        $this->actingAs($user)->get(route('financial-entries.report'))->assertOk()->assertSee(__('app.financial_report'));
        $this->actingAs($user)->get(route('pdf.index'))->assertOk()->assertSee(__('app.pdf_export'));
    }

    public function test_guest_pages_render_with_the_new_design(): void
    {
        $this->get(route('home'))->assertOk()->assertSee(__('app.landing_title'));
        $this->get(route('login'))->assertOk()->assertSee(__('app.login_heading'));
    }
}
