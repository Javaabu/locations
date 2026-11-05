<?php

namespace Javaabu\Locations\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Country;

/**
 * The filters the location selects and the options endpoint share
 *
 * | Type    | Filters                                                        |
 * | ------- | -------------------------------------------------------------- |
 * | all     | `search`                                                       |
 * | state   | `country` (id or alpha-2 code)                                 |
 * | city    | `country`, `state`                                             |
 * | atoll   | `country`: only the Maldives has atolls                        |
 * | island  | `atoll` (id or code), `category`, `inhabited`, `named`         |
 */
class LocationOptions
{
    /** The filters each type accepts, besides `search` */
    public const array FILTERS = [
        'country' => [],
        'state' => ['country'],
        'city' => ['country', 'state'],
        'atoll' => ['country'],
        'island' => ['atoll', 'category', 'inhabited', 'named'],
    ];

    /**
     * Apply filters (`['atoll' => 'HA', 'search' => 'dhi']`) to a query of a location type
     *
     * @param  array<string, mixed>  $filters
     */
    public static function filter(string $type, Builder $query, array $filters): Builder
    {
        $query->search(is_string($filters['search'] ?? null) ? $filters['search'] : null);

        foreach (self::FILTERS[$type] ?? [] as $filter) {
            $value = $filters[$filter] ?? null;

            if (blank($value) || is_array($value) && $filter !== 'category') {
                continue;
            }

            $query = match ($filter) {
                'country' => $type === 'atoll'
                    ? static::atollsOfCountry($query, $value)
                    : $query->ofCountry($value),
                'state' => $query->ofState($value),
                'atoll' => $query->ofAtoll($value),
                'category' => $query->category(array_values(array_intersect(
                    (array) $value,
                    array_column(IslandCategories::cases(), 'value'),
                ))),
                'inhabited' => filter_var($value, FILTER_VALIDATE_BOOL) ? $query->inhabited() : $query,
                'named' => filter_var($value, FILTER_VALIDATE_BOOL) ? $query->named() : $query,
            };
        }

        return $query;
    }

    /**
     * Atolls belong to the Maldives: all of them for it, none for any other country
     */
    protected static function atollsOfCountry(Builder $query, mixed $country): Builder
    {
        $code = is_numeric($country)
            ? Locations::countryModel()::query()->whereKey($country)->value('code')
            : strtoupper((string) $country);

        if ($code === Country::MALDIVES) {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * The URL of the options endpoint for a type, when `Route::locationOptions()` registered it
     */
    public static function url(string $type): ?string
    {
        $name = static::routeName($type);

        return Route::has($name) ? route($name) : null;
    }

    /**
     * The name of the options route for a type, e.g. `location-options.islands`
     */
    public static function routeName(string $type): string
    {
        return config('locations.options_route', 'location-options') . '.' . Locations::resource($type);
    }
}
