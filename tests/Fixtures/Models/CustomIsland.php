<?php

namespace Javaabu\Locations\Tests\Fixtures\Models;

use Javaabu\Locations\Models\Island;

class CustomIsland extends Island
{
    public function shout(): string
    {
        return strtoupper((string) $this->name);
    }
}
