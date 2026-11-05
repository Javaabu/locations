<?php

namespace Javaabu\Locations\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Javaabu\Locations\Forms\CityForm;
use Javaabu\Locations\Models\City;
use Javaabu\Locations\Stencil\CountrySelect;
use Javaabu\Locations\Stencil\StateSelect;

class CitiesController extends LocationsController
{
    protected string $type = 'city';

    protected function formClass(): string
    {
        return CityForm::class;
    }

    protected function with(): array
    {
        return ['country', 'state'];
    }

    protected function filterFields(Request $request): array
    {
        return [
            CountrySelect::make('country'),
            StateSelect::make('state')->dependsOn('country'),
        ];
    }

    protected function columns(): array
    {
        return [
            'name' => __('locations::strings.fields.name'),
            'dhivehi_name' => __('locations::strings.fields.dhivehi_name'),
            'state' => __('locations::strings.types.state.singular'),
            'country' => __('locations::strings.types.country.singular'),
        ];
    }

    /**
     * @param  City  $location
     */
    protected function cells(Model $location): array
    {
        return [
            'name' => $location->name,
            'dhivehi_name' => $location->dhivehi_name,
            'state' => $location->state?->localizedName(),
            'country' => $location->country?->localizedName(),
        ];
    }

    /**
     * @param  City  $location
     */
    protected function detailValues(Model $location): array
    {
        return [
            ...$this->cells($location),
            'code' => $location->code,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
        ];
    }
}
