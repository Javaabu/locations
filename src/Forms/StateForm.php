<?php

namespace Javaabu\Locations\Forms;

use Javaabu\Locations\Stencil\CountrySelect;
use Javaabu\Stencil\Components\Section;

class StateForm extends LocationForm
{
    protected function definition(): array
    {
        return [
            Section::make(__('locations::strings.sections.details'))->columns(2)->schema([
                CountrySelect::make('country_id')->required(),
                ...$this->nameFields(),
                $this->string('code', 10)->unique()->help(__('locations::strings.help.state_code')),
                $this->string('type'),
            ]),
        ];
    }
}
