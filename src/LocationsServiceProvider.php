<?php

namespace Javaabu\Locations;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Javaabu\Locations\Commands\SeedLocationsCommand;
use Javaabu\Locations\Policies\AtollPolicy;
use Javaabu\Locations\Policies\CityPolicy;
use Javaabu\Locations\Policies\CountryPolicy;
use Javaabu\Locations\Policies\IslandPolicy;
use Javaabu\Locations\Policies\StatePolicy;
use Javaabu\Locations\Routing\LocationOptionRoutes;
use Javaabu\Locations\Routing\LocationRoutes;
use Javaabu\Manifest\Concerns\RegistersAssets;
use Javaabu\Manifest\Package;
use Javaabu\Permissions\Permissions;

class LocationsServiceProvider extends ServiceProvider
{
    use RegistersAssets;

    /** The policy of each location type */
    public const array POLICIES = [
        'country' => CountryPolicy::class,
        'state' => StatePolicy::class,
        'city' => CityPolicy::class,
        'atoll' => AtollPolicy::class,
        'island' => IslandPolicy::class,
    ];

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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'locations');

        $this->registerRouteMacros();
        $this->registerPermissions();
        $this->registerFrontendAssets();
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

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/locations'),
        ], 'locations-views');
    }

    protected function registerRouteMacros(): void
    {
        Route::macro('locations', function (array $options = []) {
            LocationRoutes::register($this, $options);
        });

        Route::macro('locationOptions', function (array $options = []) {
            LocationOptionRoutes::register($this, $options);
        });
    }

    /**
     * view_/edit_/delete_ permissions for each location type, and their policies
     */
    protected function registerPermissions(): void
    {
        if (! config('locations.register_permissions', true)) {
            return;
        }

        foreach (Locations::MODELS as $type => $model) {
            $resource = Locations::resource($type);

            Permissions::resource($resource, $resource, ['view', 'edit', 'delete'], label: ucfirst($resource));
            Permissions::policy($model, self::POLICIES[$type]);
        }
    }

    /**
     * The Inertia pages (locations::Resources/Index, …) and the components apps import as
     * `@javaabu/locations`
     */
    protected function registerFrontendAssets(): void
    {
        $this->registerAssets('locations', __DIR__ . '/../resources', function (Package $package) {
            $package
                ->js()
                ->pages()
                ->npm([
                    '@javaabu/forms' => 'github:Javaabu/react-forms#next-gen',
                ], peer_dependencies: [
                    'react' => '^18.3 || ^19.0',
                    'react-dom' => '^18.3 || ^19.0',
                    '@inertiajs/react' => '^2.0 || ^3.0',
                ]);
        });
    }
}
