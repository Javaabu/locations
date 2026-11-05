<?php

namespace Javaabu\Locations\Tests\Unit;

use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ServiceProviderTest extends TestCase
{
    #[Test]
    public function it_merges_the_default_config(): void
    {
        $this->assertEquals('blade', config('locations.views'));
        $this->assertEquals('islands', config('locations.tables.islands'));
        $this->assertEquals(['dv'], config('locations.dhivehi_locales'));
    }
}
