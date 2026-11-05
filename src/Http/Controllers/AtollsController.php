<?php

namespace Javaabu\Locations\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Locations\Forms\AtollForm;
use Javaabu\Locations\Models\Atoll;

class AtollsController extends LocationsController
{
    protected string $type = 'atoll';

    protected function formClass(): string
    {
        return AtollForm::class;
    }

    protected function with(): array
    {
        return ['capital'];
    }

    protected function columns(): array
    {
        return [
            'code' => __('locations::strings.fields.code'),
            'name' => __('locations::strings.fields.name'),
            'dhivehi_name' => __('locations::strings.fields.dhivehi_name'),
            'official_name' => __('locations::strings.fields.official_name'),
            'capital' => __('locations::strings.fields.capital'),
        ];
    }

    /**
     * @param  Atoll  $location
     */
    protected function cells(Model $location): array
    {
        return [
            'code' => $location->code,
            'name' => $location->name,
            'dhivehi_name' => $location->dhivehi_name,
            'official_name' => $location->localizedOfficialName(),
            'capital' => $location->capital?->localizedName(),
        ];
    }

    /**
     * @param  Atoll  $location
     */
    protected function detailValues(Model $location): array
    {
        return [
            'name' => $location->name,
            'dhivehi_name' => $location->dhivehi_name,
            'official_name' => $location->official_name,
            'official_dhivehi_name' => $location->official_dhivehi_name,
            'code' => $location->code,
            'dhivehi_code' => $location->dhivehi_code,
            'letter_code' => $location->letter_code,
            'iso_code' => $location->iso_code,
            'capital' => $location->capital?->localizedName(),
            'is_city' => $location->is_city ? __('locations::strings.yes') : __('locations::strings.no'),
        ];
    }

    protected function children(): array
    {
        return ['islands' => 'islands'];
    }
}
