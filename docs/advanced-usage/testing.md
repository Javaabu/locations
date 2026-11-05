---
title: Testing
sidebar_position: 9
---

# Locations in your tests

Create only what a test needs with the factories:

```php
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Country;
use Javaabu\Locations\Models\Island;

$maldives = Country::factory()->maldives()->create();
$atoll = Atoll::factory()->haaAlifu()->create();
$island = Island::factory()->for($atoll)->create(['name' => 'Dhidhdhoo', 'dhivehi_name' => 'ދިއްދޫ']);
$resort = Island::factory()->for($atoll)->category(IslandCategories::TOURISM)->create();
```

Or seed the real data. It takes under a second on SQLite:

```php
use Javaabu\Locations\Database\Seeders\LocationsSeeder;

$this->seed(LocationsSeeder::class);

$island = Island::findByCode('LD0442'); // Malé
```

Look locations up by their codes, never by ids: ids depend on the seeding order.

# Forms with location fields

```php
#[Test]
public function it_saves_the_address_island(): void
{
    $island = Island::factory()->for(Atoll::factory()->haaAlifu())->create(['name' => 'Dhidhdhoo']);

    $this->actingAs($user)
        ->post('/addresses', ['atoll_id' => $island->atoll_id, 'island_id' => $island->id])
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('addresses', ['island_id' => $island->id]);
}
```

The stencil location fields validate that the location exists, so an unknown id fails with an error on the field.

# The admin pages

Give the user the permissions of the routes' guard, then request the pages:

```php
use Javaabu\Permissions\Permissions;

Permissions::sync();

$role = Role::findOrCreate('editor', 'web_admin');
$role->givePermissionTo(['view_islands', 'edit_islands']);
$admin->assignRole($role);

$this->actingAs($admin, 'web_admin')
    ->get(route('admin.locations.islands.index', ['atoll' => 'HA']))
    ->assertOk()
    ->assertSee('ދިއްދޫ');
```

For the Inertia pages, set `locations.views` to `inertia` and assert on the props:

```php
config(['locations.views' => 'inertia', 'inertia.testing.ensure_pages_exist' => false]);

$this->actingAs($admin, 'web_admin')
    ->get(route('admin.locations.islands.index'))
    ->assertInertia(fn (AssertableInertia $page) => $page
        ->component('locations::Resources/Index')
        ->has('rows', 2));
```

`ensure_pages_exist` is off because the pages are package files that javaabu/manifest resolves, not files in your `resources/js/Pages`.

# The options endpoint

```php
$this->getJson(route('location-options.islands', ['filter' => ['atoll' => 'HA'], 'per_page' => -1]))
    ->assertJsonPath('data.0.name', 'Dhidhdhoo');
```
