<?php

namespace Javaabu\Locations\Commands;

use Illuminate\Console\Command;
use Javaabu\Locations\Seeding\ImportResult;
use Javaabu\Locations\Seeding\LocationsImporter;

class SeedLocationsCommand extends Command
{
    protected $signature = 'locations:seed
        {types?* : countries, atolls, islands or states (default: what config(\'locations.seed\') turns on)}
        {--update : Overwrite locations that already exist with the package\'s data}
        {--unnamed : Include the unnamed features of the island register}
        {--country=* : Only seed the states of these countries (alpha-2 codes)}';

    protected $description = 'Seed countries, Maldivian atolls and islands, and ISO 3166-2 states from the package\'s data';

    public const array TYPES = ['countries', 'atolls', 'islands', 'states'];

    public function handle(): int
    {
        $types = $this->argument('types') ?: $this->configuredTypes();
        $unknown = array_diff($types, self::TYPES);

        if ($unknown !== []) {
            $this->error('Unknown types: ' . implode(', ', $unknown) . '. Use: ' . implode(', ', self::TYPES) . '.');

            return self::FAILURE;
        }

        $importer = new LocationsImporter((bool) $this->option('update'));
        $results = [];

        foreach (self::TYPES as $type) {
            if (! in_array($type, $types, true)) {
                continue;
            }

            $this->comment("Seeding {$type}…");

            $results[] = match ($type) {
                'countries' => $importer->countries(),
                'atolls' => $importer->atolls(),
                'islands' => $importer->islands($this->option('unnamed') || config('locations.seed.unnamed_islands', false)),
                'states' => $importer->states($this->option('country') ?: null),
            };
        }

        $this->table(
            ['Type', 'Created', 'Updated', 'Unchanged'],
            array_map(fn (ImportResult $result) => [$result->type, $result->created, $result->updated, $result->skipped], $results),
        );

        if (! $this->option('update')) {
            $this->line('Locations that already existed were left alone. Pass --update to overwrite them.');
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    protected function configuredTypes(): array
    {
        $seed = config('locations.seed', []);

        return array_values(array_filter(self::TYPES, fn (string $type) => $seed[$type] ?? ($type !== 'states')));
    }
}
