# javaabu/locations API

## Models (`Javaabu\Locations\Models`)

| Model | Key attributes | Relations | Lookups and scopes |
| --- | --- | --- | --- |
| `Country` | `code` (MV), `alpha3_code` (MDV), `numeric_code` (462), `name`, `dhivehi_name`, `official_name`, `calling_code` (960), `flag` (🇲🇻) | `states`, `cities` | `findByCode()`, `code()`, `isMaldives()`, `formattedCallingCode()` (+960) |
| `State` | `country_id`, `code` (LK-1), `name`, `dhivehi_name`, `type` | `country`, `cities` | `ofCountry($model\|$id\|'LK')` |
| `City` | `country_id`, `state_id`, `code`, `name`, `dhivehi_name`, `latitude`, `longitude` | `country`, `state` | `ofCountry()`, `ofState()` |
| `Atoll` | `code` (HA, MLE), `dhivehi_code` (ހއ), `letter_code` (A), `iso_code` (MV-07), `name`, `dhivehi_name`, `official_name`, `official_dhivehi_name`, `is_city`, `sort_order` | `islands`, `capital` | `findByCode()`, `code()`, `localizedCode()`, `localizedOfficialName()`, `ordered()` (Malé City, then north to south) |
| `Island` | `atoll_id`, `code` (LD0442), `name`, `dhivehi_name`, `category` (`IslandCategories`), `is_capital`, `latitude`, `longitude`, `area` (hectares) | `atoll` | `findByCode()`, `ofAtoll()`, `category()`, `inhabited()`, `named()`, `capitals()`, `isInhabited()`, `formattedName()` |

Every model: `localizedName(?string $locale)`, `localized_name`, `optionLabel()`, `toOption()`, `search(?string)`, `ordered(?string $locale)`, a factory (`Country::factory()->maldives()`, `Atoll::factory()->haaAlifu()`, `->male()`, `Island::factory()->capital()`, `->category(...)`).

`IslandCategories`: `RESIDENTIAL`, `TOURISM`, `INDUSTRIAL`, `INSTITUTIONAL`, `UNINHABITED`. Methods: `label()`, `isInhabited()`, `labels()`.

## `Javaabu\Locations\Locations`

`model($type)`, `countryModel()`, `stateModel()`, `cityModel()`, `atollModel()`, `islandModel()`, `table($resource)`, `resource($type)`, `typeOf($model)`, `isDhivehiLocale($locale)`, `ignoreMigrations()`.

## Seeding

- `php artisan locations:seed {countries|atolls|islands|states ...} {--update} {--unnamed} {--country=LK}`
- Seeders in `Javaabu\Locations\Database\Seeders`: `LocationsSeeder`, `CountriesSeeder`, `AtollsSeeder`, `IslandsSeeder`, `StatesSeeder`.
- `(new LocationsImporter)->updatingExisting()->islands(include_unnamed: true)` returns an `ImportResult` (`created`, `updated`, `skipped`).
- `LocationData::countries()`, `atolls()`, `islands()`, `states(['LK'])` read the JSON without a database.

## Routes

```php
Route::locationOptions(['prefix' => 'location-options', 'middleware' => []]);
Route::locations([
    'guard' => 'web_admin',
    'prefix' => 'locations',
    'name' => 'locations',
    'middleware' => ['auth:web_admin'],
    'only' => ['atolls', 'islands'],
    'except' => [],
    'controllers' => ['islands' => IslandsController::class],
]);
```

Options endpoint: `GET /location-options/{countries,states,cities,atolls,islands}`. It takes `filter[search]`, `filter[country]` (states, cities and atolls), `filter[state]` (cities), and `filter[atoll]`, `filter[category]`, `filter[inhabited]`, `filter[named]` (islands), plus `per_page` (`-1` returns every match) and `page`. Items are `{ id, name, code }`.

## Selects

| Blade | React | Stencil |
| --- | --- | --- |
| `<x-locations::country-select>` | `CountrySelect` | `Stencil\CountrySelect::make('country_id')` |
| `<x-locations::state-select parent="country_id">` | `StateSelect parent="#country_id"` | `StateSelect::make('state_id')->dependsOn('country_id')` |
| `<x-locations::city-select parent="state_id">` | `CitySelect parent="#state_id"` | `CitySelect::make('city_id')->dependsOn('state_id')` |
| `<x-locations::atoll-select child="island_id">` | `AtollSelect id="atoll_id"` | `AtollSelect::make('atoll_id')` |
| `<x-locations::island-select parent="atoll_id">` | `IslandSelect parent="#atoll_id"` | `IslandSelect::make('island_id')->dependsOn('atoll_id')` |

- Blade attributes: `name`, `label`, `parent`, `parent-value`, `child`, `filter-field`, `filters`, `options`, plus every `<x-forms::select2>` attribute.
- React props: `type` (on `LocationSelect`), `options`, `parent`, `filterField`, `filters`, `endpoint`, plus every `Select2` prop. `LocationsProvider endpoint="…"` sets the endpoint for every select inside it.
- Stencil methods: `dependsOn($field, $filter = null)`, `filters([...])`, `query(fn (Builder $query) => …)`, plus every `Select` method. Each field validates that the location exists.

## Admin pages

- **Controllers:** `Http\Controllers\{Countries,States,Cities,Atolls,Islands}Controller`, extending `LocationsController`. Override `query()`, `columns()`, `cells()`, `detailValues()`, `filterFields()`, `formClass()`, `saving()`, `children()`.
- **Forms:** `Forms\{Country,State,City,Atoll,Island}Form` (stencil). **Policies:** `Policies\{Country,State,City,Atoll,Island}Policy` (`ResourcePolicy`, `view_`/`edit_`/`delete_`).
- **Blade views** (`--tag=locations-views`): `resources/index`, `resources/form`, `resources/show`, `components/location-select`.
- **Inertia pages:** `locations::Resources/Index`, `locations::Resources/Form`, `locations::Resources/Show`. Props are typed as `LocationsIndexProps`, `LocationsFormProps` and `LocationsShowProps` from `@javaabu/locations`.

## Config (`config/locations.php`)

`models.*`, `tables.*`, `dhivehi_locales`, `seed.{countries,atolls,islands,unnamed_islands,states}`, `register_permissions`, `views` (`LOCATIONS_VIEWS`), `layout`, `layout_section`, `per_page`, `max_options`, `options_route`, `controllers.*`.
