<?php

namespace Javaabu\Locations\Tests\Translatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Models\Translatable\Island as TranslatableIsland;
use Javaabu\Locations\Policies\IslandPolicy;
use Javaabu\Locations\Seeding\LocationsImporter;
use Javaabu\Locations\Support\Translations;
use Javaabu\Locations\Tests\TestCase;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Testing\InteractsWithLanguages;
use Javaabu\Translatable\Translatable;
use Javaabu\Translatable\TranslatableServiceProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * With javaabu/translatable installed and enabled. The rest of the suite runs without it.
 */
class TranslatableTest extends TestCase
{
    use InteractsWithLanguages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! class_exists(Translatable::class)) {
            $this->markTestSkipped('javaabu/translatable is not installed.');
        }

        parent::setUp();

        $this->setUpLanguages(['en', 'dv']);
    }

    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), TranslatableServiceProvider::class];
    }

    protected function defineRoutes($router): void
    {
        Route::middleware('web')->group(function () {
            Route::locations();
            Route::locationOptions();
        });
    }

    #[Test]
    public function it_uses_the_translatable_models(): void
    {
        $this->assertTrue(Translations::enabled());
        $this->assertEquals(TranslatableIsland::class, Locations::islandModel());
        $this->assertTrue(Translations::supports(Locations::islandModel(), 'name'));
        $this->assertFalse(Translations::supports(Locations::islandModel(), 'code'));
        $this->assertEquals(Island::class, (new TranslatableIsland)->getMorphClass());
    }

    #[Test]
    public function it_still_uses_the_apps_model(): void
    {
        config(['locations.models.island' => Island::class]);

        $this->assertEquals(Island::class, Locations::islandModel());
    }

    #[Test]
    public function it_uses_the_plain_models_when_translatable_is_off(): void
    {
        $this->withoutTranslatable(function () {
            $this->assertFalse(Translations::enabled());
            $this->assertEquals(Island::class, Locations::islandModel());
        });
    }

    #[Test]
    public function it_seeds_the_dhivehi_names_as_translations(): void
    {
        $importer = new LocationsImporter;
        $importer->atolls();
        $importer->islands();

        $male = Locations::islandModel()::findByCode('LD0442');

        $this->assertInstanceOf(TranslatableIsland::class, $male);
        $this->assertEquals('en', $male->lang);
        $this->assertEquals('Malé', $male->translate('name', 'en'));
        $this->assertEquals('މާލެ', $male->translate('name', 'dv'));
        $this->assertEquals('މާލެ', $male->dhivehi_name);

        app()->setLocale('dv');
        Languages::setCurrentLocale('dv');

        $this->assertEquals('މާލެ', $male->name);
        $this->assertEquals('މާލެ', $male->localizedName());
        $this->assertEquals('Malé', $male->localizedName('en'));
    }

    #[Test]
    public function it_prefers_the_translation_over_the_dhivehi_name_column(): void
    {
        $island = TranslatableIsland::query()->create([
            'atoll_id' => Atoll::factory()->create()->id,
            'code' => 'LD9999',
            'name' => 'Huraa',
            'dhivehi_name' => 'ހުރާ',
        ]);

        $island->setTranslationAttributeValue('name', 'dv', 'ހުރާ (ކ)');
        $island->save();

        $this->assertEquals('ހުރާ (ކ)', $island->refresh()->localizedName('dv'));
    }

    #[Test]
    public function it_applies_the_policies_to_the_translatable_models(): void
    {
        $this->assertInstanceOf(IslandPolicy::class, Gate::getPolicyFor(TranslatableIsland::class));
    }

    #[Test]
    public function it_shows_an_input_per_language_on_the_form(): void
    {
        Atoll::factory()->haaAlifu()->create();

        $this->actingAs($this->locationsAdmin())
            ->get('/locations/islands/create')
            ->assertOk()
            ->assertSee('name="name_en"', false)
            ->assertSee('name="name_dv"', false)
            ->assertDontSee('name="dhivehi_name"', false);
    }

    #[Test]
    public function it_saves_every_language_and_keeps_the_dhivehi_name_in_step(): void
    {
        $atoll = Atoll::factory()->haaAlifu()->create();

        $this->actingAs($this->locationsAdmin())
            ->post('/locations/islands', [
                'atoll_id' => $atoll->id,
                'code' => 'LD9999',
                'name_en' => 'Huraa',
                'name_dv' => 'ހުރާ',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $island = Locations::islandModel()::findByCode('LD9999');

        $this->assertEquals('Huraa', $island->translate('name', 'en'));
        $this->assertEquals('ހުރާ', $island->translate('name', 'dv'));
        $this->assertEquals('ހުރާ', $island->dhivehi_name);
    }

    #[Test]
    public function it_shows_the_translation_of_other_languages(): void
    {
        $this->setUpLanguages(['en', 'dv', 'ar']);

        $island = TranslatableIsland::query()->create([
            'atoll_id' => Atoll::factory()->create()->id,
            'code' => 'LD9999',
            'name' => 'Huraa',
        ]);

        $island->setTranslationAttributeValue('name', 'ar', 'هورا');
        $island->save();

        $this->assertEquals('هورا', $island->refresh()->localizedName('ar'));
        $this->assertEquals('Huraa', $island->localizedName('dv'));
        $this->assertEquals('Huraa', $island->localizedName('fr'));
    }
}
