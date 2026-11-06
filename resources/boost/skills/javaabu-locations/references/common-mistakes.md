# Common mistakes with javaabu/locations

| Don't | Do |
| --- | --- |
| Hard-code atolls or islands in an array, an enum or your own seeder | `php artisan locations:seed`, then query `Atoll` / `Island` |
| `Island::find(42)` in seeders, tests or config | `Island::findByCode('LD0442')`: ids depend on seeding order |
| `new Island` / `Island::class` in reusable code | `Locations::islandModel()`, so the app's configured model is used |
| `$island->name_dv` | `$island->dhivehi_name`, or `localizedName('dv')`. `name_dv` is javaabu/translatable's naming, not a column |
| `"{$island->atoll->code}. {$island->name}"` | `$island->formattedName()`: localized, and right for Malé City |
| Query islands of a country | Atolls and islands are Maldivian and have no country. Check `$country->isMaldives()`, then use atolls |
| A cascading select without `Route::locationOptions()` | Register it: the child loads its options from it |
| Installing React or `@javaabu/forms` in a Blade-only app for locations | Not needed: the Blade selects use javaabu/forms' script. `manifest:check` only asks for them when the app uses Inertia |
| Filter a child's options in the view or with `query()` only | `filters([...])` / `:filters` / `filters={...}`: they also apply when the parent changes |
| `child` on the Blade parent without `parent` on the child (or the reverse) | Blade needs both. React needs `parent` on the child. Stencil needs `dependsOn()` on the child |
| Register the admin routes outside your admin group, or without `guard` | `Route::locations(['guard' => 'web_admin'])` inside the group, so URLs, names and permissions match |
| Check `$user->can('edit_islands')` in your own controllers for island records | `$user->can('update', $island)` goes through the policy, which an app can override |
| Forget `php artisan permissions:sync` | Run it after installing and upgrading, or every page answers 403 |
| `locations:seed --update` on production | It overwrites names admins edited. Run plain `locations:seed` to add what's missing |
| Delete an atoll that has islands | The page and the database refuse. Move or delete the islands first |
| Edit `database/data/islands.json` or `countries.json` in the package | They're generated. Fix `sources/` (or `atolls.json`) and run `php bin/build-data.php` |
| English-only tests of user-facing text | Use Thaana: `'dhivehi_name' => 'ދިއްދޫ'`, `app()->setLocale('dv')` |
