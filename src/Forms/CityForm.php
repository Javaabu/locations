<?php

namespace Javaabu\Locations\Forms;

use Javaabu\Locations\Stencil\CountrySelect;
use Javaabu\Locations\Stencil\StateSelect;
use Javaabu\Stencil\Components\Section;

class CityForm extends LocationForm
{
    protected function definition(): array
    {
        return [
            Section::make(__('locations::strings.sections.details'))->columns(2)->schema([
                CountrySelect::make('country_id')->required(),
                StateSelect::make('state_id')->dependsOn('country_id'),
                ...$this->nameFields(),
                $this->string('code'),
                $this->coordinate('latitude', 90),
                $this->coordinate('longitude', 180),
            ]),
        ];
    }
}
