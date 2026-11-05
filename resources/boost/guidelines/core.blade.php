{{--
    javaabu/locations guidelines for AI code assistants

    Maintained by: Javaabu (https://javaabu.com)
--}}

@verbatim
## javaabu/locations

- Countries, states, cities, Maldivian atolls and islands come from `javaabu/locations`: seed them with `php artisan locations:seed` and use `Javaabu\Locations\Models\{Country, State, City, Atoll, Island}`. Never hard-code atoll or island lists, and never write your own location seeders.
- Resolve models with `Locations::islandModel()` in reusable code, and look locations up by code (`Island::findByCode('LD0442')`, `Atoll::findByCode('HA')`, `Country::findByCode('MV')`), never by id.
- Names are `name` (English) and `dhivehi_name` (Thaana); show them with `localizedName()`, and islands in addresses with `formattedName()` (`HA. Dhidhdhoo`).
- For address forms use the cascading selects: `<x-locations::atoll-select child="island_id" />` + `<x-locations::island-select parent="atoll_id" />` in Blade, `<IslandSelect parent="#atoll_id" />` from `@javaabu/locations` in React, `IslandSelect::make('island_id')->dependsOn('atoll_id')` in stencil. They need `Route::locationOptions()`.
- Admin pages are `Route::locations(['guard' => 'web_admin'])` inside the admin group, authorized with `view_islands`, `edit_islands`, `delete_islands` (and the same for the other types) through javaabu/permissions.
- Always activate the `javaabu-locations` skill when working on addresses, countries, atolls, islands, location selects, the location admin pages or the location seed data.
@endverbatim
