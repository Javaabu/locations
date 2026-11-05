<?php

namespace Javaabu\Locations\Tests\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\City;
use Javaabu\Locations\Models\Country;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Models\State;
use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class OptionsEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function defineRoutes($router): void
    {
        Route::locationOptions();
    }

    protected function seedIslands(): Atoll
    {
        $atoll = Atoll::factory()->haaAlifu()->create();

        Island::factory()->for($atoll)->create(['code' => 'LD0568', 'name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);
        Island::factory()->for($atoll)->category(IslandCategories::TOURISM)->create(['name' => 'Manafaru', 'dhivehi_name' => 'މަނަފަރު']);
        Island::factory()->for($atoll)->category(IslandCategories::UNINHABITED)->create(['name' => null, 'dhivehi_name' => null]);
        Island::factory()->for(Atoll::factory()->male())->create(['name' => 'Malé', 'dhivehi_name' => 'މާލެ']);

        return $atoll;
    }

    #[Test]
    public function it_returns_every_option_with_per_page_minus_one(): void
    {
        $atoll = $this->seedIslands();

        $this->getJson("/location-options/islands?filter[atoll]={$atoll->id}&per_page=-1&fields=id,name")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Dhidhdhoo')
            ->assertJsonPath('data.0.code', 'LD0568')
            ->assertJsonMissingPath('next_page_url');
    }

    #[Test]
    public function it_takes_an_atoll_code(): void
    {
        $this->seedIslands();

        $this->getJson('/location-options/islands?filter[atoll]=HA&per_page=-1')->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_searches_in_thaana(): void
    {
        $this->seedIslands();

        $this->getJson('/location-options/islands?filter[search]=' . urlencode('ދިއް'))
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Dhidhdhoo')
            ->assertJsonPath('current_page', 1);
    }

    #[Test]
    public function it_labels_options_in_the_current_language(): void
    {
        $this->seedIslands();

        app()->setLocale('dv');

        $this->getJson('/location-options/atolls?per_page=-1')
            ->assertJsonPath('data.0.name', 'މާލެ ސިޓީ')
            ->assertJsonPath('data.1.name', 'ހއ. ހާ އަލިފު');
    }

    #[Test]
    public function it_filters_islands_by_category_and_inhabited(): void
    {
        $this->seedIslands();

        $this->getJson('/location-options/islands?filter[category]=tourism&per_page=-1')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Manafaru');

        $this->getJson('/location-options/islands?filter[inhabited]=1&per_page=-1')->assertJsonCount(2, 'data');
        $this->getJson('/location-options/islands?filter[named]=0&per_page=-1')->assertJsonCount(4, 'data');
    }

    #[Test]
    public function it_lists_atolls_for_the_maldives_only(): void
    {
        $this->seedIslands();
        $maldives = Country::factory()->maldives()->create();
        $sri_lanka = Country::factory()->create(['code' => 'LK']);

        $this->getJson("/location-options/atolls?filter[country]={$maldives->id}&per_page=-1")->assertJsonCount(2, 'data');
        $this->getJson('/location-options/atolls?filter[country]=MV&per_page=-1')->assertJsonCount(2, 'data');
        $this->getJson("/location-options/atolls?filter[country]={$sri_lanka->id}&per_page=-1")->assertJsonCount(0, 'data');
    }

    #[Test]
    public function it_filters_states_and_cities(): void
    {
        $country = Country::factory()->create(['code' => 'LK']);
        $state = State::factory()->for($country)->create(['name' => 'Western Province']);
        City::factory()->for($country)->for($state)->create(['name' => 'Colombo']);
        City::factory()->for($country)->create(['name' => 'Kandy']);

        $this->getJson('/location-options/states?filter[country]=LK')->assertJsonPath('data.0.name', 'Western Province');
        $this->getJson("/location-options/cities?filter[state]={$state->id}")->assertJsonCount(1, 'data');
        $this->getJson("/location-options/cities?filter[country]={$country->id}")->assertJsonCount(2, 'data');
    }

    #[Test]
    public function it_paginates(): void
    {
        $atoll = Atoll::factory()->create();
        Island::factory()->count(30)->for($atoll)->create();

        $this->getJson('/location-options/islands?per_page=10&page=2')
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('last_page', 3);
    }

    #[Test]
    public function it_ignores_unknown_and_malformed_filters(): void
    {
        $this->seedIslands();

        $this->getJson('/location-options/islands?filter[unknown]=1&filter[atoll][]=1&per_page=-1')->assertJsonCount(3, 'data');
        $this->getJson('/location-options/islands?filter=oops&per_page=-1')->assertJsonCount(3, 'data');
    }

    #[Test]
    public function apps_can_register_it_under_their_own_prefix(): void
    {
        Route::locationOptions(['prefix' => 'api/locations']);

        $this->seedIslands();

        $this->getJson('/api/locations/atolls')->assertOk();
    }
}
