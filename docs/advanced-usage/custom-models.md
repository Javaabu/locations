---
title: Custom models
sidebar_position: 3
---

Extend a package model to add relationships or behaviour, and point the config at yours:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Javaabu\Locations\Models\Island as BaseIsland;

class Island extends BaseIsland
{
    public function schools(): HasMany
    {
        return $this->hasMany(School::class);
    }
}
```

```php
// config/locations.php
'models' => [
    'island' => App\Models\Island::class,
],
```

Everything in the package then uses yours: relationships (`$atoll->islands`), the seeders, the selects, the options endpoint and the admin pages. The package's policies still apply, because Laravel's Gate matches subclasses.

In your own code, ask `Locations` for the class instead of naming one, so it follows the config:

```php
use Javaabu\Locations\Locations;

Locations::islandModel()::query()->ofAtoll('HA')->get();
Locations::model('island');     // the same
Locations::countryModel();
Locations::stateModel();
Locations::cityModel();
Locations::atollModel();
```

# Relating your models to locations

Give your tables plain foreign keys:

```php
Schema::create('addresses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('country_id')->constrained(Locations::table('countries'));
    $table->foreignId('island_id')->nullable()->constrained(Locations::table('islands'));
    $table->string('city')->nullable();
    // …
});
```

```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Javaabu\Locations\Locations;

class Address extends Model
{
    public function island(): BelongsTo
    {
        return $this->belongsTo(Locations::islandModel());
    }
}
```

The package's tables restrict deletes: a country, atoll or state can't be deleted while it has children. Restrict yours too (`constrained()` does by default), so an island an address points at can't disappear.

# Bilingual names on your own models

The models share the `Javaabu\Locations\Models\Concerns\IsLocation` trait. It adds `localizedName()`, the `search()` and `ordered()` scopes, `optionLabel()`, `toOption()`, and the configured table. Keep it when you override a method, and call `parent::` if you need the default.

To change how a type shows in selects, override `optionLabel()`:

```php
class Island extends BaseIsland
{
    public function optionLabel(): string
    {
        return $this->formattedName(); // HA. Dhidhdhoo
    }
}
```
