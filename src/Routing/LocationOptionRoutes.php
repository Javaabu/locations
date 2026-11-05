<?php

namespace Javaabu\Locations\Routing;

use Illuminate\Routing\Router;
use Javaabu\Locations\Locations;

/**
 * Registers the read-only JSON endpoint the location selects load their options from,
 * for `Route::locationOptions()`
 */
class LocationOptionRoutes
{
    /**
     * @param  array{
     *     prefix?: string,
     *     name?: string,
     *     middleware?: string|array<int, string>,
     *     controller?: class-string|null
     * }  $options
     */
    public static function register(Router $router, array $options = []): void
    {
        $prefix = $options['prefix'] ?? 'location-options';
        $name = trim($options['name'] ?? config('locations.options_route', 'location-options'), '.');
        $controller = $options['controller'] ?? config('locations.controllers.options');

        $router->group([
            'prefix' => $prefix,
            'as' => "{$name}.",
            'middleware' => $options['middleware'] ?? [],
        ], function (Router $router) use ($controller) {
            foreach (Locations::RESOURCES as $type => $resource) {
                $router->get("/{$resource}", [$controller, 'index'])
                    ->name($resource)
                    ->defaults('type', $type);
            }
        });
    }
}
