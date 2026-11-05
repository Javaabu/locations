---
title: Models
sidebar_position: 1
---

Five models in `Javaabu\Locations\Models`, one table each:

| Model | Table | Belongs to | Has many | Key code |
| --- | --- | --- | --- | --- |
| `Country` | `countries` | | `states`, `cities` | `code`: ISO 3166-1 alpha-2 (`MV`) |
| `State` | `states` | `country` | `cities` | `code`: ISO 3166-2 (`LK-1`), nullable |
| `City` | `cities` | `country`, `state` (nullable) | | `code`, nullable |
| `Atoll` | `atolls` | | `islands`, and `capital` (has one) | `code`: `HA`, `MLE` |
| `Island` | `islands` | `atoll` | | `code`: the island register's feature code (`LD0442`) |

Atolls and islands are Maldivian, so they have no country column. Countries have states and cities, for addresses outside the Maldives.

Always reach the models through `Locations` (or your own subclasses, see [Custom models](../advanced-usage/custom-models.md)), so an app's configured model is used:

```php
use Javaabu\Locations\Locations;

$island_class = Locations::islandModel(); // the configured class
$island = Locations::islandModel()::findByCode('LD0442');
```

# Names in English and Dhivehi

Every location has `name` (English) and `dhivehi_name` (Thaana, nullable). `localizedName()` picks one for a locale:

```php
$atoll = Atoll::findByCode('HA');

$atoll->name;                   // Haa Alifu
$atoll->dhivehi_name;           // ހާ އަލިފު
$atoll->localizedName('dv');    // ހާ އަލިފު
$atoll->localizedName('en');    // Haa Alifu
$atoll->localized_name;         // in app()->getLocale()
```

Dhivehi locales are the ones in `config('locations.dhivehi_locales')` (`['dv']`). A location without a Dhivehi name falls back to its English name. With [javaabu/translatable](../advanced-usage/translatable.md), names can be translated into any language.

:::info Why not `name_dv`?
`name_dv` is javaabu/translatable's naming for a field's translation. A real `name_dv` column would collide with it, so the column is `dhivehi_name`.
:::

# Countries

```php
use Javaabu\Locations\Models\Country;

$maldives = Country::findByCode('mv');      // case-insensitive

$maldives->code;                    // MV
$maldives->alpha3_code;             // MDV
$maldives->numeric_code;            // 462
$maldives->official_name;           // Republic of Maldives
$maldives->calling_code;            // 960
$maldives->formattedCallingCode();  // +960
$maldives->flag;                    // 🇲🇻
$maldives->isMaldives();            // true

Country::query()->code('LK')->first();
Country::query()->search('ސިންގަ')->get();   // names, Dhivehi names and codes
```

# States and cities

```php
use Javaabu\Locations\Models\City;
use Javaabu\Locations\Models\State;

State::query()->ofCountry('LK')->ordered()->get();     // a code, an id or a model
City::query()->ofCountry($country)->ofState($state)->get();

$state->type;        // Province (the ISO 3166-2 category)
$city->latitude;     // float|null
```

# Atolls

```php
use Javaabu\Locations\Models\Atoll;

$atoll = Atoll::findByCode('HA');

$atoll->code;                       // HA
$atoll->dhivehi_code;               // ހއ
$atoll->letter_code;                // A
$atoll->iso_code;                   // MV-07
$atoll->official_name;              // Thiladhunmathi Uthuruburi
$atoll->official_dhivehi_name;      // ތިލަދުންމަތީ އުތުރުބުރި
$atoll->is_city;                    // false (true for Malé, Addu and Fuvahmulah)
$atoll->capital;                    // the Island Dhidhdhoo
$atoll->islands;

$atoll->localizedCode('dv');        // ހއ
$atoll->localizedOfficialName('dv');
$atoll->optionLabel();              // HA. Haa Alifu / ހއ. ހާ އަލިފު

Atoll::query()->ordered()->get();   // Malé City, then north to south
```

Malé City is stored as an atoll (`MLE`, ISO `MV-MLE`) holding Malé, Hulhumalé and Vilin'gili, as the island register and ISO 3166-2 have it.

# Islands

```php
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Models\Island;

$island = Island::findByCode('LD0568');

$island->name;                  // Dhidhdhoo
$island->dhivehi_name;          // ދިއްދޫ
$island->atoll;                 // Haa Alifu
$island->category;              // IslandCategories::RESIDENTIAL
$island->is_capital;            // true
$island->latitude;              // 6.888…
$island->area;                  // hectares
$island->isInhabited();         // true

$island->formattedName('en');   // HA. Dhidhdhoo
$island->formattedName('dv');   // ހއ. ދިއްދޫ
$island->formatted_name;        // in the current locale; Malé City's islands have no prefix
```

| Scope | Islands |
| --- | --- |
| `ofAtoll('HA')` | Of an atoll: a code, an id or a model |
| `inhabited()` | Residential islands |
| `category(IslandCategories::TOURISM)` | Of one or more categories (enums or values) |
| `capitals()` | Atoll capitals |
| `named()` | With a name (the register also lists unnamed features) |
| `search('ދިއް')` | Matching a name, a Dhivehi name or a code |
| `ordered()` | By name in the current locale's language |

`IslandCategories` has `RESIDENTIAL`, `TOURISM`, `INDUSTRIAL`, `INSTITUTIONAL` and `UNINHABITED`, with `label()` (translated), `isInhabited()` and `labels()` for selects:

```php
IslandCategories::labels(); // ['residential' => 'Residential', 'tourism' => 'Tourism', …]
```

# On every model

| Method | Returns |
| --- | --- |
| `localizedName(?string $locale = null)` | The name for the locale |
| `localized_name` | The same, in the current locale |
| `optionLabel()` | What selects show: the localized name (`HA. Haa Alifu` for atolls) |
| `toOption()` | `['id' => …, 'name' => optionLabel()]`, as the options endpoint returns |
| `scopeSearch(?string $search)` | Matches names, Dhivehi names and codes |
| `scopeOrdered(?string $locale = null)` | Sorted by name in the locale's language (atolls: north to south) |
| `findByCode(string $code)` | Country, atoll and island only |

# Factories

Each model has a factory, for tests:

```php
Country::factory()->maldives()->create();
Atoll::factory()->haaAlifu()->create();
Atoll::factory()->male()->create();
Island::factory()->for($atoll)->capital()->create(['name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);
Island::factory()->category(IslandCategories::TOURISM)->create();
State::factory()->for($country)->create();
City::factory()->for($country)->create();
```

# Migrations

The package migrates its tables automatically. To change them, publish the migration first:

```bash
php artisan vendor:publish --tag=locations-migrations
```

Then stop the package from loading its own, in a service provider's `register()`:

```php
use Javaabu\Locations\Locations;

Locations::ignoreMigrations();
```

To use other table names (an app that already has a `countries` table), set `config('locations.tables')` before migrating. Every table also has nullable `translations` and `lang` columns for javaabu/translatable, which do nothing without it.
