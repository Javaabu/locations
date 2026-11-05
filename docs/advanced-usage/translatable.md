---
title: Translatable
sidebar_position: 7
---

Every location has an English `name` and a `dhivehi_name`, which is all most Maldivian apps need. With [javaabu/translatable](https://github.com/Javaabu/translatable) v3 installed and enabled, names can be translated into every language your app has. The package doesn't require it.

```bash
composer require javaabu/translatable:^3.0@dev
```

# What changes

Once translatable is enabled (`translatable.enabled` is on and there's at least one language):

- **The models** are the translatable variants in `Javaabu\Locations\Models\Translatable`, unless you configured your own. They use translatable's JSON storage for `name`, in the `translations` and `lang` columns every location table has.
- **Seeding** writes the English name as the primary one (`lang` is `en`) and the Dhivehi name as the translation of each active Dhivehi locale.
- **The admin forms** have one name input per language (`name_en`, `name_dv`), with Thaana and right to left for Dhivehi, instead of the Dhivehi name field. Saving keeps `dhivehi_name` in step with the Dhivehi translation, so code that reads the column still works.
- **Reading names** works as with any translatable model:

```php
app()->setLocale('dv');

$island->name;                          // ދިއްދޫ (the current locale)
$island->translate('name', 'en');       // Dhidhdhoo
$island->localizedName('dv');           // the translation, or dhivehi_name
$island->localizedName();               // the current locale's translation, falling back to the name
```

Morph types and policies keep pointing at the base models, so `Island::class` in `config('permissions.policies')` or `Gate::policy()` still applies.

# Your own model

To make your own model translatable, add the trait to it yourself. The package only swaps models you haven't configured:

```php
namespace App\Models;

use Javaabu\Locations\Models\Island as BaseIsland;
use Javaabu\Locations\Models\Translatable\Concerns\TranslatesName;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

class Island extends BaseIsland implements JsonTranslatable
{
    use IsJsonTranslatable;
    use TranslatesName;

    protected array $translatable = ['name'];
}
```

`TranslatesName` makes `localizedName()` prefer the translation and keeps the morph class on the base model.

# Without it

Nothing references translatable's classes unless it's installed. The `translations` and `lang` columns stay empty, and the plain models are used. CI runs the suite without it.

# Checking in code

```php
use Javaabu\Locations\Support\Translations;

Translations::enabled();                        // installed, on, with languages
Translations::supports(Island::class, 'name');  // the configured model translates the field
```
