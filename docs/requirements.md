---
title: Requirements
sidebar_position: 1.1
---

This package requires:

- PHP 8.4 or higher
- Laravel 12 or 13
- [javaabu/forms](https://github.com/Javaabu/forms) v2, for the Blade pages and selects (installed with locations)
- [javaabu/stencil](https://github.com/Javaabu/stencil), for the admin forms and the stencil location fields (installed with locations)
- [javaabu/permissions](https://github.com/Javaabu/permissions) v3, which registers the package's permissions and policies (installed with locations)
- [javaabu/manifest](https://github.com/Javaabu/manifest), which ships the React components and pages to your app (installed with locations)

For the Inertia React pages and components:

- React 18.3 or 19
- [inertiajs/inertia-laravel](https://github.com/inertiajs/inertia-laravel) 2 or 3, and `@inertiajs/react`
- [@javaabu/forms](https://github.com/Javaabu/react-forms) (`php artisan manifest:check` lists what's missing)

Blade apps need none of these npm packages: the Blade selects use javaabu/forms' own script. The package declares its npm packages for manifest's `pages` scope, so `manifest:check` only asks for them when your `package.json` has `@inertiajs/react`.
- manifest's Vite plugin, which lets your app import `@javaabu/locations` and `@javaabu/stencil`

Optional:

- [javaabu/translatable](https://github.com/Javaabu/translatable) v3, to translate location names into every language your app has (see [Translatable](./advanced-usage/translatable.md))
