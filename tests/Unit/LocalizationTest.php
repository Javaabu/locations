<?php

namespace Javaabu\Locations\Tests\Unit;

use Illuminate\Support\Arr;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class LocalizationTest extends TestCase
{
    #[Test]
    public function it_has_a_dhivehi_line_for_every_english_line(): void
    {
        $en = Arr::dot(require __DIR__ . '/../../lang/en/strings.php');
        $dv = Arr::dot(require __DIR__ . '/../../lang/dv/strings.php');

        $this->assertEquals(array_keys($en), array_keys($dv));

        foreach ($dv as $key => $line) {
            $this->assertMatchesRegularExpression('/\p{Thaana}/u', $line, "lang/dv/strings.php [{$key}] isn't in Dhivehi.");

            preg_match_all('/:[a-z_]+/', $en[$key], $placeholders);

            foreach ($placeholders[0] as $placeholder) {
                $this->assertStringContainsString($placeholder, $line, "lang/dv/strings.php [{$key}] is missing {$placeholder}.");
            }
        }
    }

    #[Test]
    public function it_translates_the_permission_descriptions(): void
    {
        $dv = json_decode(file_get_contents(__DIR__ . '/../../lang/dv.json'), true);

        foreach (['countries', 'states', 'cities', 'atolls', 'islands'] as $resource) {
            $this->assertArrayHasKey(ucfirst($resource), $dv);

            foreach (['View', 'Edit', 'Delete'] as $action) {
                $this->assertArrayHasKey("{$action} {$resource}", $dv);
            }
        }

        $this->app->setLocale('dv');

        $this->assertEquals('ރަށްތައް ބެލުން', __('View islands'));
    }

    #[Test]
    public function it_translates_island_categories(): void
    {
        $this->app->setLocale('dv');

        $this->assertEquals('ފަތުރުވެރިކަމުގެ ރަށް', IslandCategories::TOURISM->label());
        $this->assertEquals('އައު ރަށް', __('locations::strings.actions.new', ['type' => __('locations::strings.types.island.singular')]));
    }

    #[Test]
    public function it_only_uses_logical_directions_in_views_and_components(): void
    {
        $files = [];

        foreach (['resources/views', 'resources/js'] as $directory) {
            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . "/../../{$directory}")) as $file) {
                if (preg_match('/\.(blade\.php|tsx|ts)$/', $file->getFilename())) {
                    $files[] = $file->getPathname();
                }
            }
        }

        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $this->assertDoesNotMatchRegularExpression(
                '/\b(ml|mr|pl|pr|left|right|text-left|text-right|border-l|border-r|rounded-l|rounded-r)-[0-9a-z]/',
                file_get_contents($file),
                basename($file) . ' uses a physical direction; use ms-, me-, ps-, pe-, start-, end-, text-start or text-end.',
            );
        }
    }
}
