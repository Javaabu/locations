<?php

namespace Javaabu\Locations;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\City;
use Javaabu\Locations\Models\Country;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Models\State;
use Javaabu\Locations\Support\Translations;

/**
 * Resolves the configured models and tables of each location type
 */
class Locations
{
    /** The location types, keyed by their config key, with the package's models */
    public const array MODELS = [
        'country' => Country::class,
        'state' => State::class,
        'city' => City::class,
        'atoll' => Atoll::class,
        'island' => Island::class,
    ];

    /** The translatable variants the package ships, used when javaabu/translatable is enabled */
    public const array TRANSLATABLE_MODELS = [
        'country' => Models\Translatable\Country::class,
        'state' => Models\Translatable\State::class,
        'city' => Models\Translatable\City::class,
        'atoll' => Models\Translatable\Atoll::class,
        'island' => Models\Translatable\Island::class,
    ];

    /** Plural resource names, used for tables, permissions, routes and pages */
    public const array RESOURCES = [
        'country' => 'countries',
        'state' => 'states',
        'city' => 'cities',
        'atoll' => 'atolls',
        'island' => 'islands',
    ];

    /** Whether the service provider loads the package's migrations */
    public static bool $runs_migrations = true;

    /**
     * The model class of a location type: the app's from config, the translatable
     * variant when javaabu/translatable is enabled, or the package's own
     *
     * @return class-string<Model>
     */
    public static function model(string $type): string
    {
        if (! array_key_exists($type, self::MODELS)) {
            throw new InvalidArgumentException("Unknown location type [{$type}]. Use one of: " . implode(', ', array_keys(self::MODELS)) . '.');
        }

        $configured = config("locations.models.{$type}");

        if ($configured) {
            return $configured;
        }

        if (Translations::enabled()) {
            return self::TRANSLATABLE_MODELS[$type];
        }

        return self::MODELS[$type];
    }

    /** @return class-string<Country> */
    public static function countryModel(): string
    {
        return static::model('country');
    }

    /** @return class-string<State> */
    public static function stateModel(): string
    {
        return static::model('state');
    }

    /** @return class-string<City> */
    public static function cityModel(): string
    {
        return static::model('city');
    }

    /** @return class-string<Atoll> */
    public static function atollModel(): string
    {
        return static::model('atoll');
    }

    /** @return class-string<Island> */
    public static function islandModel(): string
    {
        return static::model('island');
    }

    /**
     * The location type of a model class or instance, e.g. `island`
     */
    public static function typeOf(object|string $model): ?string
    {
        foreach (self::MODELS as $type => $class) {
            if (is_a($model, $class, true)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * The table of a resource, e.g. `islands`
     */
    public static function table(string $resource): string
    {
        return config("locations.tables.{$resource}") ?: $resource;
    }

    /**
     * The plural resource name of a type: `island` → `islands`
     */
    public static function resource(string $type): string
    {
        return self::RESOURCES[$type] ?? throw new InvalidArgumentException("Unknown location type [{$type}].");
    }

    /**
     * Whether a locale shows Dhivehi names (`config('locations.dhivehi_locales')`)
     */
    public static function isDhivehiLocale(?string $locale = null): bool
    {
        $locale ??= app()->getLocale();

        return in_array($locale, config('locations.dhivehi_locales', ['dv']), true);
    }

    public static function ignoreMigrations(): void
    {
        static::$runs_migrations = false;
    }
}
