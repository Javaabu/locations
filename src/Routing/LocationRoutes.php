<?php

namespace Javaabu\Locations\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use InvalidArgumentException;
use Javaabu\Locations\Locations;

/**
 * Registers the admin pages for `Route::locations()`
 */
class LocationRoutes
{
    /** The route action key that holds the guard the pages authorize against */
    public const string GUARD_ACTION = 'locations_guard';

    /** The route action key that holds the location type a route manages */
    public const string TYPE_ACTION = 'locations_type';

    /**
     * @param  array{
     *     guard?: string|null,
     *     prefix?: string,
     *     name?: string,
     *     middleware?: string|array<int, string>|null,
     *     only?: array<int, string>|null,
     *     except?: array<int, string>,
     *     controllers?: array<string, class-string>
     * }  $options
     */
    public static function register(Router $router, array $options = []): void
    {
        $guard = $options['guard'] ?? static::defaultGuard();
        $prefix = $options['prefix'] ?? 'locations';
        $name = trim($options['name'] ?? 'locations', '.');
        $middleware = $options['middleware'] ?? ["auth:{$guard}"];
        $resources = static::resources($options['only'] ?? null, $options['except'] ?? []);

        $router->group([
            'prefix' => $prefix,
            'as' => $name === '' ? '' : "{$name}.",
            'middleware' => $middleware,
            self::GUARD_ACTION => $guard,
        ], function (Router $router) use ($resources, $options) {
            foreach ($resources as $type => $resource) {
                $controller = $options['controllers'][$resource]
                    ?? config("locations.controllers.{$resource}");

                $router->group([
                    'prefix' => $resource,
                    'as' => "{$resource}.",
                    self::TYPE_ACTION => $type,
                ], function (Router $router) use ($controller, $type) {
                    $router->get('/', [$controller, 'index'])->name('index');
                    $router->get('/create', [$controller, 'create'])->name('create');
                    $router->post('/', [$controller, 'store'])->name('store');
                    $router->get("/{{$type}}", [$controller, 'show'])->name('show');
                    $router->get("/{{$type}}/edit", [$controller, 'edit'])->name('edit');
                    $router->match(['put', 'patch'], "/{{$type}}", [$controller, 'update'])->name('update');
                    $router->delete("/{{$type}}", [$controller, 'destroy'])->name('destroy');
                });
            }
        });
    }

    /**
     * The resources to register, keyed by type
     *
     * @param  array<int, string>|null  $only
     * @param  array<int, string>  $except
     * @return array<string, string>
     */
    public static function resources(?array $only = null, array $except = []): array
    {
        $resources = Locations::RESOURCES;
        $unknown = array_diff([...($only ?? []), ...$except], $resources);

        if ($unknown !== []) {
            throw new InvalidArgumentException('Unknown location resources: ' . implode(', ', $unknown) . '. Use: ' . implode(', ', $resources) . '.');
        }

        return array_filter(
            $resources,
            fn (string $resource) => ($only === null || in_array($resource, $only, true)) && ! in_array($resource, $except, true),
        );
    }

    /**
     * The guard the current route authorizes against
     */
    public static function guard(Request $request): string
    {
        return $request->route()?->getAction(self::GUARD_ACTION) ?? static::defaultGuard();
    }

    /**
     * The location type of the current route, e.g. `island`
     */
    public static function type(Request $request): ?string
    {
        return $request->route()?->getAction(self::TYPE_ACTION);
    }

    /**
     * The route name prefix of the current route's resource, e.g. `admin.locations.islands`
     */
    public static function namePrefix(Request $request): string
    {
        $name = (string) $request->route()?->getName();

        return str_contains($name, '.')
            ? substr($name, 0, strrpos($name, '.'))
            : Locations::resource(static::type($request) ?? 'island');
    }

    /**
     * The route name prefix of another resource registered in the same group, e.g. the atolls
     * of `admin.locations.islands` are `admin.locations.atolls`
     */
    public static function siblingPrefix(Request $request, string $resource): string
    {
        $prefix = static::namePrefix($request);

        if (! str_contains($prefix, '.')) {
            return $resource;
        }

        return substr($prefix, 0, strrpos($prefix, '.')) . ".{$resource}";
    }

    public static function defaultGuard(): string
    {
        return config('auth.defaults.guard', 'web');
    }
}
