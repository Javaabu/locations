<?php

namespace Javaabu\Locations\Policies;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Permissions\Policies\ResourcePolicy;

/**
 * A location resource: view_{resource}, edit_{resource} and delete_{resource}
 *
 * Locations aren't soft deleted, so there's no trash.
 */
abstract class LocationPolicy extends ResourcePolicy
{
    public function restore(Model $user, Model $model): bool
    {
        return false;
    }

    public function forceDelete(Model $user, Model $model): bool
    {
        return false;
    }

    public function viewTrash(Model $user): bool
    {
        return false;
    }
}
