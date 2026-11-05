<?php

namespace Javaabu\Locations\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Locations\Forms\CountryForm;
use Javaabu\Locations\Models\Country;

class CountriesController extends LocationsController
{
    protected string $type = 'country';

    protected function formClass(): string
    {
        return CountryForm::class;
    }

    protected function columns(): array
    {
        return [
            'code' => __('locations::strings.fields.code'),
            'name' => __('locations::strings.fields.name'),
            'dhivehi_name' => __('locations::strings.fields.dhivehi_name'),
            'calling_code' => __('locations::strings.fields.calling_code'),
        ];
    }

    /**
     * @param  Country  $location
     */
    protected function cells(Model $location): array
    {
        return [
            'code' => $location->code,
            'name' => trim("{$location->flag} {$location->name}"),
            'dhivehi_name' => $location->dhivehi_name,
            'calling_code' => $location->formattedCallingCode(),
        ];
    }

    /**
     * @param  Country  $location
     */
    protected function detailValues(Model $location): array
    {
        return [
            'name' => $location->name,
            'dhivehi_name' => $location->dhivehi_name,
            'official_name' => $location->official_name,
            'code' => $location->code,
            'alpha3_code' => $location->alpha3_code,
            'numeric_code' => $location->numeric_code,
            'calling_code' => $location->formattedCallingCode(),
        ];
    }

    protected function children(): array
    {
        return ['states' => 'states', 'cities' => 'cities'];
    }
}
