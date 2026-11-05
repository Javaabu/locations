<?php

namespace Javaabu\Locations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Country;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Tests\TestCase;
use Javaabu\Locations\Views\Components\LocationSelect;
use PHPUnit\Framework\Attributes\Test;

class BladeComponentsTest extends TestCase
{
    use RefreshDatabase;

    protected function defineRoutes($router): void
    {
        Route::locationOptions();
    }

    protected function setUp(): void
    {
        parent::setUp();

        view()->share('errors', new ViewErrorBag);
    }

    protected function renderBlade(string $template, array $data = []): string
    {
        return preg_replace('/\s+/', ' ', Blade::render($template, $data));
    }

    protected function seedAtolls(): Atoll
    {
        $atoll = Atoll::factory()->haaAlifu()->create();

        Island::factory()->for($atoll)->create(['name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);
        Island::factory()->for($atoll)->category(IslandCategories::TOURISM)->create(['name' => 'Manafaru', 'dhivehi_name' => 'މަނަފަރު']);
        Island::factory()->for(Atoll::factory()->male())->create(['name' => 'Malé', 'dhivehi_name' => 'މާލެ']);

        return $atoll;
    }

    #[Test]
    public function it_renders_a_select_of_every_location_of_a_type(): void
    {
        Country::factory()->maldives()->create();

        $html = $this->renderBlade('<x-locations::country-select />');

        $this->assertStringContainsString('name="country_id"', $html);
        $this->assertStringContainsString('data-location-select="country"', $html);
        $this->assertStringContainsString('Maldives', $html);
        $this->assertStringContainsString('Country', $html);
    }

    #[Test]
    public function it_labels_options_in_dhivehi(): void
    {
        $this->seedAtolls();

        app()->setLocale('dv');

        $html = $this->renderBlade('<x-locations::atoll-select />');

        $this->assertStringContainsString('ހއ. ހާ އަލިފު', $html);
        $this->assertStringContainsString('އަތޮޅު', $html);
    }

    #[Test]
    public function it_cascades_from_a_parent(): void
    {
        $atoll = $this->seedAtolls();

        $html = $this->renderBlade(<<<'BLADE'
            <x-forms::form :model="$address">
                <x-locations::atoll-select name="atoll_id" child="island_id" />
                <x-locations::island-select name="island_id" parent="atoll_id" />
            </x-forms::form>
            BLADE, ['address' => ['atoll_id' => $atoll->id, 'island_id' => null]]);

        $this->assertStringContainsString('data-select-child="#island_id"', $html);
        $this->assertStringContainsString('data-select-ajax-url="http://localhost/location-options/islands"', $html);
        $this->assertStringContainsString('data-filter-field="atoll"', $html);
        $this->assertStringContainsString('Dhidhdhoo', $html);
        $this->assertStringContainsString('Manafaru', $html);
        $this->assertStringNotContainsString('>Malé<', $html);
        $this->assertStringContainsString('>Malé City<', $html);
    }

    #[Test]
    public function it_has_no_child_options_without_a_parent_value(): void
    {
        $this->seedAtolls();

        $html = $this->renderBlade('<x-locations::island-select parent="atoll_id" />');

        $this->assertStringNotContainsString('Dhidhdhoo', $html);
    }

    #[Test]
    public function it_takes_the_parent_value_and_filters(): void
    {
        $atoll = $this->seedAtolls();

        $html = $this->renderBlade('<x-locations::island-select parent="#atoll_id" :parent-value="$atoll" :filters="[\'category\' => \'tourism\']" />', [
            'atoll' => $atoll->id,
        ]);

        $this->assertStringContainsString('Manafaru', $html);
        $this->assertStringNotContainsString('Dhidhdhoo', $html);
        $this->assertStringContainsString('filter%5Bcategory%5D=tourism', $html);
    }

    #[Test]
    public function it_passes_attributes_to_the_select(): void
    {
        $this->seedAtolls();

        $html = $this->renderBlade('<x-locations::atoll-select name="home_atoll" label="އަތޮޅު" required :options="[1 => \'Custom\']" />');

        $this->assertStringContainsString('name="home_atoll"', $html);
        $this->assertStringContainsString('required', $html);
        $this->assertStringContainsString('Custom', $html);
        $this->assertStringNotContainsString('Haa Alifu', $html);
    }

    #[Test]
    public function it_turns_field_names_into_selectors(): void
    {
        $this->assertEquals('#island_id', LocationSelect::selector('island_id'));
        $this->assertEquals('#address_island', LocationSelect::selector('address[island]'));
        $this->assertEquals('.island', LocationSelect::selector('.island'));
    }

    #[Test]
    public function it_cascades_from_country_to_atoll_to_island(): void
    {
        $atoll = $this->seedAtolls();
        $maldives = Country::factory()->maldives()->create();
        $sri_lanka = Country::factory()->create(['code' => 'LK', 'name' => 'Sri Lanka']);

        $template = <<<'BLADE'
            <x-forms::form :model="$address">
                <x-locations::country-select child="atoll_id" />
                <x-locations::atoll-select parent="country_id" child="island_id" fallback="#city" />
                <x-locations::island-select parent="atoll_id" />
            </x-forms::form>
            BLADE;

        $html = $this->renderBlade($template, ['address' => ['country_id' => $maldives->id, 'atoll_id' => $atoll->id]]);

        $this->assertStringContainsString('data-select-ajax-url="http://localhost/location-options/atolls"', $html);
        $this->assertStringContainsString('data-filter-field="country"', $html);
        $this->assertStringContainsString('HA. Haa Alifu', $html);
        $this->assertStringContainsString('Dhidhdhoo', $html);

        $html = $this->renderBlade($template, ['address' => ['country_id' => $sri_lanka->id]]);

        $this->assertStringNotContainsString('HA. Haa Alifu', $html);
        $this->assertStringNotContainsString('Dhidhdhoo', $html);
    }
}
