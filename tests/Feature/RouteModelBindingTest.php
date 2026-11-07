<?php

namespace Tests\Feature;

use App\Models\Contribution;
use App\Models\ContributionNotice;
use App\Models\Member;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route as RouteFacade;
use Tests\TestCase;

class RouteModelBindingTest extends TestCase
{
    use RefreshDatabase;

    private User $secretary;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::create(['name' => 'Secretary', 'slug' => 'secretary']);
        $this->secretary = User::factory()->create();
        $this->secretary->roles()->attach($role);
    }

    public function test_contribution_notice_show_page_loads_the_bound_notice(): void
    {
        $member = Member::factory()->create(['status' => 'active']);
        $contribution = Contribution::create([
            'reference' => 'COT-'.now()->year.'-10',
            'year' => now()->year,
            'month' => 10,
            'amount_per_member' => 10.00,
            'expected_count' => 1,
            'received_count' => 0,
            'total_expected' => 10.00,
            'total_received' => 0,
            'status' => 'open',
            'created_by' => $this->secretary->id,
            'updated_by' => $this->secretary->id,
        ]);

        $notice = ContributionNotice::create([
            'reference' => 'AVI-'.now()->year.'-0001',
            'member_id' => $member->id,
            'contribution_id' => $contribution->id,
            'notice_date' => now()->toDateString(),
            'due_date' => now()->addDays(15)->toDateString(),
            'amount' => 10.00,
            'status' => 'generated',
            'created_by' => $this->secretary->id,
            'updated_by' => $this->secretary->id,
        ]);

        $response = $this->actingAs($this->secretary)
            ->get(route('contribution-notices.show', $notice));

        $response->assertOk();
        $response->assertSee($notice->reference);
        $response->assertSee($member->member_code);
        $response->assertSee($contribution->reference);
    }

    public function test_resource_route_parameter_names_match_controller_arguments(): void
    {
        $expected = [
            'contribution-notices.show' => 'notice',
            'meeting-minutes.show' => 'minute',
            'exceptional-demands.show' => 'demand',
            'priest-transports.show' => 'transport',
            'priest-indemnities.show' => 'indemnity',
        ];

        foreach ($expected as $routeName => $parameterName) {
            $route = RouteFacade::getRoutes()->getByName($routeName);

            $this->assertNotNull($route, "Route [{$routeName}] is not registered.");

            preg_match_all('/\{(\w+)\}/', $route->uri(), $matches);

            $this->assertSame(
                [$parameterName],
                $matches[1],
                "Route [{$routeName}] must bind its model to \${$parameterName} so implicit binding resolves it."
            );
        }
    }
}
