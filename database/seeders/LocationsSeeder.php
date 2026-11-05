<?php

namespace Javaabu\Locations\Database\Seeders;

use Illuminate\Database\Seeder;
use Javaabu\Locations\Seeding\LocationsImporter;

/**
 * Seeds what `config('locations.seed')` turns on: countries, atolls and islands by default
 *
 * Call it from your DatabaseSeeder: `$this->call(LocationsSeeder::class);`
 */
class LocationsSeeder extends Seeder
{
    /** Overwrite rows that already exist with the package's data */
    public bool $update_existing = false;

    public function run(): void
    {
        $importer = new LocationsImporter($this->update_existing);
        $seed = config('locations.seed', []);

        if ($seed['countries'] ?? true) {
            $importer->countries();
        }

        if ($seed['atolls'] ?? true) {
            $importer->atolls();
        }

        if ($seed['islands'] ?? true) {
            $importer->islands($seed['unnamed_islands'] ?? false);
        }

        if ($seed['states'] ?? false) {
            $importer->states();
        }
    }
}
