<?php

namespace Javaabu\Locations;

use Illuminate\Support\ServiceProvider;

class LocationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/locations.php', 'locations');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerPublishing();
        }
    }

    protected function registerPublishing(): void
    {
        $this->publishes([
            __DIR__ . '/../config/locations.php' => config_path('locations.php'),
        ], 'locations-config');
    }
}
