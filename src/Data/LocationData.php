<?php

namespace Javaabu\Locations\Data;

use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Reads the seed data the package ships in database/data
 */
class LocationData
{
    /** @var array<string, array<string, mixed>> */
    protected static array $cache = [];

    public static function path(string $file = ''): string
    {
        return __DIR__ . '/../../database/data' . ($file === '' ? '' : "/{$file}");
    }

    /**
     * Every ISO 3166-1 country
     *
     * @return Collection<int, array{code: string, alpha3_code: string, numeric_code: string, name: string, iso_name: string, official_name: ?string, dhivehi_name: ?string, dhivehi_name_source: ?string, calling_code: ?string}>
     */
    public static function countries(): Collection
    {
        return collect(static::read('countries.json')['countries']);
    }

    /**
     * The administrative atolls of the Maldives and Malé City, Malé first, then north to south
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function atolls(): Collection
    {
        return collect(static::read('atolls.json')['atolls'])->sortBy('sort_order')->values();
    }

    /**
     * Islands of the national island register
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function islands(bool $include_unnamed = false): Collection
    {
        $islands = collect(static::read('islands.json')['islands']);

        if ($include_unnamed) {
            return $islands;
        }

        return $islands->whereNotNull('name')->values();
    }

    /**
     * Top-level ISO 3166-2 subdivisions, optionally of some countries only
     *
     * @param  array<int, string>|null  $countries  alpha-2 codes
     * @return Collection<int, array{code: string, country: string, name: string, type: string}>
     */
    public static function states(?array $countries = null): Collection
    {
        $states = collect(static::read('states.json')['states']);

        if ($countries === null) {
            return $states;
        }

        $countries = array_map('strtoupper', $countries);

        return $states->whereIn('country', $countries)->values();
    }

    /**
     * @return array<string, mixed>
     */
    protected static function read(string $file): array
    {
        if (isset(static::$cache[$file])) {
            return static::$cache[$file];
        }

        $path = static::path($file);

        if (! is_file($path)) {
            throw new RuntimeException("The locations data file [{$path}] is missing.");
        }

        return static::$cache[$file] = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    public static function flush(): void
    {
        static::$cache = [];
    }
}
