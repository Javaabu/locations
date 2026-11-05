<?php

namespace Javaabu\Locations\Tests\Fixtures\Controllers;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Locations\Http\Controllers\IslandsController;
use Javaabu\Locations\Models\Island;

class CustomIslandsController extends IslandsController
{
    protected function columns(): array
    {
        return ['name' => 'Island name'];
    }

    /**
     * @param  Island  $location
     */
    protected function cells(Model $location): array
    {
        return ['name' => strtoupper((string) $location->name)];
    }
}
