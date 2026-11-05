---
title: Configuration
sidebar_position: 2
---

Publish the config to change it:

```bash
php artisan vendor:publish --tag=locations-config
```

Everything has a default, so most apps never need to.

| Option | Default | Description |
| --- | --- | --- |
| `models.country` | `null` | The country model: yours, extending `Javaabu\Locations\Models\Country`. `null` uses the package's, or its translatable variant when javaabu/translatable is enabled |
| `models.state` | `null` | The same, for states |
| `models.city` | `null` | The same, for cities |
| `models.atoll` | `null` | The same, for atolls |
| `models.island` | `null` | The same, for islands |
| `tables.countries` | `countries` | The countries table. Set the tables before migrating |
| `tables.states` | `states` | The states table |
| `tables.cities` | `cities` | The cities table |
| `tables.atolls` | `atolls` | The atolls table |
| `tables.islands` | `islands` | The islands table |
| `dhivehi_locales` | `['dv']` | App locales that show `dhivehi_name`. Other locales show the English `name` |
| `seed.countries` | `true` | `LocationsSeeder` and `locations:seed` seed the countries |
| `seed.atolls` | `true` | … the atolls |
| `seed.islands` | `true` | … the named islands |
| `seed.unnamed_islands` | `false` | … and the 429 unnamed features of the island register, keyed by their code |
| `seed.states` | `false` | … and the top-level ISO 3166-2 subdivisions of every country except the Maldives |
| `register_permissions` | `true` | Register the `view_`, `edit_` and `delete_` permissions of each type and their policies with javaabu/permissions |
| `views` | `env('LOCATIONS_VIEWS', 'blade')` | The admin pages' stack: `blade` or `inertia` |
| `layout` | `layouts.app` | The Blade layout the pages extend |
| `layout_section` | `content` | The section of the layout they fill |
| `per_page` | `20` | Rows per admin page (a request can ask for up to 100 with `per_page`) |
| `max_options` | `2000` | The most options the options endpoint returns for `per_page=-1`, and a Blade select loads |
| `options_route` | `location-options` | The route name prefix of `Route::locationOptions()`, which the selects find their endpoint by |
| `controllers.countries` | `CountriesController::class` | The countries pages' controller |
| `controllers.states` | `StatesController::class` | The states pages' controller |
| `controllers.cities` | `CitiesController::class` | The cities pages' controller |
| `controllers.atolls` | `AtollsController::class` | The atolls pages' controller |
| `controllers.islands` | `IslandsController::class` | The islands pages' controller |
| `controllers.options` | `LocationOptionsController::class` | The options endpoint's controller |

The controllers are in `Javaabu\Locations\Http\Controllers`.
