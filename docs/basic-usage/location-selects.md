---
title: Location selects
sidebar_position: 3
---

Searchable selects of countries, states, cities, atolls and islands, for Blade (javaabu/forms), React (@javaabu/forms) and javaabu/stencil. Each one lists every location of its type, or, with a parent, only the locations of the parent's value. Choosing a parent reloads its child: country → atoll → island, or country → state → city.

Cascades load the child's options from the [options endpoint](../advanced-usage/options-endpoint.md), so register `Route::locationOptions()` first (see [Installation](../installation-and-setup.md#routes)). Options are labelled in the current locale (`HA. Haa Alifu` / `ހއ. ހާ އަލިފު`).

# Atoll and island

**Blade**

```blade
<x-forms::form :model="$address">
    <x-locations::atoll-select name="atoll_id" child="island_id" required />
    <x-locations::island-select name="island_id" parent="atoll_id" required />
</x-forms::form>
```

**React**

```tsx
import { AtollSelect, IslandSelect } from "@javaabu/locations"

<Form model={address} action="/addresses">
  <AtollSelect name="atoll_id" id="atoll_id" label="އަތޮޅު" options={atolls} required />
  <IslandSelect name="island_id" label="ރަށް" parent="#atoll_id" options={islands} required />
</Form>
```

**Stencil**

```php
use Javaabu\Locations\Stencil\AtollSelect;
use Javaabu\Locations\Stencil\IslandSelect;

protected function definition(): array
{
    return [
        AtollSelect::make('atoll_id')->required(),
        IslandSelect::make('island_id')->dependsOn('atoll_id')->required(),
    ];
}
```

The parent and the child find each other:

- **Blade:** the parent names its `child` and the child names its `parent`, as field names (`island_id`) or selectors (`#island_id`).
- **React:** the child names its `parent`, as a selector or a name.
- **Stencil:** the child calls `dependsOn()`. The parent finds its child in the form by itself.

The child lists the islands of the parent's current value: old input, then the form's bound model (Blade and stencil), then `parent-value`. With no parent value it's empty, and javaabu/forms disables it until one is chosen.

# Country, atoll and island

Atolls belong to the Maldives. An atoll select whose parent is a country lists every atoll when the Maldives is chosen, and none for any other country. Use javaabu/forms' `fallback` to show a text field instead:

```blade
<x-forms::form :model="$address">
    <x-locations::country-select child="atoll_id" required />
    <x-locations::atoll-select parent="country_id" child="island_id" fallback="#city" />
    <x-locations::island-select parent="atoll_id" />
    <x-forms::text name="city" id="city" :label="__('City')" />
</x-forms::form>
```

```tsx
<CountrySelect name="country_id" id="country_id" label="ޤައުމު" required />
<AtollSelect name="atoll_id" id="atoll_id" label="އަތޮޅު" parent="#country_id" fallback="#city" />
<IslandSelect name="island_id" label="ރަށް" parent="#atoll_id" />
<Text name="city" id="city" label="ސިޓީ" />
```

```php
CountrySelect::make('country_id')->required(),
AtollSelect::make('atoll_id')->dependsOn('country_id'),
IslandSelect::make('island_id')->dependsOn('atoll_id'),
```

# Country, state and city

```blade
<x-locations::country-select child="state_id" />
<x-locations::state-select parent="country_id" child="city_id" />
<x-locations::city-select parent="state_id" />
```

```tsx
<CountrySelect name="country_id" id="country_id" label="ޤައުމު" />
<StateSelect name="state_id" id="state_id" label="ސްޓޭޓް" parent="#country_id" />
<CitySelect name="city_id" label="ސިޓީ" parent="#state_id" />
```

```php
CountrySelect::make('country_id'),
StateSelect::make('state_id')->dependsOn('country_id'),
CitySelect::make('city_id')->dependsOn('state_id'),
```

A city select can depend on a country instead: `dependsOn('country_id')` (stencil) sends `filter[country]`. In Blade and React, add `filter-field="country"` / `filterField="country"`.

# Narrowing the options

Only inhabited islands, or only resorts:

