---
title: Installation & Setup
sidebar_position: 1.2
---

# Installing

Locations and its Javaabu dependencies aren't on Packagist yet. Add their repositories, then require the package:

```json
"repositories": [
    { "type": "vcs", "url": "https://github.com/Javaabu/locations" },
    { "type": "vcs", "url": "https://github.com/Javaabu/stencil" },
    { "type": "vcs", "url": "https://github.com/Javaabu/permissions" },
    { "type": "vcs", "url": "https://github.com/Javaabu/forms" },
    { "type": "vcs", "url": "https://github.com/Javaabu/manifest" }
]
```

```bash
composer require javaabu/locations:^1.0@dev
```

The service provider is discovered automatically.

# Migrating and seeding

```bash
php artisan migrate
php artisan locations:seed
php artisan permissions:sync
```

`locations:seed` adds every country, the atolls and the named islands (see [Seeding the data](./basic-usage/seeding.md)). `permissions:sync` creates the package's permissions (`view_islands`, `edit_islands`, …) for your guards.

# Routes

Nothing is registered until you call the macros, so you choose the prefix, middleware and guard. Put them in your admin route group:

```php
// routes/web.php
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    // The read-only endpoint the cascading selects load their options from
    Route::locationOptions();

    // The admin pages: /admin/locations/countries, /admin/locations/islands, …
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::locations(['guard' => 'web_admin']);
    });
});
```

| Macro | Registers |
| --- | --- |
| `Route::locationOptions()` | `GET location-options/{countries,states,cities,atolls,islands}`, named `location-options.*`. Public by default: wrap it in your own middleware to change that |
| `Route::locations()` | The index, create, show, edit and delete pages of every location type, named `locations.{resource}.*` and behind `auth:{guard}` |

Every cascading select (Blade, React or stencil) needs `Route::locationOptions()`. Both macros take options, see [Admin pages](./basic-usage/admin-pages.md) and [The options endpoint](./advanced-usage/options-endpoint.md).

# Blade

The Blade pages extend `layouts.app` and fill its `content` section. Point them at your layout:

```php
// config/locations.php (php artisan vendor:publish --tag=locations-config)
'layout' => 'layouts.admin',
'layout_section' => 'content',
```

The layout needs manifest's tags (`@manifestStyles` and `@manifestScripts`), or its automatic injection, as every javaabu/forms page does. That loads javaabu/forms' select script, which handles the cascades.

# Inertia React

Render the admin pages with Inertia instead of Blade:

```dotenv
LOCATIONS_VIEWS=inertia
```

Then set up manifest's Vite plugin and page resolver, if your app doesn't already have them:

```js
// vite.config.js
import manifest from "./vendor/javaabu/manifest/dist/vite.js"

export default defineConfig({
  plugins: [laravel({ input: ["resources/js/app.tsx"] }), manifest(), react()],
})
```

```tsx
// resources/js/app.tsx
import { resolvePage } from "@javaabu/manifest/inertia"

createInertiaApp({
  resolve: (name) => resolvePage(name, import.meta.glob("./Pages/**/*.tsx")),
  // …
})
```

The pages are `locations::Resources/Index`, `locations::Resources/Form` and `locations::Resources/Show`. They render the forms with `@javaabu/stencil`'s `StencilForm`. Your own pages import the selects from `@javaabu/locations`:

```tsx
import { IslandSelect } from "@javaabu/locations"
```

`php artisan manifest:check` lists the npm packages your app still needs. Locations' (`@javaabu/forms`, React) are only listed once your `package.json` has `@inertiajs/react`, so a Blade app isn't asked for them; `--scope=pages` checks them anyway.

# Publishing

| Tag | What |
| --- | --- |
| `locations-config` | `config/locations.php` |
| `locations-migrations` | The migration (then call `Locations::ignoreMigrations()`, see [Models](./basic-usage/models.md#migrations)) |
| `locations-translations` | `lang/vendor/locations` |
| `locations-views` | `resources/views/vendor/locations` |

React pages and components are overridden through manifest, see [Overriding controllers and views](./advanced-usage/overriding-controllers-and-views.md).
