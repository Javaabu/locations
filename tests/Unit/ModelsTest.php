<?php

namespace Javaabu\Locations\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\City;
use Javaabu\Locations\Models\Country;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Models\State;
use Javaabu\Locations\Tests\Fixtures\Models\CustomIsland;
use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

class ModelsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_gives_a_country_its_flag_and_calling_code(): void
    {
        $country = Country::factory()->maldives()->create();

        $this->assertEquals('🇲🇻', $country->flag);
        $this->assertEquals('+960', $country->formattedCallingCode());
        $this->assertTrue($country->isMaldives());
        $this->assertTrue($country->is(Country::findByCode('mv')));
    }

    #[Test]
    public function it_relates_countries_states_and_cities(): void
    {
        $country = Country::factory()->create(['code' => 'LK', 'name' => 'Sri Lanka']);
        $state = State::factory()->for($country)->create(['code' => 'LK-1', 'name' => 'Western Province']);
        $city = City::factory()->for($country)->for($state)->create(['name' => 'Colombo']);

        $this->assertTrue($state->country->is($country));
        $this->assertTrue($city->state->is($state));
        $this->assertTrue($country->cities->first()->is($city));
        $this->assertTrue($state->cities->first()->is($city));
        $this->assertEquals(1, State::query()->ofCountry('LK')->count());
        $this->assertEquals(1, State::query()->ofCountry($country)->count());
        $this->assertEquals(1, City::query()->ofCountry($country->id)->ofState($state)->count());
    }

    #[Test]
    public function it_shows_dhivehi_names_for_dhivehi_locales(): void
    {
        $atoll = Atoll::factory()->haaAlifu()->create();

        $this->assertEquals('Haa Alifu', $atoll->localizedName('en'));
        $this->assertEquals('ހާ އަލިފު', $atoll->localizedName('dv'));
        $this->assertEquals('HA. Haa Alifu', $atoll->optionLabel());
        $this->assertEquals('ތިލަދުންމަތީ އުތުރުބުރި', $atoll->localizedOfficialName('dv'));

        app()->setLocale('dv');

        $this->assertEquals('ހާ އަލިފު', $atoll->localized_name);
        $this->assertEquals('ހއ. ހާ އަލިފު', $atoll->optionLabel());
        $this->assertEquals(['id' => $atoll->id, 'name' => 'ހއ. ހާ އަލިފު'], $atoll->toOption());
    }

    #[Test]
    public function it_falls_back_to_the_english_name_without_a_dhivehi_name(): void
    {
        $country = Country::factory()->create(['name' => 'Wakanda', 'dhivehi_name' => null]);

        $this->assertEquals('Wakanda', $country->localizedName('dv'));
    }

    #[Test]
    public function it_relates_atolls_and_islands(): void
    {
        $atoll = Atoll::factory()->haaAlifu()->create();
        $capital = Island::factory()->for($atoll)->capital()->create(['name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);
        Island::factory()->for($atoll)->category(IslandCategories::TOURISM)->create();
        Island::factory()->for($atoll)->category(IslandCategories::UNINHABITED)->create(['name' => null]);

        $this->assertTrue($atoll->capital->is($capital));
        $this->assertCount(3, $atoll->islands);
        $this->assertEquals(1, Island::query()->inhabited()->count());
        $this->assertEquals(2, Island::query()->named()->count());
        $this->assertEquals(1, Island::query()->capitals()->count());
        $this->assertEquals(2, Island::query()->category([IslandCategories::TOURISM, 'uninhabited'])->count());
        $this->assertEquals(3, Island::query()->ofAtoll('HA')->count());
        $this->assertEquals(3, Island::query()->ofAtoll($atoll)->count());
        $this->assertTrue($capital->isInhabited());
        $this->assertSame(IslandCategories::RESIDENTIAL, $capital->category);
    }

    #[Test]
    public function it_formats_island_names_as_addresses_write_them(): void
    {
        $atoll = Atoll::factory()->haaAlifu()->create();
        $island = Island::factory()->for($atoll)->create(['name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);
        $male = Island::factory()->for(Atoll::factory()->male())->create(['name' => 'Malé', 'dhivehi_name' => 'މާލެ']);

        $this->assertEquals('HA. Dhidhdhoo', $island->formattedName('en'));
        $this->assertEquals('ހއ. ދިއްދޫ', $island->formattedName('dv'));
        $this->assertEquals('Malé', $male->formatted_name);
        $this->assertEquals('މާލެ', $male->formattedName('dv'));
    }

    #[Test]
    public function it_searches_names_in_english_and_thaana_and_codes(): void
    {
        $atoll = Atoll::factory()->haaAlifu()->create();
        Island::factory()->for($atoll)->create(['code' => 'LD0568', 'name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);
        Island::factory()->for($atoll)->create(['code' => 'LD0246', 'name' => 'Hoarafushi', 'dhivehi_name' => 'ހުވަރަފުށި']);

        $this->assertEquals(['Dhidhdhoo'], Island::query()->search('ދިއް')->pluck('name')->all());
        $this->assertEquals(['Hoarafushi'], Island::query()->search('hoara')->pluck('name')->all());
        $this->assertEquals(['Hoarafushi'], Island::query()->search('LD0246')->pluck('name')->all());
        $this->assertEquals(2, Island::query()->search('  ')->count());
    }

    #[Test]
    public function it_orders_by_the_name_in_the_current_language(): void
    {
        $atoll = Atoll::factory()->create();
        Island::factory()->for($atoll)->create(['name' => 'Alifushi', 'dhivehi_name' => 'އަލިފުށި']);
        Island::factory()->for($atoll)->create(['name' => 'Baarah', 'dhivehi_name' => 'ބާރަށް']);

        $this->assertEquals(['Alifushi', 'Baarah'], Island::query()->ordered('en')->pluck('name')->all());
        // Thaana order: ހ ށ ނ ރ ބ ޅ ކ އ …, so ބ comes before އ
        $this->assertEquals(['Baarah', 'Alifushi'], Island::query()->ordered('dv')->pluck('name')->all());
    }

    #[Test]
    public function it_orders_atolls_north_to_south(): void
    {
        Atoll::factory()->male()->create();
        Atoll::factory()->haaAlifu()->create();

        $this->assertEquals(['HA', 'MLE'], Atoll::query()->ordered()->pluck('code')->all());
    }

    #[Test]
    public function it_uses_the_models_from_config(): void
    {
        config(['locations.models.island' => CustomIsland::class]);

        $atoll = Atoll::factory()->create();
        Island::factory()->for($atoll)->create(['name' => 'Fenfushi']);

        $this->assertEquals(CustomIsland::class, Locations::islandModel());
        $this->assertInstanceOf(CustomIsland::class, $atoll->islands->first());
        $this->assertEquals('FENFUSHI', $atoll->islands->first()->shout());
    }

    #[Test]
    public function it_rejects_unknown_location_types(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Locations::model('village');
    }

    #[Test]
    public function it_knows_the_type_of_a_model(): void
    {
        $this->assertEquals('island', Locations::typeOf(new CustomIsland));
        $this->assertEquals('country', Locations::typeOf(Country::class));
        $this->assertNull(Locations::typeOf(new stdClass));
        $this->assertEquals('islands', Locations::resource('island'));
    }
}
