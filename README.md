# Locations

[![Tests](https://github.com/Javaabu/locations/actions/workflows/tests.yml/badge.svg)](https://github.com/Javaabu/locations/actions/workflows/tests.yml)
[![codecov](https://codecov.io/gh/Javaabu/locations/branch/main/graph/badge.svg)](https://codecov.io/gh/Javaabu/locations)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/javaabu/locations.svg)](https://packagist.org/packages/javaabu/locations)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)

Countries, states, cities, atolls and islands for Laravel:

- **Data:** all 249 ISO 3166-1 countries (codes, English and Dhivehi names, calling codes), every Maldivian atoll (letter and ISO 3166-2 codes, official names) and every island of the national island register (feature codes, Dhivehi names, categories, coordinates). All seeded from cited, authoritative sources.
- **Selects:** cascading selects (country → atoll → island) for Blade ([javaabu/forms](https://github.com/Javaabu/forms)), React ([@javaabu/forms](https://github.com/Javaabu/react-forms)) and [javaabu/stencil](https://github.com/Javaabu/stencil).
- **Admin pages:** in Blade or Inertia React, authorized with [javaabu/permissions](https://github.com/Javaabu/permissions).

```bash
php artisan migrate
php artisan locations:seed
```

```php
use Javaabu\Locations\Models\Island;

$island = Island::findByCode('LD0568');

$island->dhivehi_name;          // ދިއްދޫ
$island->formattedName('dv');   // ހއ. ދިއްދޫ
$island->category->label();     // Residential

Island::query()->ofAtoll('HA')->inhabited()->ordered()->get();
```

```blade
<x-forms::form :model="$address">
    <x-locations::atoll-select name="atoll_id" child="island_id" />
    <x-locations::island-select name="island_id" parent="atoll_id" />
</x-forms::form>
```

```php
Route::locationOptions();                                   // what the cascading selects load from
Route::locations(['guard' => 'web_admin']);                 // admin pages for every type
```

## Documentation

The docs are in [`docs/`](docs/intro.md), and will be published on [docs.javaabu.com](https://docs.javaabu.com). For AI assistants, [`llms.txt`](llms.txt) lists the rules and every page, and the package ships a [Laravel Boost](https://github.com/laravel/boost) guideline and skill.

Where the data comes from, and what still needs a human to review it: [Data sources](docs/advanced-usage/data-sources.md).

## Testing

```bash
composer test
pnpm install && pnpm test
php bin/validate-data.php
```

## Changelog

See [CHANGELOG](CHANGELOG.md).

## Contributing

See [CONTRIBUTING](CONTRIBUTING.md).

## Security

If you discover any security related issues, please email info@javaabu.com instead of using the issue tracker.

## Credits

- [Javaabu Pvt. Ltd.](https://github.com/javaabu)
- [All Contributors](../../contributors)

The island register contains information licensed under Data Usage License – Geomatics Department of Ministry of Infrastructure, Housing and Urban Development. Country data comes from Debian's [iso-codes](https://salsa.debian.org/iso-codes-team/iso-codes) (LGPL-2.1-or-later) and Google's [libphonenumber](https://github.com/google/libphonenumber) (Apache-2.0).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
