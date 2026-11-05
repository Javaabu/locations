---
title: The options endpoint
sidebar_position: 6
---

`Route::locationOptions()` registers a read-only JSON endpoint per type. It's what the cascading selects load their options from, in the format javaabu/forms' and @javaabu/forms' select2 request.

```php
Route::middleware('web')->group(function () {
    Route::locationOptions();
});
```

| URL | Name |
| --- | --- |
| `GET /location-options/countries` | `location-options.countries` |
| `GET /location-options/states` | `location-options.states` |
| `GET /location-options/cities` | `location-options.cities` |
| `GET /location-options/atolls` | `location-options.atolls` |
| `GET /location-options/islands` | `location-options.islands` |

| Option | Default | Description |
| --- | --- | --- |
| `prefix` | `location-options` | The URL prefix |
| `middleware` | `[]` | More middleware, e.g. `['auth']` or `['throttle:60,1']` |
| `controller` | `config('locations.controllers.options')` | The controller |

The route names come from `config('locations.options_route')`, and the selects find the endpoint by them. To rename the routes, change the config, not the macro.

The endpoint is public by default: it only lists names and codes. If yours aren't public, put it behind middleware and give the same users the selects.

# Requests

```
GET /location-options/islands?filter[atoll]=2&filter[search]=dhi&per_page=-1
```

| Parameter | Description |
| --- | --- |
| `filter[search]` | Matches names, Dhivehi names and codes |
| `filter[…]` | The type's filters (below) |
| `per_page` | Options per page, up to 100 (default 20). `-1` returns every match, up to `config('locations.max_options')` |
| `page` | The page |

Other parameters the select scripts send (`fields`, `include`, `append`) are ignored.

## Filters

| Type | Filter | Value |
| --- | --- | --- |
| states | `country` | A country's id or alpha-2 code |
| cities | `country` | A country's id or alpha-2 code |
| cities | `state` | A state's id |
| atolls | `country` | A country's id or code: every atoll for the Maldives, none for any other |
| islands | `atoll` | An atoll's id or code (`HA`) |
| islands | `category` | A category, or several (`filter[category][]=residential&filter[category][]=tourism`) |
| islands | `inhabited` | `1` for residential islands only |
| islands | `named` | Named islands only. On by default, `0` includes unnamed features |

Unknown filters are ignored.

# Responses

With `per_page=-1`:

```json
{
    "data": [
        { "id": 12, "name": "Dhidhdhoo", "code": "LD0568" },
        { "id": 15, "name": "Filladhoo", "code": "LD0993" }
    ]
}
```

Otherwise, a Laravel paginator, with `next_page_url` for the select's infinite scroll:

```json
{
    "current_page": 1,
    "data": [{ "id": 12, "name": "Dhidhdhoo", "code": "LD0568" }],
    "next_page_url": "https://example.test/location-options/islands?page=2",
    "last_page": 3,
    "total": 52,
    "…": "…"
}
```

`name` is the option's label in the request's locale (`optionLabel()`): `HA. Haa Alifu` or `ހއ. ހާ އަލިފު` for atolls, the localized name for everything else. Use [SetLocale-style middleware](https://laravel.com/docs/localization#determining-the-current-locale) on the endpoint if your app switches locales per request.

# Searching as the user types

For a long list (every island of the Maldives), use javaabu/forms' AJAX mode with the endpoint instead of loading everything:

```blade
<x-forms::select2
    name="island_id"
    :options="\Javaabu\Locations\Models\Island::query()"
    :ajax-url="route('location-options.islands')"
    is-ajax />
```

```tsx
<Select2 name="island_id" label="ރަށް" isAjax ajaxUrl="/location-options/islands" />
```

# In code

The filters are also available as a helper:

```php
use Javaabu\Locations\Support\LocationOptions;

LocationOptions::filter('island', Island::query(), ['atoll' => 'HA', 'inhabited' => true])->get();
LocationOptions::url('island'); // https://example.test/location-options/islands, or null when not registered
```
