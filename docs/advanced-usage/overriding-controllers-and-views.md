---
title: Overriding controllers and views
sidebar_position: 5
---

# Controllers

Each type has a controller extending `Javaabu\Locations\Http\Controllers\LocationsController`. Extend one and set it in config (or pass `controllers` to `Route::locations()`):

```php
// config/locations.php
'controllers' => [
    'islands' => App\Http\Controllers\Admin\IslandsController::class,
],
```

```php
namespace App\Http\Controllers\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Javaabu\Locations\Http\Controllers\IslandsController as BaseController;

class IslandsController extends BaseController
{
    /** Only the islands of the user's atoll */
    protected function query(Request $request): Builder
    {
        return parent::query($request)->where('atoll_id', $request->user()->atoll_id);
    }

    protected function columns(): array
    {
        return [
            ...parent::columns(),
            'schools_count' => __('Schools'),
        ];
    }

    protected function cells(Model $location): array
    {
        return [
            ...parent::cells($location),
            'schools_count' => $location->schools()->count(),
        ];
    }

    protected function formClass(): string
    {
        return \App\Stencil\IslandForm::class;
    }
}
```

| Method | Override to change |
| --- | --- |
| `query(Request)` | Which locations the pages can reach |
| `columns()`, `cells(Model)` | The index columns and their values |
| `detailValues(Model)` | What the show page lists |
| `filterFields(Request)` | The index filters (stencil fields), besides the search |
| `with()` | Relations to eager load |
| `formClass()` | The stencil form of the create and edit pages |
| `form(Request, ?Model)` | How the form is built (action, method, submit label) |
| `saving(Request, Model, array $data)` | What happens before a location is saved |
| `children()` | The relations that stop a delete and get links on the show page |
| `deletionBlocker(Model)` | Whether a location may be deleted (return a message to refuse) |
| `title(Model)` | The page title of a location |
| `strings()` | The Inertia pages' text |

# Forms

The forms are stencil form classes in `Javaabu\Locations\Forms`. Extend one to add fields:

```php
namespace App\Stencil;

use Javaabu\Locations\Forms\IslandForm as BaseForm;
use Javaabu\Stencil\Components\Section;
use Javaabu\Stencil\Fields\TextInput;

class IslandForm extends BaseForm
{
    protected function definition(): array
    {
        return [
            ...parent::definition(),
            Section::make(__('Council'))->schema([
                TextInput::make('council_name')->label(__('Council name')),
            ]),
        ];
    }
}
```

Add the column to the table and to the model's `$fillable` as well.

# Blade views

Publish them, then edit the copies in `resources/views/vendor/locations`:

```bash
php artisan vendor:publish --tag=locations-views
```

| View | Page |
| --- | --- |
| `resources/index.blade.php` | Index |
| `resources/form.blade.php` | Create and edit |
| `resources/show.blade.php` | Show |
| `resources/partials/actions.blade.php` | The View, Edit and Delete buttons |
| `resources/partials/status.blade.php` | The flashed messages |
| `components/location-select.blade.php` | The location selects |

Each page gets the [Inertia props](../basic-usage/admin-pages.md#the-inertia-pages) as variables (`$rows`, `$columns`, `$urls`, `$can`, …), plus `$locations` (the paginator), `$filters` and `$form` (stencil forms), `$layout` and `$layout_section`.

# React pages and components

They're delivered through javaabu/manifest, so override any one file by copying it to `resources/vendor/locations/` with the same path:

```bash
php artisan manifest:override locations js/Pages/Resources/Index.tsx
```

| File | What |
| --- | --- |
| `js/Pages/Resources/Index.tsx` | `locations::Resources/Index` |
| `js/Pages/Resources/Form.tsx` | `locations::Resources/Form` |
| `js/Pages/Resources/Show.tsx` | `locations::Resources/Show` |
| `js/components/LocationSelect.tsx` | The selects |
| `js/components/LocationActions.tsx` | The View, Edit and Delete buttons |

To render your own page for one type, override the controller's page:

```php
protected function formPage(Request $request, Form $form, ?Model $location): View|Responsable
{
    return Inertia::render('Admin/Islands/Form', [/* … */]);
}
```
