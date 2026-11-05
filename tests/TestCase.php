<?php

namespace Javaabu\Locations\Tests;

use Javaabu\Forms\FormsServiceProvider;
use Javaabu\Locations\LocationsServiceProvider;
use Javaabu\Manifest\ManifestServiceProvider;
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
    }
}
