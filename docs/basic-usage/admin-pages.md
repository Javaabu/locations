---
title: Admin pages
sidebar_position: 4
---

Pages to list, search, add, view, edit and delete countries, states, cities, atolls and islands. They come in Blade (javaabu/forms) or Inertia React (@javaabu/forms), and every request that expects JSON gets JSON.

# Registering the routes

```php
Route::middleware('web')->group(function () {
    Route::locationOptions();

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::locations(['guard' => 'web_admin']);
    });
});
```

| Option | Default | Description |
| --- | --- | --- |
| `guard` | `config('auth.defaults.guard')` | The guard the pages authenticate and authorize against |
| `prefix` | `locations` | The URL prefix inside your group |
| `name` | `locations` | The route name prefix inside your group |
| `middleware` | `["auth:{guard}"]` | The pages' middleware |
| `only` | every type | The resources to register: `['atolls', 'islands']` |
| `except` | `[]` | Resources to leave out |
| `controllers` | `config('locations.controllers')` | Controllers per resource: `['islands' => IslandsController::class]` |

With the group above, the islands pages are:

| Method | URL | Name | Action |
| --- | --- | --- | --- |
| `GET` | `/admin/locations/islands` | `admin.locations.islands.index` | List, search and filter |
| `GET` | `/admin/locations/islands/create` | `admin.locations.islands.create` | The create form |
| `POST` | `/admin/locations/islands` | `admin.locations.islands.store` | Create |
| `GET` | `/admin/locations/islands/{island}` | `admin.locations.islands.show` | The details, and links to its children |
| `GET` | `/admin/locations/islands/{island}/edit` | `admin.locations.islands.edit` | The edit form |
| `PUT`/`PATCH` | `/admin/locations/islands/{island}` | `admin.locations.islands.update` | Update |
| `DELETE` | `/admin/locations/islands/{island}` | `admin.locations.islands.destroy` | Delete |

The same goes for `countries`, `states`, `cities` and `atolls`. Call the macro again for another guard. For example, a council portal for its users:

```php
Route::prefix('portal')->name('portal.')->group(function () {
    Route::locations(['guard' => 'web', 'only' => ['islands']]);
});
```

# What each page does

**Index.** It shows a table of the type's columns, searched with `?search=` (names in English and Thaana, and codes) and filtered with the type's filters:

| Type | Columns | Filters |
| --- | --- | --- |
| Countries | flag and name, Dhivehi name, code, calling code | |
| States | code, name, type, country | `country` |
| Cities | name, Dhivehi name, state, country | `country`, `state` |
| Atolls | code, name, Dhivehi name, official name, capital | |
| Islands | code, name, Dhivehi name, atoll, category | `atoll`, `category` |

Atolls are ordered from Malé City north to south, and everything else by name in the current language. The first column links to the location. Each row shows View, Edit and Delete for what the user may do.

**Create and edit.** These pages render the type's [stencil](https://github.com/Javaabu/stencil) form (`Javaabu\Locations\Forms\IslandForm`, …). Its rules come from the form: codes are unique, parents must exist, coordinates are in range, and Dhivehi fields type Thaana. After saving, the page redirects to the location with a message.

**Show.** This page lists the location's details. Atolls link to their islands, countries to their states and cities, and states to their cities.

**Delete.** Delete asks for confirmation. A location with children (an atoll with islands, a country with states or cities, a state with cities) isn't deleted: the page says why. The database refuses it too.

# Permissions

Each type has three permissions, registered through javaabu/permissions:

| Permission | Lets the user |
| --- | --- |
| `view_islands` | See the list and the details |
| `edit_islands` | Create and edit |
| `delete_islands` | Delete |

The same goes for `countries`, `states`, `cities` and `atolls`. Sync them after installing, then give them to your roles:

```bash
php artisan permissions:sync
```

```php
$role->givePermissionTo(['view_islands', 'edit_islands']);
```

See [Permissions and policies](../advanced-usage/permissions-and-policies.md) to change who may do what.

# JSON

Send `Accept: application/json` to any page:

```bash
curl -H "Accept: application/json" https://example.test/admin/locations/islands?atoll=HA
```

```json
{
    "data": [
        {
            "id": 12,
            "type": "island",
            "code": "LD0568",
            "name": "Dhidhdhoo",
            "dhivehi_name": "ދިއްދޫ",
            "localized_name": "Dhidhdhoo",
            "label": "Dhidhdhoo",
            "formatted_name": "HA. Dhidhdhoo",
            "category": "residential",
            "category_label": "Residential",
            "is_capital": true,
            "latitude": 6.888109,
            "longitude": 73.110928,
            "area": 96.5429,
            "atoll": { "id": 2, "type": "atoll", "code": "HA", "label": "HA. Haa Alifu", "…": "…" },
            "can": { "view": true, "update": true, "delete": false },
            "links": { "show": "…", "edit": "…", "update": "…", "destroy": "…" }
        }
    ],
    "links": { "…": "…" },
    "meta": { "current_page": 1, "total": 1, "…": "…" }
}
```

| Request | Response |
| --- | --- |
| `GET index` | Paginated locations (`per_page` up to 100) |
| `GET show` | `{ data: location }` |
| `GET create`, `GET edit` | `{ data: { form, location } }`: the stencil form as JSON |
| `POST store` | `201` and `{ data: location }`, or `422` with errors |
| `PATCH update` | `{ data: location }`, or `422` |
| `DELETE destroy` | `204`, or `409` with a `message` when it has children |

# The Inertia pages

With `LOCATIONS_VIEWS=inertia`, the same pages render as `locations::Resources/Index`, `locations::Resources/Form` and `locations::Resources/Show`. Their props are typed in `@javaabu/locations` (`LocationsIndexProps`, `LocationsFormProps`, `LocationsShowProps`). Every page gets:

| Prop | What |
| --- | --- |
| `title`, `type`, `resource`, `labels` | The page title, `island`, `islands`, and the translated singular and plural |
| `guard` | The routes' guard |
| `urls` | `index`, `create`, `store`, and the location's `show`, `edit`, `update`, `destroy` |
| `can` | `create`, and the location's `view`, `update`, `delete` |
| `status` | The flashed `success` or `error` message |
| `strings` | The page's text in the current language |

Index pages also get `columns`, `rows`, `locations` (the paginator), `filters` (a stencil form) and `filtered`. Form pages get `form` (a stencil form) and `location`. Show pages get `location`, `details` and `related`.
