<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_secretary_can_create_a_member_with_an_immutable_reference(): void
    {
        $role = Role::create(['name' => 'Secretary', 'slug' => 'secretary']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $this->assertTrue($user->hasPermission('members.create'));
        $this->actingAs($user)->post(route('members.store'), ['given_name' => 'Mebrat', 'family_name' => 'Tesfay', 'status' => 'active'])->assertRedirect();
        $this->assertDatabaseHas('members', ['member_code' => 'MEM-'.now()->format('Y').'-'.now()->format('m').'-001', 'given_name' => 'Mebrat']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'member.created']);
    }

    public function test_reviewer_cannot_create_members(): void
    {
        $role = Role::create(['name' => 'Reviewer', 'slug' => 'reviewer']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        $this->actingAs($user)->post(route('members.store'), ['given_name' => 'Mebrat', 'family_name' => 'Tesfay', 'status' => 'active'])->assertForbidden();
        $this->assertSame(0, Member::count());
    }

    public function test_secretary_can_print_members(): void
    {
        $role = Role::create(['name' => 'Secretary', 'slug' => 'secretary']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        Member::factory()->count(3)->create(['status' => 'active']);
        $this->actingAs($user)->get(route('members.print'))->assertOk()->assertSee('MEM-');
    }

    public function test_print_respects_search_filter(): void
    {
        $role = Role::create(['name' => 'Secretary', 'slug' => 'secretary']);
        $user = User::factory()->create();
        $user->roles()->attach($role);
        Member::factory()->create(['given_name' => 'Mebrat', 'family_name' => 'Tesfay', 'status' => 'active']);
        Member::factory()->create(['given_name' => 'Abraham', 'family_name' => 'Gebre', 'status' => 'active']);
        $response = $this->actingAs($user)->get(route('members.print', ['q' => 'Mebrat']));
        $response->assertOk();
        $response->assertSee('Mebrat');
        $response->assertDontSee('Abraham');
    }
}
