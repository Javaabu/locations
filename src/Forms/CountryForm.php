<?php

namespace Javaabu\Locations\Forms;

use Javaabu\Stencil\Components\Section;

class CountryForm extends LocationForm
{
    protected function definition(): array
    {
        return [
            Section::make(__('locations::strings.sections.details'))->columns(2)->schema([
                ...$this->nameFields(),
                $this->string('official_name'),
                $this->string('code', 2)
                    ->required()
                    ->minLength(2)
                    ->rules(['alpha'])
                    ->unique()
                    ->help(__('locations::strings.help.country_code')),
                $this->string('alpha3_code', 3)->minLength(3)->rules(['alpha'])->unique(),
                $this->string('numeric_code', 3)->minLength(3)->rules(['digits:3']),
                $this->string('calling_code', 8)->rules(['digits_between:1,8'])->help(__('locations::strings.help.calling_code')),
            ]),
        ];
    }
}
