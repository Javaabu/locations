<?php

namespace Javaabu\Locations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Locations\Data\LocationData;
use Javaabu\Locations\Database\Seeders\AtollsSeeder;
use Javaabu\Locations\Database\Seeders\CountriesSeeder;
use Javaabu\Locations\Database\Seeders\IslandsSeeder;
use Javaabu\Locations\Database\Seeders\LocationsSeeder;
use Javaabu\Locations\Database\Seeders\StatesSeeder;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Country;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Models\State;
use Javaabu\Locations\Seeding\LocationsImporter;
use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class SeedingTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_seeds_every_country_atoll_and_named_island(): void
    {
        $this->seed(LocationsSeeder::class);

        $this->assertEquals(249, Country::query()->count());
        $this->assertEquals(21, Atoll::query()->count());
        $this->assertEquals(LocationData::islands()->count(), Island::query()->count());
        $this->assertEquals(0, Island::query()->whereNull('name')->count());
        $this->assertEquals(0, State::query()->count());
    }

    #[Test]
    public function it_seeds_the_maldives_with_its_codes_and_dhivehi_name(): void
    {
        $this->seed(CountriesSeeder::class);

        $this->assertDatabaseHas('countries', [
            'code' => 'MV',
            'alpha3_code' => 'MDV',
            'numeric_code' => '462',
            'name' => 'Maldives',
            'dhivehi_name' => 'ދިވެހިރާއްޖެ',
            'official_name' => 'Republic of Maldives',
            'calling_code' => '960',
        ]);

        $this->assertEquals('ސިންގަޕޫރު', Country::findByCode('SG')->localizedName('dv'));
    }

    #[Test]
    public function it_seeds_atolls_with_their_official_codes(): void
    {
        $this->seed(AtollsSeeder::class);

        $this->assertDatabaseHas('atolls', [
            'code' => 'HA',
            'dhivehi_code' => 'ހއ',
            'letter_code' => 'A',
            'iso_code' => 'MV-07',
            'name' => 'Haa Alifu',
            'dhivehi_name' => 'ހާ އަލިފު',
            'official_name' => 'Thiladhunmathi Uthuruburi',
            'official_dhivehi_name' => 'ތިލަދުންމަތީ އުތުރުބުރި',
            'is_city' => false,
        ]);

        $this->assertEquals(['MLE', 'HA', 'HDh'], Atoll::query()->ordered()->limit(3)->pluck('code')->all());
        $this->assertEquals(['MLE', 'Gn', 'S'], Atoll::query()->where('is_city', true)->ordered()->pluck('code')->all());
    }

    #[Test]
    public function it_seeds_islands_from_the_register(): void
    {
        $this->seed(IslandsSeeder::class);

        $male = Island::findByCode('LD0442');

        $this->assertEquals('Malé', $male->name);
        $this->assertEquals('މާލެ', $male->dhivehi_name);
        $this->assertEquals('MLE', $male->atoll->code);
        $this->assertTrue($male->is_capital);
        $this->assertSame(IslandCategories::RESIDENTIAL, $male->category);
        $this->assertEqualsWithDelta(4.1744, $male->latitude, 0.001);
        $this->assertEqualsWithDelta(73.5097, $male->longitude, 0.001);

        $capital = Atoll::findByCode('HA')->capital;

        $this->assertEquals('Dhidhdhoo', $capital->name);
        $this->assertEquals('ހއ. ދިއްދޫ', $capital->formattedName('dv'));
        $this->assertEquals(21, Island::query()->capitals()->count());
        $this->assertGreaterThan(180, Island::query()->inhabited()->count());
        $this->assertGreaterThan(150, Island::query()->category(IslandCategories::TOURISM)->count());
    }

    #[Test]
    public function it_includes_unnamed_islands_when_asked(): void
    {
        config(['locations.seed.unnamed_islands' => true]);

        $this->seed(IslandsSeeder::class);

        $this->assertEquals(1560, Island::query()->count());
        $this->assertGreaterThan(400, Island::query()->whereNull('name')->count());
    }

    #[Test]
    public function it_keeps_existing_rows_unless_asked_to_update_them(): void
    {
        $this->seed(CountriesSeeder::class);

        Country::findByCode('MV')->update(['name' => 'ދިވެހިރާއްޖެ', 'calling_code' => '000']);

        $result = (new LocationsImporter)->countries();

        $this->assertEquals(0, $result->created);
        $this->assertEquals(249, $result->skipped);
        $this->assertEquals(249, Country::query()->count());
        $this->assertEquals('000', Country::findByCode('MV')->calling_code);

        $result = (new LocationsImporter)->updatingExisting()->countries();

        $this->assertEquals(249, $result->updated);
        $this->assertEquals(249, $result->total());
        $this->assertEquals('960', Country::findByCode('MV')->calling_code);
        $this->assertEquals('Maldives', Country::findByCode('MV')->name);
    }

    #[Test]
    public function it_adds_locations_missing_from_the_database(): void
    {
        $this->seed(CountriesSeeder::class);

        Country::findByCode('LK')->delete();

        $result = (new LocationsImporter)->countries();

        $this->assertEquals(1, $result->created);
        $this->assertNotNull(Country::findByCode('LK'));
    }

    #[Test]
    public function it_seeds_iso_3166_2_states(): void
    {
        $this->seed(CountriesSeeder::class);
        $this->seed(StatesSeeder::class);

        $this->assertDatabaseHas('states', ['code' => 'LK-1', 'name' => 'Western Province', 'type' => 'Province']);
        $this->assertEquals(0, State::query()->ofCountry('MV')->count());
        $this->assertGreaterThan(3000, State::query()->count());
    }

    #[Test]
    public function it_seeds_the_states_of_some_countries_only(): void
    {
        $this->seed(CountriesSeeder::class);

        (new LocationsImporter)->states(['lk', 'IN']);

        $this->assertEquals(['IN', 'LK'], State::query()->with('country')->get()->pluck('country.code')->unique()->sort()->values()->all());
    }

    #[Test]
    public function it_needs_the_atolls_before_the_islands(): void
    {
        $this->expectException(RuntimeException::class);

        (new LocationsImporter)->islands();
    }

    #[Test]
    public function it_seeds_from_the_command(): void
    {
        $this->artisan('locations:seed')
            ->expectsOutputToContain('Seeding countries')
            ->expectsOutputToContain('Pass --update')
            ->assertSuccessful();

        $this->assertEquals(249, Country::query()->count());
        $this->assertGreaterThan(1000, Island::query()->count());

        $this->artisan('locations:seed', ['types' => ['countries'], '--update' => true])->assertSuccessful();
        $this->artisan('locations:seed', ['types' => ['states'], '--country' => ['LK']])->assertSuccessful();

        $this->assertEquals(9, State::query()->count());
    }

    #[Test]
    public function it_rejects_unknown_types_in_the_command(): void
    {
        $this->artisan('locations:seed', ['types' => ['villages']])
            ->expectsOutputToContain('Unknown types: villages')
            ->assertFailed();
    }
}
