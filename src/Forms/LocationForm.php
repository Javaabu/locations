<?php

namespace Javaabu\Locations\Forms;

use Javaabu\Locations\Support\Translations;
use Javaabu\Stencil\Fields\Field;
use Javaabu\Stencil\Fields\TextInput;
use Javaabu\Stencil\Form;

/**
 * The create and edit forms of the admin pages. Extend one and point the controller at
 * yours (`formClass()`) to change its fields.
 */
abstract class LocationForm extends Form
{
    /**
     * The English and Dhivehi name fields. With javaabu/translatable, the name has an input per
     * language instead, and the Dhivehi name follows its Dhivehi translation.
     *
     * @return array<int, Field>
     */
    protected function nameFields(bool $required = true): array
    {
        $name = TextInput::make('name')
            ->label(__('locations::strings.fields.name'))
            ->required($required)
            ->maxLength(255);

        if (Translations::supports($this->getModelInstance(), 'name')) {
            return [$name->translatable()];
        }

        return [
            $name,
            TextInput::make('dhivehi_name')
                ->label(__('locations::strings.fields.dhivehi_name'))
                ->maxLength(255)
                ->thaana(),
        ];
    }

    protected function string(string $name, int $max_length = 255): TextInput
    {
        return TextInput::make($name)
            ->label(__("locations::strings.fields.{$name}"))
            ->maxLength($max_length);
    }

    protected function coordinate(string $name, int $max): TextInput
    {
        return TextInput::make($name)
            ->label(__("locations::strings.fields.{$name}"))
            ->numeric()
            ->minValue(-$max)
            ->maxValue($max)
            ->step('any');
    }
}
