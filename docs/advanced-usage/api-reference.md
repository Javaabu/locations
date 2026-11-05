---
title: API reference
sidebar_position: 10
---

# PHP

## `Javaabu\Locations\Locations`

| Member | Returns |
| --- | --- |
| `model(string $type)` | The configured model class of `country`, `state`, `city`, `atoll` or `island` |
| `countryModel()`, `stateModel()`, `cityModel()`, `atollModel()`, `islandModel()` | The same, per type |
| `table(string $resource)` | The configured table of `countries`, `states`, … |
| `resource(string $type)` | The plural resource name: `island` → `islands` |
| `typeOf(object\|string $model)` | The type of a model instance or class, or `null` |
| `isDhivehiLocale(?string $locale = null)` | Whether the locale (default: the current one) shows Dhivehi names |
| `ignoreMigrations()` | Stops the package from loading its migration |
| `MODELS`, `TRANSLATABLE_MODELS`, `RESOURCES` | The package's models, translatable variants and resource names, by type |

## Models

`Javaabu\Locations\Models\Country`, `State`, `City`, `Atoll` and `Island`. Every model has the methods of `Concerns\IsLocation`:

| Method | Returns |
| --- | --- |
| `localizedName(?string $locale = null)` | The Dhivehi name for Dhivehi locales (when it has one), the English name otherwise |
| `localized_name` (attribute) | `localizedName()` in the current locale |
| `optionLabel()` | What selects show |
| `toOption()` | `['id' => …, 'name' => optionLabel()]` |
| `search(?string $search)` (scope) | Names, Dhivehi names and codes containing the text |
| `ordered(?string $locale = null)` (scope) | By name in the locale's language |

| Model | Attributes | Relations | Methods and scopes |
| --- | --- | --- | --- |
| `Country` | `code`, `alpha3_code`, `numeric_code`, `name`, `dhivehi_name`, `official_name`, `calling_code`, `flag` | `states`, `cities` | `findByCode()`, `code()`, `isMaldives()`, `formattedCallingCode()`, `MALDIVES` |
| `State` | `country_id`, `code`, `name`, `dhivehi_name`, `type` | `country`, `cities` | `ofCountry()` |
| `City` | `country_id`, `state_id`, `code`, `name`, `dhivehi_name`, `latitude`, `longitude` | `country`, `state` | `ofCountry()`, `ofState()` |
| `Atoll` | `code`, `dhivehi_code`, `letter_code`, `iso_code`, `name`, `dhivehi_name`, `official_name`, `official_dhivehi_name`, `is_city`, `sort_order` | `islands`, `capital` | `findByCode()`, `code()`, `localizedCode()`, `localizedOfficialName()`, `ordered()` (north to south) |
| `Island` | `atoll_id`, `code`, `name`, `dhivehi_name`, `category`, `is_capital`, `latitude`, `longitude`, `area`, `formatted_name` | `atoll` | `findByCode()`, `code()`, `ofAtoll()`, `category()`, `inhabited()`, `named()`, `capitals()`, `isInhabited()`, `formattedName()` |

`ofCountry()` and `ofAtoll()` take a model, an id or a code.

## `Javaabu\Locations\Enums\IslandCategories`

