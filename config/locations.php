<?php

use Javaabu\Locations\Http\Controllers\AtollsController;
use Javaabu\Locations\Http\Controllers\CitiesController;
use Javaabu\Locations\Http\Controllers\CountriesController;
use Javaabu\Locations\Http\Controllers\IslandsController;
use Javaabu\Locations\Http\Controllers\LocationOptionsController;
use Javaabu\Locations\Http\Controllers\StatesController;

return [

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | The model each location type uses. Extend the package's model and put
    | yours here to add relationships or behaviour. Leave a model null to use
    | the package's own, or its javaabu/translatable variant when translatable
    | is installed and enabled.
    |
    */

    'models' => [
        'country' => null,
        'state' => null,
        'city' => null,
        'atoll' => null,
        'island' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tables
    |--------------------------------------------------------------------------
    |
    | Table names, for apps that already have a `countries` or `islands` table.
    | Set them before running the migrations.
    |
    */

    'tables' => [
        'countries' => 'countries',
        'states' => 'states',
        'cities' => 'cities',
        'atolls' => 'atolls',
        'islands' => 'islands',
    ],

    /*
    |--------------------------------------------------------------------------
    | Dhivehi locales
    |--------------------------------------------------------------------------
    |
    | App locales that show the `dhivehi_name` of a location. Other locales
    | show the English `name`.
    |
    */

    'dhivehi_locales' => ['dv'],

    /*
    |--------------------------------------------------------------------------
    | Seeding
    |--------------------------------------------------------------------------
    |
    | What `LocationsSeeder` (and `php artisan locations:seed`) seeds.
    | `unnamed_islands` includes the features of the island register that have
    | no name yet (sandbanks and very small islands), keyed by their code.
    | `states` seeds the top-level ISO 3166-2 subdivisions of every country
    | except the Maldives, whose subdivisions are the atolls.
    |
    */

    'seed' => [
        'countries' => true,
        'atolls' => true,
        'islands' => true,
        'unnamed_islands' => false,
        'states' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default permissions
    |--------------------------------------------------------------------------
    |
    | Register the package's permissions (view_countries, edit_islands, …)
    | and its policies through javaabu/permissions. Override a policy with
    | `config('permissions.policies')` or `Gate::policy()`.
    |
    */

    'register_permissions' => true,

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | Which stack the admin pages render with: `blade` (javaabu/forms) or
    | `inertia` (@javaabu/forms React pages shipped through javaabu/manifest).
    | Requests that expect JSON always get JSON.
    |
    */

    'views' => env('LOCATIONS_VIEWS', 'blade'),

    /*
    | The Blade layout the pages extend, and the section they fill.
    */

    'layout' => 'layouts.app',

    'layout_section' => 'content',

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Rows per page on the admin pages, and the most the options endpoint
    | returns per page (`per_page=-1` returns every match, up to `max_options`).
    |
    */

    'per_page' => 20,

    'max_options' => 2000,

    /*
    |--------------------------------------------------------------------------
    | Options route
    |--------------------------------------------------------------------------
    |
    | The route name prefix Route::locationOptions() registers the options
    | endpoint under. The location selects find their endpoint by it.
    |
    */

    'options_route' => 'location-options',

    /*
    |--------------------------------------------------------------------------
    | Controllers
    |--------------------------------------------------------------------------
    |
    | The controllers Route::locations() and Route::locationOptions() use.
    | Extend the package's controllers to change their behaviour.
    |
    */

    'controllers' => [
        'countries' => CountriesController::class,
        'states' => StatesController::class,
        'cities' => CitiesController::class,
        'atolls' => AtollsController::class,
        'islands' => IslandsController::class,
        'options' => LocationOptionsController::class,
    ],

];
