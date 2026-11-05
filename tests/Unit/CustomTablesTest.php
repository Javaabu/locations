<?php

namespace Javaabu\Locations\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CustomTablesTest extends TestCase
{
    use RefreshDatabase;

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('locations.tables.atolls', 'mv_atolls');
        $app['config']->set('locations.tables.islands', 'mv_islands');
    }

    #[Test]
    public function it_uses_the_table_names_from_config(): void
    {
        $this->assertTrue(Schema::hasTable('mv_atolls'));
        $this->assertTrue(Schema::hasTable('mv_islands'));
        $this->assertFalse(Schema::hasTable('islands'));

        $island = Island::factory()->for(Atoll::factory())->create();

        $this->assertDatabaseHas('mv_islands', ['id' => $island->id]);
    }
}
