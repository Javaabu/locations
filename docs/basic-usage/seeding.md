---
title: Seeding the data
sidebar_position: 2
---

The package ships every country, every Maldivian atoll and every named island of the national island register (see [Data sources](../advanced-usage/data-sources.md)). Seed them once after migrating.

# From the command line

```bash
php artisan migrate
php artisan locations:seed
```

```
Seeding countries…
Seeding atolls…
Seeding islands…
+-----------+---------+---------+-----------+
| Type      | Created | Updated | Unchanged |
+-----------+---------+---------+-----------+
| country   | 249     | 0       | 0         |
| atoll     | 21      | 0       | 0         |
| island    | 1131    | 0       | 0         |
+-----------+---------+---------+-----------+
```

| Usage | Seeds |
| --- | --- |
| `locations:seed` | What `config('locations.seed')` turns on: countries, atolls and islands by default |
| `locations:seed countries atolls` | Only those types (`countries`, `atolls`, `islands`, `states`) |
| `locations:seed islands --unnamed` | Islands, including the 429 unnamed features of the register |
| `locations:seed states` | Top-level ISO 3166-2 subdivisions of every country (seed countries first) |
| `locations:seed states --country=LK --country=IN` | Only the states of those countries |
| `locations:seed --update` | Also overwrite locations that already exist with the package's data |

Locations are matched by their code: the ISO 3166-1 alpha-2 code for countries, the atoll code (`HA`), the island register's feature code (`LD0442`) and the ISO 3166-2 code for states. Seeding again only adds what's missing, so names your admins edited are kept. `--update` brings every seeded row back to the package's data. Nothing is ever deleted.

# From your DatabaseSeeder

```php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Javaabu\Locations\Database\Seeders\LocationsSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(LocationsSeeder::class);
    }
}
```

| Seeder | Seeds |
| --- | --- |
| `LocationsSeeder` | What `config('locations.seed')` turns on |
| `CountriesSeeder` | Countries |
| `AtollsSeeder` | Atolls |
| `IslandsSeeder` | Atolls, then islands (with `config('locations.seed.unnamed_islands')`) |
| `StatesSeeder` | States of the seeded countries |

All are in `Javaabu\Locations\Database\Seeders` and leave existing rows alone. To overwrite them from a seeder, use the importer (below) with `updatingExisting()`.

# What gets seeded

```php
// config/locations.php
'seed' => [
    'countries' => true,
    'atolls' => true,
    'islands' => true,
    'unnamed_islands' => false,
    'states' => false,
],
```

States are off by default: most Maldivian apps only need countries, atolls and islands. The Maldives has no states; its subdivisions are the atolls.

# In code

`LocationsImporter` is what the command and seeders use:

```php
use Javaabu\Locations\Seeding\LocationsImporter;

$importer = new LocationsImporter;

$importer->countries();                    // ImportResult
$importer->atolls();
$importer->islands(include_unnamed: true);
$importer->states(['LK', 'IN']);

$result = (new LocationsImporter)->updatingExisting()->islands();

$result->created;   // 0
$result->updated;   // 1131
$result->skipped;   // 0
```

To read the data without a database, use `LocationData`:

```php
use Javaabu\Locations\Data\LocationData;

LocationData::countries()->firstWhere('code', 'MV');
// ['code' => 'MV', 'alpha3_code' => 'MDV', 'numeric_code' => '462', 'name' => 'Maldives',
//  'dhivehi_name' => 'ދިވެހިރާއްޖެ', 'calling_code' => '960', …]

LocationData::atolls();                      // Malé City first, then north to south
LocationData::islands()->where('atoll', 'HA');
LocationData::islands(include_unnamed: true);
LocationData::states(['LK']);
```

# In tests

Seeding everything takes under a second on SQLite. In tests that only need a few locations, use the [factories](./models.md#factories) instead.
