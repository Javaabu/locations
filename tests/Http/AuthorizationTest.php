<?php

namespace Javaabu\Locations\Tests\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Policies\IslandPolicy;
use Javaabu\Locations\Tests\Fixtures\Policies\DenyAllPolicy;
use Javaabu\Locations\Tests\TestCase;
use Javaabu\Permissions\Permissions;
use PHPUnit\Framework\Attributes\Test;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function defineRoutes($router): void
    {
        Route::middleware('web')->group(function () {
            Route::locations();
        });
    }

    #[Test]
    public function it_registers_permissions_for_every_location_type(): void
    {
        foreach (['countries', 'states', 'cities', 'atolls', 'islands'] as $resource) {
            foreach (['view', 'edit', 'delete'] as $action) {
                $this->assertTrue(Permissions::has("{$action}_{$resource}"), "{$action}_{$resource} isn't registered");
            }
        }

        $this->assertEquals('View islands', Permissions::description('view_islands'));
        $this->assertInstanceOf(IslandPolicy::class, Gate::getPolicyFor(Island::class));
    }

    #[Test]
    public function it_redirects_guests_to_log_in(): void
    {
        Route::get('/login', fn () => 'login')->name('login');

        $this->get('/locations/islands')->assertRedirect('/login');
    }

    #[Test]
    public function it_needs_the_view_permission_to_list(): void
    {
        $this->actingAs($this->userWith([]))->get('/locations/islands')->assertForbidden();
        $this->actingAs($this->userWith(['view_islands']))->get('/locations/islands')->assertOk();
        $this->actingAs($this->userWith(['view_islands']))->get('/locations/atolls')->assertForbidden();
    }

    #[Test]
    public function it_needs_the_edit_permission_to_create_and_update(): void
    {
        $island = Island::factory()->for(Atoll::factory())->create();
        $viewer = $this->userWith(['view_islands']);

        $this->actingAs($viewer)->get('/locations/islands/create')->assertForbidden();
        $this->actingAs($viewer)->post('/locations/islands', [])->assertForbidden();
        $this->actingAs($viewer)->get("/locations/islands/{$island->id}/edit")->assertForbidden();
        $this->actingAs($viewer)->patch("/locations/islands/{$island->id}", [])->assertForbidden();

        $this->actingAs($this->userWith(['view_islands', 'edit_islands']))
            ->get("/locations/islands/{$island->id}/edit")
            ->assertOk();
    }

    #[Test]
    public function it_needs_the_delete_permission_to_delete(): void
    {
        $island = Island::factory()->for(Atoll::factory())->create();

        $this->actingAs($this->userWith(['view_islands', 'edit_islands']))
            ->delete("/locations/islands/{$island->id}")
            ->assertForbidden();

        $this->actingAs($this->userWith(['delete_islands']))
            ->delete("/locations/islands/{$island->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('islands', ['id' => $island->id]);
    }

    #[Test]
    public function it_shows_only_the_actions_the_user_may_take(): void
    {
        Island::factory()->for(Atoll::factory())->create(['name' => 'Fenfushi']);

        $this->actingAs($this->userWith(['view_islands']))
            ->get('/locations/islands')
            ->assertOk()
            ->assertSee('Fenfushi')
            ->assertDontSee('/locations/islands/create')
            ->assertDontSee('data-confirm', false);

        $this->actingAs($this->userWith(['view_islands', 'edit_islands', 'delete_islands']))
            ->get('/locations/islands')
            ->assertSee('/locations/islands/create')
            ->assertSee('data-confirm', false);
    }

    #[Test]
    public function apps_can_override_the_policy_with_the_gate(): void
    {
        Gate::policy(Island::class, DenyAllPolicy::class);

        $this->actingAs($this->locationsAdmin())->get('/locations/islands')->assertForbidden();
    }

    #[Test]
    public function apps_can_override_the_policy_from_config(): void
    {
        config(['permissions.policies' => [Island::class => DenyAllPolicy::class]]);

        Permissions::applyPolicies();

        $this->actingAs($this->locationsAdmin())->get('/locations/islands')->assertForbidden();
    }

    #[Test]
    public function it_returns_404_for_unknown_locations(): void
    {
        $this->actingAs($this->locationsAdmin())->get('/locations/islands/999')->assertNotFound();
    }
}