`RESIDENTIAL`, `TOURISM`, `INDUSTRIAL`, `INSTITUTIONAL`, `UNINHABITED`, with `label()`, `isInhabited()`, `labels()` and `fromRegister(string $category)` (the island register's category names).

## Data and seeding

| Class | Members |
| --- | --- |
| `Data\LocationData` | `countries()`, `atolls()`, `islands(bool $include_unnamed = false)`, `states(?array $countries = null)`: collections of the seed data; `path(string $file)` |
| `Seeding\LocationsImporter` | `__construct(bool $update = false)`, `updatingExisting(bool $update = true)`, `countries()`, `atolls()`, `islands(bool $include_unnamed = false)`, `states(?array $countries = null)`, each returning an `ImportResult` |
| `Seeding\ImportResult` | `type`, `created`, `updated`, `skipped`, `total()` |
| `Database\Seeders\LocationsSeeder` | Seeds what `config('locations.seed')` turns on. `CountriesSeeder`, `AtollsSeeder`, `IslandsSeeder` and `StatesSeeder` seed one type. Each has `$update_existing` |

`php artisan locations:seed {types?*} {--update} {--unnamed} {--country=*}` (see [Seeding](../basic-usage/seeding.md)).

## Routes

| Macro | Options |
| --- | --- |
| `Route::locations(array $options = [])` | `guard`, `prefix`, `name`, `middleware`, `only`, `except`, `controllers` |
| `Route::locationOptions(array $options = [])` | `prefix`, `middleware`, `controller` |

`Routing\LocationRoutes::guard(Request)`, `type(Request)` and `namePrefix(Request)` read the current route's guard, type and route name prefix.

## Controllers, forms and resources

| Class | What |
| --- | --- |
| `Http\Controllers\LocationsController` | The base of `CountriesController`, `StatesController`, `CitiesController`, `AtollsController` and `IslandsController` (see [Overriding](./overriding-controllers-and-views.md)) |
| `Http\Controllers\LocationOptionsController` | The options endpoint |
| `Http\Resources\LocationResource` | Any location as JSON; `withMeta(array $links, array $abilities)` |
| `Forms\LocationForm` | The base of `CountryForm`, `StateForm`, `CityForm`, `AtollForm` and `IslandForm` (stencil forms) |
| `Policies\LocationPolicy` | The base of `CountryPolicy`, `StatePolicy`, `CityPolicy`, `AtollPolicy` and `IslandPolicy` |
| `Views\ViewRenderer` | `render(string $page, array $data)`, `isInertia()`, `inertiaPage(string $page)` |

## Selects

| Class | What |
| --- | --- |
| `Stencil\LocationSelect` | The stencil fields `CountrySelect`, `StateSelect`, `CitySelect`, `AtollSelect` and `IslandSelect`: `dependsOn()`, `filters()`, `query()`, `optionsQuery()`, and every stencil `Select` method |
| `Views\Components\LocationSelect` | The Blade components `<x-locations::country-select>`, … (see [Location selects](../basic-usage/location-selects.md#blade)) |
| `Support\LocationOptions` | `filter(string $type, Builder $query, array $filters)`, `url(string $type)`, `routeName(string $type)`, `FILTERS` |
| `Support\Translations` | `enabled()`, `supports($model, ?string $field)`, `form($model)`, `rules($model, array $rules)` |

# JavaScript (`@javaabu/locations`)

| Export | What |
| --- | --- |
| `CountrySelect`, `StateSelect`, `CitySelect`, `AtollSelect`, `IslandSelect` | Selects of one type (`TypedLocationSelectProps`) |
| `LocationSelect` | A select of `type`'s locations (`LocationSelectProps`) |
| `LocationsProvider` | Sets the options endpoint (`endpoint`) for the selects inside it |
| `useLocationsConfig()` | The provider's config |
| `useLocationOptions(url, enabled)` | Loads every option of a URL once: `{ options, loading, error }` |
| `locationOptionsUrl(endpoint, type, filters?)` | The endpoint URL of a type, with filters |
| `toOptions(options)` | Endpoint options as `{ value, label }` |
| `LOCATION_RESOURCES`, `PARENT_FILTERS` | Resource names and default parent filters, by type |
| `LocationActions`, `StatusAlerts` | The pages' buttons and flashed messages |
| Types | `LocationType`, `LocationOption`, `LocationData`, `LocationsIndexProps`, `LocationsFormProps`, `LocationsShowProps`, `LocationsPageProps`, `LocationRow`, `LocationColumn`, `LocationDetail`, `LaravelPaginator`, … |

The Inertia pages are `locations::Resources/Index`, `locations::Resources/Form` and `locations::Resources/Show`.
