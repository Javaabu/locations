<?php

namespace Javaabu\Locations\Tests\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class BladePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function defineRoutes($router): void
    {
        Route::middleware('web')->group(function () {
            Route::locations();
            Route::locationOptions();
        });
    }

    #[Test]
    public function it_lists_islands(): void
    {
        $atoll = Atoll::factory()->haaAlifu()->create();
        Island::factory()->for($atoll)->create(['code' => 'LD0568', 'name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);

        $this->actingAs($this->locationsAdmin())
            ->get('/locations/islands')
            ->assertOk()
            ->assertSee('data-locations-index="islands"', false)
            ->assertSee('Dhidhdhoo')
            ->assertSee('ދިއްދޫ')
            ->assertSee('HA. Haa Alifu');
    }

    #[Test]
    public function it_shows_the_create_form(): void
    {
        Atoll::factory()->haaAlifu()->create();

        $this->actingAs($this->locationsAdmin())
            ->get('/locations/islands/create')
            ->assertOk()
            ->assertSee('name="atoll_id"', false)
            ->assertSee('name="dhivehi_name"', false)
            ->assertSee('HA. Haa Alifu');
    }

    #[Test]
    public function it_creates_an_island(): void
    {
        $atoll = Atoll::factory()->haaAlifu()->create();

        $this->actingAs($this->locationsAdmin())
            ->post('/locations/islands', [
                'atoll_id' => $atoll->id,
                'code' => 'LD9999',
                'name' => 'Huvadhoo',
                'dhivehi_name' => 'ހުވަދޫ',
                'category' => 'uninhabited',
                'latitude' => '6.9',
                'longitude' => '73.1',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('islands', [
            'code' => 'LD9999',
            'name' => 'Huvadhoo',
            'dhivehi_name' => 'ހުވަދޫ',
            'atoll_id' => $atoll->id,
            'category' => IslandCategories::UNINHABITED->value,
        ]);
    }

    #[Test]
    public function it_shows_and_edits_an_island(): void
    {
        $island = Island::factory()->for(Atoll::factory()->haaAlifu())->create(['name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);
        $admin = $this->locationsAdmin();

        $this->actingAs($admin)->get("/locations/islands/{$island->id}")
            ->assertOk()
            ->assertSee('HA. Dhidhdhoo')
            ->assertSee('data-location-details', false);

        $this->actingAs($admin)->get("/locations/islands/{$island->id}/edit")
            ->assertOk()
            ->assertSee('value="Dhidhdhoo"', false);

        $this->actingAs($admin)->patch("/locations/islands/{$island->id}", [
            'atoll_id' => $island->atoll_id,
            'code' => $island->code,
            'name' => 'Dhihdhoo',
            'dhivehi_name' => 'ދިއްދޫ',
        ])->assertRedirect("/locations/islands/{$island->id}");

        $this->assertEquals('Dhihdhoo', $island->refresh()->name);
    }

    #[Test]
    public function it_deletes_an_island(): void
    {
        $island = Island::factory()->for(Atoll::factory())->create();

        $this->actingAs($this->locationsAdmin())
            ->delete("/locations/islands/{$island->id}")
            ->assertRedirect('/locations/islands');

        $this->assertDatabaseMissing('islands', ['id' => $island->id]);
    }

    #[Test]
    public function it_wont_delete_an_atoll_with_islands(): void
    {
        $island = Island::factory()->for(Atoll::factory())->create();

        $this->actingAs($this->locationsAdmin())
            ->delete("/locations/atolls/{$island->atoll_id}")
            ->assertRedirect("/locations/atolls/{$island->atoll_id}")
            ->assertSessionHas('error');

        $this->assertDatabaseHas('atolls', ['id' => $island->atoll_id]);
    }

    #[Test]
    public function it_renders_every_resource(): void
    {
        $admin = $this->locationsAdmin();

        foreach (['countries', 'states', 'cities', 'atolls', 'islands'] as $resource) {
            $this->actingAs($admin)->get("/locations/{$resource}")->assertOk();
            $this->actingAs($admin)->get("/locations/{$resource}/create")->assertOk();
        }
    }
}
