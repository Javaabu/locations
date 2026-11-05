<?php

namespace Javaabu\Locations\Tests\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Inertia\Testing\AssertableInertia;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * The Inertia pages' props. Also writes the JSON the React page tests render: run with
 * LOCATIONS_UPDATE_FIXTURES=1 to rewrite tests/js/fixtures.
 */
class InertiaPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! class_exists(Inertia::class)) {
            $this->markTestSkipped('inertiajs/inertia-laravel is not installed.');
        }

        parent::setUp();

        Carbon::setTestNow('2026-11-05 10:00:00');

        config(['locations.views' => 'inertia']);
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        // The pages are package files (resources/js/Pages), resolved by javaabu/manifest
        $app['config']->set('inertia.testing.ensure_pages_exist', false);
    }

    protected function getPackageProviders($app): array
    {
        return [...parent::getPackageProviders($app), InertiaServiceProvider::class];
    }

    protected function defineRoutes($router): void
    {
        Route::middleware('web')->group(function () {
            Route::locations();
            Route::locationOptions();
        });
    }

    protected function seedIslands(): Island
    {
        $atoll = Atoll::factory()->haaAlifu()->create();

        Island::factory()->for($atoll)->create(['code' => 'LD0246', 'name' => 'Hoarafushi', 'dhivehi_name' => 'ހޯރަފުށި', 'latitude' => 6.98, 'longitude' => 72.89, 'area' => 69.02]);

        return Island::factory()->for($atoll)->capital()->create(['code' => 'LD0568', 'name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ', 'latitude' => 6.88, 'longitude' => 73.11, 'area' => 96.54]);
    }

    /**
     * @param  array<string, mixed>  $props
     */
    protected function fixture(string $file, array $props): void
    {
        $path = __DIR__ . "/../js/fixtures/{$file}";
        $json = json_encode($props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";

        if (getenv('LOCATIONS_UPDATE_FIXTURES') || ! is_file($path)) {
            file_put_contents($path, $json);
        }

        $this->assertEquals(
            $this->shape(json_decode(file_get_contents($path), true)),
            $this->shape($props),
            "tests/js/fixtures/{$file} is out of date: run LOCATIONS_UPDATE_FIXTURES=1 vendor/bin/phpunit --filter InertiaPagesTest",
        );
    }

    /**
     * The keys of a JSON value, recursively: what the React pages rely on
     */
    protected function shape(mixed $value): mixed
    {
        if (! is_array($value)) {
            return get_debug_type($value) === 'null' ? 'null' : 'scalar';
        }

        return array_map(fn ($item) => $this->shape($item), $value);
    }

    #[Test]
    public function it_renders_the_index_page(): void
    {
        $this->seedIslands();

        $this->actingAs($this->locationsAdmin())
            ->get('/locations/islands?atoll=HA')
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->component('locations::Resources/Index')
                    ->where('type', 'island')
                    ->where('resource', 'islands')
                    ->where('labels.plural', 'Islands')
                    ->where('filtered', true)
                    ->has('rows', 2)
                    ->where('rows.0.cells.name', 'Dhidhdhoo')
                    ->where('rows.0.cells.atoll', 'HA. Haa Alifu')
                    ->has('columns', 5)
                    ->where('filters.stencil', 1)
                    ->where('strings.new', 'New Island')
                    ->where('locations.total', 2);

                $this->fixture('index.json', $page->toArray()['props']);
            });
    }

    #[Test]
    public function it_renders_the_form_page(): void
    {
        $island = $this->seedIslands();

        $this->actingAs($this->locationsAdmin())
            ->get("/locations/islands/{$island->id}/edit")
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) use ($island) {
                $page->component('locations::Resources/Form')
                    ->where('location.id', $island->id)
                    ->where('form.method', 'PATCH')
                    ->where('form.state.name', 'Dhidhdhoo')
                    ->where('can.delete', true);

                $this->fixture('form.json', $page->toArray()['props']);
            });
    }

    #[Test]
    public function it_renders_the_create_page(): void
    {
        Atoll::factory()->haaAlifu()->create();

        $this->actingAs($this->locationsAdmin())
            ->get('/locations/islands/create')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('locations::Resources/Form')
                ->where('location', null)
                ->where('form.method', 'POST')
                ->where('title', 'New Island'));
    }

    #[Test]
    public function it_renders_the_show_page(): void
    {
        $island = $this->seedIslands();

        $this->actingAs($this->locationsAdmin())
            ->get("/locations/atolls/{$island->atoll_id}")
            ->assertOk()
            ->assertInertia(function (AssertableInertia $page) {
                $page->component('locations::Resources/Show')
                    ->where('location.code', 'HA')
                    ->where('title', 'HA. Haa Alifu')
                    ->where('related.0.label', 'Islands (2)')
                    ->has('details');

                $this->fixture('show.json', $page->toArray()['props']);
            });
    }

    #[Test]
    public function it_renders_in_dhivehi(): void
    {
        $this->seedIslands();

        app()->setLocale('dv');

        $this->actingAs($this->locationsAdmin())
            ->get('/locations/islands')
            ->assertInertia(function (AssertableInertia $page) {
                $page->where('labels.plural', 'ރަށްތައް')
                    ->where('rows.0.cells.atoll', 'ހއ. ހާ އަލިފު')
                    ->where('strings.new', 'އައު ރަށް');

                $this->fixture('index.dv.json', $page->toArray()['props']);
            });
    }
}
