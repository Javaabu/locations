---
title: Permissions and policies
sidebar_position: 4
---

The package registers three permissions per location type with [javaabu/permissions](https://github.com/Javaabu/permissions), and a policy per model:

| Type | Permissions | Policy |
| --- | --- | --- |
| Countries | `view_countries`, `edit_countries`, `delete_countries` | `CountryPolicy` |
| States | `view_states`, `edit_states`, `delete_states` | `StatePolicy` |
| Cities | `view_cities`, `edit_cities`, `delete_cities` | `CityPolicy` |
| Atolls | `view_atolls`, `edit_atolls`, `delete_atolls` | `AtollPolicy` |
| Islands | `view_islands`, `edit_islands`, `delete_islands` | `IslandPolicy` |

The policies are in `Javaabu\Locations\Policies` and extend javaabu/permissions' `ResourcePolicy`:

| Ability | Needs |
| --- | --- |
| `viewAny`, `view` | `view_{resource}` |
| `create`, `update` | `edit_{resource}` |
| `delete` | `delete_{resource}` |
| `restore`, `forceDelete`, `viewTrash` | never: locations aren't soft deleted |

The descriptions ("View islands", "ރަށްތައް ބެލުން") are translated in `lang/dv.json`. Run `php artisan permissions:sync` after installing and after upgrading, to create them for every guard in `config('permissions.guards')`.

# Several guards

The pages authorize the user of the routes' guard (`Route::locations(['guard' => 'web_admin'])`), with that guard's permissions. Nothing assumes the `web` guard or `App\Models\User`: any authenticatable model with javaabu/permissions' `HasRoles` works.

# Overriding a policy

In config:

```php
// config/permissions.php
'policies' => [
    Javaabu\Locations\Models\Island::class => App\Policies\IslandPolicy::class,
],
```

Or with the Gate, in any service provider:

```php
use Illuminate\Support\Facades\Gate;
use Javaabu\Locations\Models\Island;

Gate::policy(Island::class, App\Policies\IslandPolicy::class);
```

Register policies for the package's model classes even when you use your own models: the Gate matches subclasses. For example, a policy that lets council staff edit only their own atoll's islands:

```php
namespace App\Policies;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Locations\Policies\IslandPolicy as BasePolicy;

class IslandPolicy extends BasePolicy
{
    public function update(Model $user, Model $island): bool
    {
        return parent::update($user, $island) && $user->atoll_id === $island->atoll_id;
    }
}
```

# Not registering them

Set `register_permissions` to `false` to register neither the permissions nor the policies. Then register your own policies for the models, or every page answers 403.
