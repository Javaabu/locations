---
title: Introduction
sidebar_position: 1.0
---

# Locations

[Locations](https://github.com/Javaabu/locations) gives a Laravel app its countries, states, cities, atolls and islands. The data is seeded from authoritative sources. The admin pages and location selects are ready to use.

- **Every country.** All 249 ISO 3166-1 countries with alpha-2, alpha-3 and numeric codes, English and Dhivehi names, and calling codes.
- **Every Maldivian atoll and island.** 20 administrative atolls and Malé City, with their letter codes, ISO 3166-2:MV codes and official names. All 1,131 named islands of the national island register, with their feature codes, Dhivehi names, categories, coordinates and areas.
- **States and cities** for addresses outside the Maldives. The top-level ISO 3166-2 subdivisions of every country can be seeded.
- **Reviewable data.** Plain JSON files, built by scripts from snapshots of their sources, with every source cited and what couldn't be verified listed for review.
- **Cascading selects** (country → atoll → island, country → state → city) for Blade (javaabu/forms), React (@javaabu/forms) and javaabu/stencil forms, loading from one read-only endpoint.
- **Admin pages** to list, search, add, edit and delete every location type. They come in Blade or Inertia React, with overridable controllers and a policy and permissions per type through javaabu/permissions. They work with several guards.
- **Dhivehi and right to left.** Every string is in English and Dhivehi, Dhivehi names show for Dhivehi locales, and the views use logical directions. Names can be translated into any language with [javaabu/translatable](https://github.com/Javaabu/translatable).

```php
use Javaabu\Locations\Models\Island;

$island = Island::findByCode('LD0568');

$island->name;                  // Dhidhdhoo
$island->dhivehi_name;          // ދިއްދޫ
$island->formattedName('dv');   // ހއ. ދިއްދޫ
$island->atoll->official_name;  // Thiladhunmathi Uthuruburi
$island->category->label();     // Residential

Island::query()->ofAtoll('HA')->inhabited()->ordered()->get();
```

```blade
<x-forms::form :model="$address">
    <x-locations::atoll-select name="atoll_id" child="island_id" />
    <x-locations::island-select name="island_id" parent="atoll_id" />
</x-forms::form>
```

```tsx
import { AtollSelect, IslandSelect } from "@javaabu/locations"

<AtollSelect name="atoll_id" id="atoll_id" label="އަތޮޅު" />
<IslandSelect name="island_id" label="ރަށް" parent="#atoll_id" />
```

```php
use Javaabu\Locations\Stencil\AtollSelect;
use Javaabu\Locations\Stencil\IslandSelect;

AtollSelect::make('atoll_id')->required(),
IslandSelect::make('island_id')->dependsOn('atoll_id')->required(),
```

Start with [Installation & Setup](./installation-and-setup.md).
