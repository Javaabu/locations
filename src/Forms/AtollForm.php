<?php

namespace Javaabu\Locations\Forms;

use Javaabu\Stencil\Components\Section;
use Javaabu\Stencil\Fields\Checkbox;
use Javaabu\Stencil\Fields\TextInput;

class AtollForm extends LocationForm
{
    protected function definition(): array
    {
        return [
            Section::make(__('locations::strings.sections.details'))->columns(2)->schema([
                ...$this->nameFields(),
                $this->string('official_name'),
                $this->string('official_dhivehi_name')->thaana(),
                $this->string('code', 8)->required()->unique()->help(__('locations::strings.help.atoll_code')),
                $this->string('dhivehi_code', 8)->thaana(),
                $this->string('letter_code', 1)->rules(['alpha'])->unique(),
                $this->string('iso_code', 6)->unique()->help(__('locations::strings.help.iso_code')),
                TextInput::make('sort_order')
                    ->label(__('locations::strings.fields.sort_order'))
                    ->integer()
                    ->minValue(0)
                    ->maxValue(65535),
                Checkbox::make('is_city')->label(__('locations::strings.fields.is_city')),
            ]),
        ];
    }
}
