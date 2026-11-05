<?php

namespace Javaabu\Locations\Tests\Fixtures\Policies;

class DenyAllPolicy
{
    public function before(): bool
    {
        return false;
    }
}
