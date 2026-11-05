<?php

namespace Javaabu\Locations\Database\Seeders;

use Illuminate\Database\Seeder;
use Javaabu\Locations\Seeding\LocationsImporter;

class AtollsSeeder extends Seeder
{
    /** Overwrite rows that already exist with the package's data */
    public bool $update_existing = false;

    public function run(): void
    {
        (new LocationsImporter($this->update_existing))->atolls();
    }
}
