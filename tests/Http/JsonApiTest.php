<?php

namespace Javaabu\Locations\Tests\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Country;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Tests\Fixtures\Controllers\CustomIslandsController;
use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class JsonApiTest extends TestCase
{
    use RefreshDatabase;

    protected function defineRoutes($router): void
    {
        Route::middleware('web')->group(function () {
            Route::locations();

            Route::prefix('custom')->name('custom.')->group(function () {
                Route::locations(['only' => ['islands'], 'controllers' => ['islands' => CustomIslandsController::class]]);
            });
        });
    }

    #[Test]
    public function it_lists_locations_as_json(): void
    {
        $atoll = Atoll::factory()->haaAlifu()->create();
        Island::factory()->for($atoll)->create(['code' => 'LD0568', 'name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);

        $this->actingAs($this->locationsAdmin())
            ->getJson('/locations/islands?atoll=HA')
            ->assertOk()
            ->assertJsonPath('data.0.code', 'LD0568')
            ->assertJsonPath('data.0.type', 'island')
            ->assertJsonPath('data.0.dhivehi_name', 'ދިއްދޫ')
            ->assertJsonPath('data.0.formatted_name', 'HA. Dhidhdhoo')
            ->assertJsonPath('data.0.category', 'residential')
            ->assertJsonPath('data.0.atoll.code', 'HA')
            ->assertJsonPath('data.0.can.update', true)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonMissingPath('data.0.translations');
    }

    #[Test]
    public function it_filters_the_list(): void
    {
        $atoll = Atoll::factory()->haaAlifu()->create();
        Island::factory()->for($atoll)->create(['name' => 'Dhidhdhoo', 'category' => 'residential']);
        Island::factory()->for($atoll)->create(['name' => 'Manafaru', 'category' => 'tourism']);
        Island::factory()->for(Atoll::factory())->create(['name' => 'Fenfushi', 'category' => 'residential']);

        $admin = $this->locationsAdmin();

        $this->actingAs($admin)->getJson('/locations/islands?atoll=HA')->assertJsonCount(2, 'data');
        $this->actingAs($admin)->getJson('/locations/islands?atoll=HA&category=tourism')->assertJsonPath('data.0.name', 'Manafaru');
        $this->actingAs($admin)->getJson('/locations/islands?search=fen')->assertJsonCount(1, 'data');
    }

    #[Test]
    public function it_creates_updates_and_deletes_with_json(): void
    {
        $admin = $this->locationsAdmin();

        $id = $this->actingAs($admin)
            ->postJson('/locations/countries', ['code' => 'XK', 'name' => 'Kosovo', 'dhivehi_name' => 'ކޮސޮވޯ', 'calling_code' => '383'])
            ->assertCreated()
            ->assertJsonPath('data.code', 'XK')
            ->assertJsonPath('data.localized_name', 'Kosovo')
            ->json('data.id');

        $this->actingAs($admin)
            ->patchJson("/locations/countries/{$id}", ['code' => 'XK', 'name' => 'Republic of Kosovo'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Republic of Kosovo');

        $this->actingAs($admin)->deleteJson("/locations/countries/{$id}")->assertNoContent();

        $this->assertDatabaseMissing('countries', ['code' => 'XK']);
    }

    #[Test]
    public function it_validates_with_the_form(): void
    {
        Country::factory()->create(['code' => 'MV']);

        $this->actingAs($this->locationsAdmin())
            ->postJson('/locations/countries', ['code' => 'MV', 'name' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['code', 'name']);
    }

    #[Test]
    public function it_validates_the_parent_exists(): void
    {
        $this->actingAs($this->locationsAdmin())
            ->postJson('/locations/islands', ['atoll_id' => 999, 'code' => 'LD9999', 'name' => 'Huraa'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['atoll_id']);
    }

    #[Test]
    public function it_refuses_to_delete_a_location_with_children(): void
    {
        $island = Island::factory()->for(Atoll::factory())->create();

        $this->actingAs($this->locationsAdmin())
            ->deleteJson("/locations/atolls/{$island->atoll_id}")
            ->assertConflict();
    }

    #[Test]
    public function it_returns_the_form_schema_for_create_and_edit(): void
    {
        $island = Island::factory()->for(Atoll::factory())->create();
        $admin = $this->locationsAdmin();

        $this->actingAs($admin)->getJson('/locations/islands/create')->assertOk()->assertJsonPath('data.form.stencil', 1);
        $this->actingAs($admin)->getJson("/locations/islands/{$island->id}/edit")->assertOk()->assertJsonPath('data.location.id', $island->id);
    }

    #[Test]
    public function apps_can_swap_the_controller(): void
    {
        Island::factory()->for(Atoll::factory())->create(['name' => 'Fenfushi']);

        $this->actingAs($this->locationsAdmin())
            ->get('/custom/locations/islands')
            ->assertOk()
            ->assertSee('Island name')
            ->assertSee('FENFUSHI');
    }
}