```blade
<x-locations::island-select parent="atoll_id" :filters="['category' => 'residential']" />
<x-locations::island-select :filters="['category' => 'tourism']" />
```

```tsx
<IslandSelect name="island_id" label="ރަށް" parent="#atoll_id" filters={{ inhabited: true }} />
```

```php
IslandSelect::make('island_id')->dependsOn('atoll_id')->filters(['inhabited' => true]),
```

The filters are the [options endpoint's](../advanced-usage/options-endpoint.md#filters): `search`, `country`, `state`, `atoll`, `category`, `inhabited` and `named`. They apply to the first options and to every reload. Stencil's `query()` changes only the server-side query of the first options, for what the endpoint can't filter. Or give any select your own options:

```blade
<x-locations::atoll-select :options="$atolls" />
```

# Blade

| Component | Default `name` |
| --- | --- |
| `<x-locations::country-select>` | `country_id` |
| `<x-locations::state-select>` | `state_id` |
| `<x-locations::city-select>` | `city_id` |
| `<x-locations::atoll-select>` | `atoll_id` |
| `<x-locations::island-select>` | `island_id` |

| Attribute | Type | Default | Description |
| --- | --- | --- | --- |
| `name` | `string` | as above | The field name |
| `label` | `string` | the type, translated (`ރަށް`) | The label |
| `parent` | `string` | | The field this one depends on: a name or a selector |
| `parent-value` | `mixed` | | The parent's value, when it isn't old input or on the bound model |
| `child` | `string` | | The field that depends on this one: a name or a selector |
| `filter-field` | `string` | by type | The filter the parent's value is sent as: `atoll` for islands, `state` for cities, `country` otherwise |
| `filters` | `array` | `[]` | More filters for the options |
| `options` | `array` | the package's | `[value => label]`, instead of loading the locations |

Every other attribute (`required`, `id`, `placeholder`, `fallback`, `multiple`, `:clearable`, …) goes to `<x-forms::select2>`, so model binding, old input and errors work as for any javaabu/forms select.

# React

| Component | Lists |
| --- | --- |
| `CountrySelect` | Countries |
| `StateSelect` | States |
| `CitySelect` | Cities |
| `AtollSelect` | Atolls and Malé City |
| `IslandSelect` | Islands |
| `LocationSelect` | `type`'s locations |

They take every prop of @javaabu/forms' `Select2`, and:

| Prop | Type | Default | Description |
| --- | --- | --- | --- |
| `options` | `OptionsInput` | loaded | The options. Without them, a select with no `parent` loads every location of its type once. A child shows them until its first request |
| `parent` | `string` | | The field this one depends on: `#id` or a name |
| `filterField` | `string` | by type | The filter the parent's value is sent as |
| `filters` | `Record<string, string \| number \| boolean>` | `{}` | More filters for the options |
| `endpoint` | `string` | `/location-options` | Where `Route::locationOptions()` is mounted |

Set the endpoint once for the whole app:

```tsx
import { LocationsProvider } from "@javaabu/locations"

<LocationsProvider endpoint="/api/location-options">
  <App />
</LocationsProvider>
```

# Stencil

`CountrySelect`, `StateSelect`, `CitySelect`, `AtollSelect` and `IslandSelect` in `Javaabu\Locations\Stencil` are stencil `Select` fields, so every `Select` method works (`required()`, `label()`, `multiple()`, `visibleWhen()`, …). They are searchable by default, labelled with their type, and validate that the chosen location exists.

| Method | Description |
| --- | --- |
| `dependsOn(string $field, ?string $filter = null)` | List the locations of another field's value, and reload when it changes |
| `filters(array $filters)` | The options endpoint's filters, for the options and every reload: `['category' => 'residential']` |
| `query(Closure $callback)` | Change the query of the first options: `fn (Builder $query) => $query->where(…)` |
| `options(...)` | Your own options instead of the package's |
| `optionsQuery()` | The query the options come from, after `dependsOn()`, `filters()` and `query()` |

They render through both stencil drivers, Blade and JSON, as `select2` / `Select2` with the cascade's props, so nothing needs registering in `StencilProvider`.
