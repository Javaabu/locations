<?php

namespace Javaabu\Locations\Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ManifestCheckTest extends TestCase
{
    protected function tearDown(): void
    {
        File::delete(base_path('package.json'));

        parent::tearDown();
    }

    /**
     * The packages manifest:check asks the app for on behalf of locations, by name
     *
     * @param  array<string, string>  $dependencies  the app's package.json
     * @return array<string, array<string, mixed>>
     */
    protected function neededByLocations(array $dependencies): array
    {
        File::put(base_path('package.json'), json_encode(['devDependencies' => (object) $dependencies]));

        Artisan::call('manifest:check', ['--json' => true]);

        return collect(json_decode(Artisan::output(), true))
            ->filter(fn (array $row) => in_array('locations', $row['needed_by']))
            ->keyBy('name')
            ->all();
    }

    #[Test]
    public function it_does_not_ask_blade_apps_for_react_packages(): void
    {
        $this->assertSame([], $this->neededByLocations([]));
    }

    #[Test]
    public function it_asks_inertia_apps_for_what_the_pages_and_the_selects_need(): void
    {
        $rows = $this->neededByLocations(['@inertiajs/react' => '^3.0']);

        foreach (['@javaabu/forms', 'react', 'react-dom', '@inertiajs/react'] as $name) {
            $this->assertArrayHasKey($name, $rows);
            $this->assertContains('pages', $rows[$name]['scopes']);
        }
    }
}
