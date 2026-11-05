<?php

namespace Javaabu\Locations\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Javaabu\Locations\Forms\StateForm;
use Javaabu\Locations\Models\State;
use Javaabu\Locations\Stencil\CountrySelect;

class StatesController extends LocationsController
{
    protected string $type = 'state';

    protected function formClass(): string
    {
        return StateForm::class;
    }

    protected function with(): array
    {
        return ['country'];
    }

    protected function filterFields(Request $request): array
    {
        return [CountrySelect::make('country')];
    }

    protected function columns(): array
    {
        return [
            'code' => __('locations::strings.fields.code'),
            'name' => __('locations::strings.fields.name'),
            'type' => __('locations::strings.fields.type'),
            'country' => __('locations::strings.types.country.singular'),
        ];
    }

    /**
     * @param  State  $location
     */
    protected function cells(Model $location): array
    {
        return [
            'code' => $location->code,
            'name' => $location->localizedName(),
            'type' => $location->type,
            'country' => $location->country?->localizedName(),
        ];
    }

    /**
     * @param  State  $location
     */
    protected function detailValues(Model $location): array
    {
        return [
            'name' => $location->name,
            'dhivehi_name' => $location->dhivehi_name,
            'code' => $location->code,
            'type' => $location->type,
            'country' => $location->country?->localizedName(),
        ];
    }

    protected function children(): array
    {
        return ['cities' => 'cities'];
    }
}
