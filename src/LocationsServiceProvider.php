<?php

namespace Javaabu\Locations;

use Illuminate\Support\ServiceProvider;
use Javaabu\Locations\Commands\SeedLocationsCommand;

class LocationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/locations.php', 'locations');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerMigrations();
            $this->registerPublishing();

            $this->commands([
                SeedLocationsCommand::class,
            ]);
        }

        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'locations');
    }

    protected function registerMigrations(): void
    {
        if (Locations::$runs_migrations) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../config/locations.php' => config_path('locations.php'),
        ], 'locations-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'locations-migrations');

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/locations'),
        ], 'locations-translations');
    }
}
