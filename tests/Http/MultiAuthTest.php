<?php

namespace Javaabu\Locations\Tests\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Javaabu\Locations\Models\Atoll;
use Javaabu\Locations\Models\Island;
use Javaabu\Locations\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class MultiAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function defineRoutes($router): void
    {
        Route::middleware('web')->group(function () {
            Route::locations();

            Route::prefix('admin')->name('admin.')->group(function () {
                Route::locations(['guard' => 'web_admin', 'only' => ['atolls', 'islands']]);
            });
        });
    }

    #[Test]
    public function it_authorizes_admins_on_their_guard(): void
    {
        $island = Island::factory()->for(Atoll::factory())->create(['name' => 'Fenfushi']);

        $this->actingAs($this->locationsAdmin('web_admin'), 'web_admin')
            ->get('/admin/locations/islands')
            ->assertOk()
            ->assertSee('Fenfushi')
            ->assertSee("/admin/locations/islands/{$island->id}", false);
    }

    #[Test]
    public function it_doesnt_let_users_into_the_admin_guards_pages(): void
    {
        Route::get('/login', fn () => 'login')->name('login');

        $this->actingAs($this->locationsAdmin('web'), 'web')
            ->get('/admin/locations/islands')
            ->assertRedirect('/login');
    }

    #[Test]
    public function it_doesnt_let_admins_into_the_user_guards_pages(): void
    {
        Route::get('/login', fn () => 'login')->name('login');

        $this->actingAs($this->locationsAdmin('web_admin'), 'web_admin')
            ->get('/locations/islands')
            ->assertRedirect('/login');
    }

    #[Test]
    public function it_uses_the_permissions_of_the_routes_guard(): void
    {
        $this->actingAs($this->userWith(['view_islands'], 'web_admin'), 'web_admin')
            ->get('/admin/locations/islands')
            ->assertOk();

        $this->actingAs($this->userWith(['view_atolls'], 'web_admin'), 'web_admin')
            ->get('/admin/locations/islands')
            ->assertForbidden();
    }

    #[Test]
    public function it_registers_only_the_resources_asked_for(): void
    {
        $this->assertTrue(Route::has('admin.locations.islands.index'));
        $this->assertTrue(Route::has('admin.locations.atolls.edit'));
        $this->assertFalse(Route::has('admin.locations.countries.index'));
        $this->assertTrue(Route::has('locations.countries.index'));
    }

    #[Test]
    public function it_redirects_within_the_routes_prefix(): void
    {
        $atoll = Atoll::factory()->create();

        $this->actingAs($this->locationsAdmin('web_admin'), 'web_admin')
            ->post('/admin/locations/islands', ['atoll_id' => $atoll->id, 'code' => 'LD9998', 'name' => 'Huraa'])
            ->assertRedirect('/admin/locations/islands/' . Island::query()->value('id'));
    }
}
