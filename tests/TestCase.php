<?php

namespace Javaabu\Locations\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Javaabu\Forms\FormsServiceProvider;
use Javaabu\Locations\LocationsServiceProvider;
use Javaabu\Locations\Tests\Fixtures\Models\Admin;
use Javaabu\Locations\Tests\Fixtures\Models\User;
use Javaabu\Manifest\ManifestServiceProvider;
use Javaabu\Permissions\Permissions;
use Javaabu\Permissions\PermissionsServiceProvider;
use Javaabu\Stencil\StencilServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Spatie\Permission\PermissionServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ManifestServiceProvider::class,
            FormsServiceProvider::class,
            StencilServiceProvider::class,
            PermissionServiceProvider::class,
            PermissionsServiceProvider::class,
            LocationsServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('view.paths', [__DIR__ . '/views', ...$app['config']->get('view.paths', [])]);

        $app['config']->set('auth.guards', [
            'web' => ['driver' => 'session', 'provider' => 'users'],
            'web_admin' => ['driver' => 'session', 'provider' => 'admins'],
        ]);

        $app['config']->set('auth.providers', [
            'users' => ['driver' => 'eloquent', 'model' => User::class],
            'admins' => ['driver' => 'eloquent', 'model' => Admin::class],
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        foreach (['users', 'admins'] as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }
    }

    /**
     * A user of a guard with a role that has the given permissions
     *
     * @param  array<int, string>  $permissions
     */
    protected function userWith(array $permissions, string $guard = 'web'): User|Admin
    {
        config(['permissions.guards' => ['web', 'web_admin']]);

        Permissions::sync();

        $model = $guard === 'web_admin' ? Admin::class : User::class;
        $user = $model::query()->create(['name' => 'އަޙްމަދު', 'email' => uniqid() . '@example.com']);

        if ($permissions === []) {
            return $user;
        }

        $role_class = config('permission.models.role');
        $role = $role_class::findOrCreate('role_' . uniqid(), $guard);
        $role->givePermissionTo($permissions);

        $user->assignRole($role);

        return $user;
    }

    /**
     * A user who may do everything with locations
     */
    protected function locationsAdmin(string $guard = 'web'): User|Admin
    {
        $permissions = [];

        foreach (['countries', 'states', 'cities', 'atolls', 'islands'] as $resource) {
            array_push($permissions, "view_{$resource}", "edit_{$resource}", "delete_{$resource}");
        }

        return $this->userWith($permissions, $guard);
    }
}
