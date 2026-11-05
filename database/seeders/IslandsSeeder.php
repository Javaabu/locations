<?php

namespace Javaabu\Locations\Database\Seeders;

use Illuminate\Database\Seeder;
use Javaabu\Locations\Seeding\LocationsImporter;

/**
 * The islands of the register (and their atolls, which they need)
 */
class IslandsSeeder extends Seeder
{
    /** Overwrite rows that already exist with the package's data */
    public bool $update_existing = false;

    public function run(): void
    {
        $importer = new LocationsImporter($this->update_existing);

        $importer->atolls();
        $importer->islands(config('locations.seed.unnamed_islands', false));
    }
}
