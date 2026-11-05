<?php

namespace Javaabu\Locations\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Forms\IslandForm;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Stencil\AtollSelect;
use Javaabu\Stencil\Fields\Select;

class IslandsController extends LocationsController
{
    protected string $type = 'island';

    protected function formClass(): string
    {
        return IslandForm::class;
    }

    protected function with(): array
    {
        return ['atoll'];
    }

    protected function filterFields(Request $request): array
    {
        return [
            AtollSelect::make('atoll'),
            Select::make('category')
                ->label(__('locations::strings.fields.category'))
                ->options(IslandCategories::class),
        ];
    }

    protected function columns(): array
    {
        return [
            'code' => __('locations::strings.fields.code'),
            'name' => __('locations::strings.fields.name'),
            'dhivehi_name' => __('locations::strings.fields.dhivehi_name'),
            'atoll' => __('locations::strings.types.atoll.singular'),
            'category' => __('locations::strings.fields.category'),
        ];
    }

    /**
     * @param  Island  $location
     */
    protected function cells(Model $location): array
    {
        return [
            'code' => $location->code,
            'name' => $location->name,
            'dhivehi_name' => $location->dhivehi_name,
            'atoll' => $location->atoll?->optionLabel(),
            'category' => $location->category?->label(),
        ];
    }

    /**
     * @param  Island  $location
     */
    protected function detailValues(Model $location): array
    {
        return [
            ...$this->cells($location),
            'is_capital' => $location->is_capital ? __('locations::strings.yes') : __('locations::strings.no'),
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'area' => $location->area === null ? null : __('locations::strings.units.area', ['area' => $location->area]),
        ];
    }

    protected function title(Model $location): string
    {
        /** @var Island $location */
        return $location->formattedName();
    }
}
