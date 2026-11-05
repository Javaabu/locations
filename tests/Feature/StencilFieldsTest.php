<?php

namespace Javaabu\Locations\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Validation\ValidationException;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Country;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Stencil\AtollSelect;
use Javaabu\Locations\Stencil\CountrySelect;
use Javaabu\Locations\Stencil\IslandSelect;
use Javaabu\Locations\Tests\TestCase;
use Javaabu\Stencil\Form;
use PHPUnit\Framework\Attributes\Test;

class StencilFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function defineRoutes($router): void
    {
        Route::locationOptions();
    }

    protected function form(array $state = []): Form
    {
        return Form::make()->model($state)->action('/addresses')->schema([
            CountrySelect::make('country_id'),
            AtollSelect::make('atoll_id')->dependsOn('country_id'),
            IslandSelect::make('island_id')->dependsOn('atoll_id')->required(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function field(array $json, string $name): array
    {
        foreach ($json['components'] as $component) {
            if (($component['name'] ?? null) === $name) {
                return $component;
            }
        }

        $this->fail("No [{$name}] field in the form.");
    }

    #[Test]
    public function it_serializes_the_cascade_for_react(): void
    {
        $atoll = Atoll::factory()->haaAlifu()->create();
        Island::factory()->for($atoll)->create(['name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);
        $maldives = Country::factory()->maldives()->create();

        $json = $this->form(['country_id' => $maldives->id, 'atoll_id' => $atoll->id])->render('json');

        $country = $this->field($json, 'country_id');
        $atoll_field = $this->field($json, 'atoll_id');
        $island = $this->field($json, 'island_id');

        $this->assertEquals('Select2', $country['component']);
        $this->assertEquals('#atoll_id', $country['props']['child']);
        $this->assertEquals('#island_id', $atoll_field['props']['child']);
        $this->assertEquals('http://localhost/location-options/atolls', $atoll_field['props']['ajaxUrl']);
        $this->assertEquals('country', $atoll_field['props']['filterField']);
        $this->assertEquals('#country_id', $atoll_field['props']['parent']);
        $this->assertEquals('atoll', $island['props']['filterField']);
        $this->assertEquals([['value' => (string) Island::query()->value('id'), 'label' => 'Dhidhdhoo']], $island['props']['options']);
        $this->assertEquals('Island', $island['props']['label']);
    }

    #[Test]
    public function it_renders_the_cascade_with_blade(): void
    {
        view()->share('errors', new ViewErrorBag);

        $atoll = Atoll::factory()->haaAlifu()->create();
        Island::factory()->for($atoll)->create(['name' => 'Dhidhdhoo']);

        $html = (string) $this->form(['atoll_id' => $atoll->id])->render('blade');

        $this->assertStringContainsString('data-select-child="#island_id"', $html);
        $this->assertStringContainsString('data-select-ajax-url="http://localhost/location-options/islands"', $html);
        $this->assertStringContainsString('Dhidhdhoo', $html);
    }

    #[Test]
    public function it_lists_nothing_until_the_parent_has_a_value(): void
    {
        Island::factory()->for(Atoll::factory())->create();

        $json = $this->form()->render('json');

        $this->assertEmpty($this->field($json, 'island_id')['props']['options'] ?? []);
    }

    #[Test]
    public function it_validates_that_the_location_exists(): void
    {
        $this->expectException(ValidationException::class);

        $this->form()->validate(['island_id' => 999]);
    }

    #[Test]
    public function it_returns_valid_locations(): void
    {
        $island = Island::factory()->for(Atoll::factory())->create();

        $data = $this->form()->validate(['atoll_id' => $island->atoll_id, 'island_id' => $island->id]);

        $this->assertEquals($island->id, $data['island_id']);
    }

    #[Test]
    public function it_narrows_the_options_with_a_query(): void
    {
        $atoll = Atoll::factory()->create();
        Island::factory()->for($atoll)->create(['name' => 'Inhabited']);
        Island::factory()->for($atoll)->category(IslandCategories::TOURISM)->create(['name' => 'Resort']);

        $field = IslandSelect::make('island_id')->query(fn ($query) => $query->inhabited());
        Form::make()->schema([$field]);

        $this->assertEquals(['Inhabited'], array_values($field->getOptions()));
    }

    #[Test]
    public function it_narrows_the_options_and_the_reloads_with_filters(): void
    {
        $atoll = Atoll::factory()->create();
        Island::factory()->for($atoll)->create(['name' => 'Inhabited']);
        Island::factory()->for($atoll)->category(IslandCategories::TOURISM)->create(['name' => 'Resort']);

        $json = Form::make()->model(['atoll_id' => $atoll->id])->schema([
            AtollSelect::make('atoll_id'),
            IslandSelect::make('island_id')->dependsOn('atoll_id')->filters(['category' => 'tourism']),
        ])->render('json');

        $island = $this->field($json, 'island_id');

        $this->assertEquals(['Resort'], array_column($island['props']['options'], 'label'));
        $this->assertEquals('http://localhost/location-options/islands?filter%5Bcategory%5D=tourism', $island['props']['ajaxUrl']);
    }
}
