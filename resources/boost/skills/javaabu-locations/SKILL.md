---
name: javaabu-locations
description: Work with countries, states, cities, Maldivian atolls and islands using javaabu/locations. Use when seeding location data (php artisan locations:seed, LocationsSeeder), querying the Country, State, City, Atoll and Island models (findByCode, ofAtoll, inhabited, localizedName, formattedName), relating app models (addresses, people, facilities) to islands or countries, building address forms with cascading selects (<x-locations::island-select>, IslandSelect from @javaabu/locations, the stencil IslandSelect::make()->dependsOn()), registering Route::locations() admin pages or Route::locationOptions(), authorizing them with view_islands/edit_islands permissions, overriding the controllers, forms or views, or javaabu/translatable names.
license: MIT
metadata:
  author: Javaabu
---

# Locations with javaabu/locations

The docs ship with the package: `vendor/javaabu/locations/llms.txt` lists every page in `vendor/javaabu/locations/docs/`.

| Task | Read |
| --- | --- |
| Seed and query locations, address forms, admin pages | This file |
| Every class, method, option and JS export | [references/api.md](references/api.md) |
| Mistakes to avoid | [references/common-mistakes.md](references/common-mistakes.md) |

## 1. Seed the data once

```bash
php artisan migrate
php artisan locations:seed        # 249 countries, 21 atolls, 1,131 named islands
php artisan permissions:sync      # view_/edit_/delete_ permissions of each type
```

`locations:seed states` adds top-level ISO 3166-2 states, and `--unnamed` adds the register's unnamed islands. Seeding again only adds what's missing. `--update` overwrites names admins edited.

In app seeders: `$this->call(\Javaabu\Locations\Database\Seeders\LocationsSeeder::class);`.

## 2. Query

```php
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Country;
use Javaabu\Locations\Models\Island;

$maldives = Country::findByCode('MV');            // code, alpha3_code, numeric_code, calling_code, flag
$atoll = Atoll::findByCode('HA');                 // dhivehi_code ހއ, letter_code A, iso_code MV-07, official_name
$male = Island::findByCode('LD0442');             // the island register's feature code

$islands = Locations::islandModel()::query()
    ->ofAtoll('HA')                               // a code, an id or a model
    ->inhabited()                                 // or ->category(IslandCategories::TOURISM)
    ->ordered()                                   // by name in the current language
    ->get();

$island->localizedName('dv');                     // ދިއްދޫ
$island->formattedName('dv');                     // ހއ. ދިއްދޫ (Malé City's islands have no prefix)
$atoll->capital;                                  // the atoll's capital Island
$atoll->optionLabel();                            // HA. Haa Alifu
```

Malé City is the atoll `MLE`, holding Malé, Hulhumalé and Vilin'gili. Atolls have no country column, because they're all Maldivian.

## 3. Relate your models

```php
Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('country_id')->constrained(Locations::table('countries'));
    $table->foreignId('island_id')->nullable()->constrained(Locations::table('islands'));
    $table->string('city')->nullable();       // for addresses outside the Maldives
    $table->timestamps();
});
```

```php
public function island(): BelongsTo
{
    return $this->belongsTo(Locations::islandModel());
}
```

## 4. Address forms

Register the options endpoint the cascades load from:

```php
Route::middleware('web')->group(function () {
    Route::locationOptions();
});
```

**Stencil** (one schema, both stacks):

```php
use Javaabu\Locations\Stencil\AtollSelect;
use Javaabu\Locations\Stencil\CountrySelect;
use Javaabu\Locations\Stencil\IslandSelect;
use Javaabu\Stencil\Fields\TextInput;

protected function definition(): array
{
    return [
        CountrySelect::make('country_id')->label(__('Country'))->required(),
        AtollSelect::make('atoll_id')->dependsOn('country_id'),            // every atoll for MV, none otherwise
        IslandSelect::make('island_id')->dependsOn('atoll_id')->filters(['inhabited' => true]),
        TextInput::make('city')->label(__('City')),           // for addresses outside the Maldives
    ];
}
```

**Blade** (javaabu/forms):

```blade
<x-forms::form :model="$address">
    <x-locations::country-select child="atoll_id" required />
    <x-locations::atoll-select parent="country_id" child="island_id" fallback="#city" />
    <x-locations::island-select parent="atoll_id" :filters="['inhabited' => true]" />
    <x-forms::text name="city" id="city" :label="__('City')" />
</x-forms::form>
```

**React** (@javaabu/forms):

```tsx
import { AtollSelect, CountrySelect, IslandSelect } from "@javaabu/locations"

<CountrySelect name="country_id" id="country_id" label="ޤައުމު" required />
<AtollSelect name="atoll_id" id="atoll_id" label="އަތޮޅު" parent="#country_id" fallback="#city" />
<IslandSelect name="island_id" label="ރަށް" parent="#atoll_id" filters={{ inhabited: true }} />
```

Blade forms need no npm packages. React needs `@javaabu/forms` and React, which `php artisan manifest:check` lists once `@inertiajs/react` is in `package.json` (or with `--scope=pages`).

The stencil fields validate that the chosen location exists. With a plain FormRequest, use `Rule::exists(Locations::islandModel(), 'id')`.

## 5. Admin pages

```php
Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
    Route::locations(['guard' => 'web_admin']);                       // every type
    // Route::locations(['guard' => 'web_admin', 'only' => ['atolls', 'islands']]);
});
```

These register `admin.locations.{countries,states,cities,atolls,islands}.{index,create,store,show,edit,update,destroy}`. The pages render in Blade by default, or in Inertia with `LOCATIONS_VIEWS=inertia` (`locations::Resources/Index|Form|Show`). Requests that expect JSON get JSON.

Permissions per type: `view_islands`, `edit_islands`, `delete_islands`. Grant them to roles of the routes' guard. To customise:

```php
// config/locations.php
'controllers' => ['islands' => App\Http\Controllers\Admin\IslandsController::class],   // extends IslandsController
'layout' => 'layouts.admin',

// config/permissions.php
'policies' => [Javaabu\Locations\Models\Island::class => App\Policies\IslandPolicy::class],
```

## 6. Tests

```php
$atoll = Atoll::factory()->haaAlifu()->create();
$island = Island::factory()->for($atoll)->create(['name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);
Country::factory()->maldives()->create();

// or the real data (under a second on SQLite)
$this->seed(\Javaabu\Locations\Database\Seeders\LocationsSeeder::class);
```

Use Thaana in tests of user-facing behaviour.
