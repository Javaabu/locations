<?php

namespace Javaabu\Locations\Forms;

use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Stencil\AtollSelect;
use Javaabu\Stencil\Components\Section;
use Javaabu\Stencil\Fields\Checkbox;
use Javaabu\Stencil\Fields\Select;
use Javaabu\Stencil\Fields\TextInput;

class IslandForm extends LocationForm
{
    protected function definition(): array
    {
        return [
            Section::make(__('locations::strings.sections.details'))->columns(2)->schema([
                AtollSelect::make('atoll_id')->required(),
                $this->string('code', 16)->required()->unique()->help(__('locations::strings.help.island_code')),
                ...$this->nameFields(required: false),
                Select::make('category')
                    ->label(__('locations::strings.fields.category'))
                    ->options(IslandCategories::class),
                Checkbox::make('is_capital')->label(__('locations::strings.fields.is_capital')),
            ]),
            Section::make(__('locations::strings.sections.geography'))->columns(3)->schema([
                $this->coordinate('latitude', 90),
                $this->coordinate('longitude', 180),
                TextInput::make('area')
                    ->label(__('locations::strings.fields.area'))
                    ->numeric()
                    ->minValue(0)
                    ->step('any')
                    ->suffix(__('locations::strings.units.hectares')),
            ]),
        ];
    }
}
