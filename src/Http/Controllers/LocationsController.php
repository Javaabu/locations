<?php

namespace Javaabu\Locations\Http\Controllers;

use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Javaabu\Locations\Forms\LocationForm;
use Javaabu\Locations\Http\Resources\LocationResource;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Routing\LocationRoutes;
use Javaabu\Locations\Support\LocationOptions;
use Javaabu\Locations\Support\Translations;
use Javaabu\Locations\Views\ViewRenderer;
use Javaabu\Stencil\Fields\Field;
use Javaabu\Stencil\Fields\TextInput;
use Javaabu\Stencil\Form;

/**
 * The admin pages of one location type: index, create, show, edit and delete
 *
 * Every action answers JSON when the request expects it, and a Blade or Inertia page
 * otherwise. Extend a type's controller and set it in `config('locations.controllers')`
 * (or the `controllers` option of `Route::locations()`) to change it.
 */
abstract class LocationsController extends Controller
{
    /** The location type: country, state, city, atoll or island */
    protected string $type;

    public function __construct(
        protected ViewRenderer $views,
    ) {}

    /**
     * The stencil form of the create and edit pages
     *
     * @return class-string<LocationForm>
     */
    abstract protected function formClass(): string;

    /**
     * The index columns: key => translated label
     *
     * @return array<string, string>
     */
    abstract protected function columns(): array;

    /**
     * The value of each index column for a location
     *
     * @return array<string, string|int|float|null>
     */
    abstract protected function cells(Model $location): array;

    /**
     * The filters of the index page, besides the search
     *
     * @return array<int, Field>
     */
    protected function filterFields(Request $request): array
    {
        return [];
    }

    /**
     * Relations the index and show pages load
     *
     * @return array<int|string, mixed>
     */
    protected function with(): array
    {
        return [];
    }

    /**
     * Child locations that stop a location from being deleted: relation => resource
     *
     * @return array<string, string>
     */
    protected function children(): array
    {
        return [];
    }

    public function index(Request $request): View|Responsable|JsonResponse
    {
        $this->authorizeAbility($request, 'viewAny', $this->modelClass());

        $filters = $this->filters($request);
        $per_page = min(max($request->integer('per_page', (int) config('locations.per_page', 20)), 1), 100);

        $locations = LocationOptions::filter($this->type, $this->query($request)->with($this->with()), $filters)
            ->ordered()
            ->paginate($per_page)
            ->withQueryString();

        if ($request->expectsJson()) {
            $data = $locations->getCollection()
                ->map(fn (Model $location) => $this->resource($request, $location)->resolve($request))
                ->all();

            return new JsonResponse([
                ...LocationResource::collection($locations)->response()->getData(true),
                'data' => $data,
            ]);
        }

        $rows = $locations->getCollection()->map(fn (Model $location) => $this->row($request, $location))->all();

        return $this->views->render('resources.index', [
            ...$this->meta($request),
            'title' => $this->label('plural'),
            'columns' => collect($this->columns())->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'dir' => $this->columnDirection($key),
            ])->values()->all(),
            'rows' => $rows,
            'locations' => $this->views->isInertia() ? $locations->toArray() : $locations,
            'filters' => $this->filterForm($request, $filters),
            'filtered' => array_filter($filters, fn ($value) => filled($value)) !== [],
        ]);
    }

    public function create(Request $request): View|Responsable|JsonResponse
    {
        $this->authorizeAbility($request, 'create', $this->modelClass());

        $form = $this->form($request, null);

        if ($request->expectsJson()) {
            return new JsonResponse(['data' => ['form' => $form->render('json')]]);
        }

        return $this->formPage($request, $form, null);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorizeAbility($request, 'create', $this->modelClass());

        $model_class = $this->modelClass();
        $location = $this->save($request, $this->form($request, null), new $model_class);

        if ($request->expectsJson()) {
            return $this->resource($request, $location)->response()->setStatusCode(201);
        }

        return redirect()
            ->to($this->url($request, 'show', $location))
            ->with('success', __('locations::strings.messages.created', ['type' => $this->label('singular')]));
    }

    public function show(Request $request, string $key): View|Responsable|JsonResponse
    {
        $location = $this->find($request, $key);

        $this->authorizeAbility($request, 'view', $location);

        $location->loadMissing($this->with());

        if ($request->expectsJson()) {
            return $this->resource($request, $location)->response();
        }

        return $this->views->render('resources.show', [
            ...$this->meta($request, $location),
            'title' => $this->title($location),
            'location' => $this->resource($request, $location)->resolve($request),
            'details' => $this->details($location),
            'related' => $this->related($request, $location),
        ]);
    }

    public function edit(Request $request, string $key): View|Responsable|JsonResponse
    {
        $location = $this->find($request, $key);

        $this->authorizeAbility($request, 'update', $location);

        $form = $this->form($request, $location);

        if ($request->expectsJson()) {
            return new JsonResponse(['data' => [
                'location' => $this->resource($request, $location)->resolve($request),
                'form' => $form->render('json'),
            ]]);
        }

        return $this->formPage($request, $form, $location);
    }

    public function update(Request $request, string $key): RedirectResponse|JsonResponse
    {
        $location = $this->find($request, $key);

        $this->authorizeAbility($request, 'update', $location);

        $location = $this->save($request, $this->form($request, $location), $location);

        if ($request->expectsJson()) {
            return $this->resource($request, $location)->response();
        }

        return redirect()
            ->to($this->url($request, 'show', $location))
            ->with('success', __('locations::strings.messages.updated', ['type' => $this->label('singular')]));
    }

    public function destroy(Request $request, string $key): RedirectResponse|JsonResponse
    {
        $location = $this->find($request, $key);

        $this->authorizeAbility($request, 'delete', $location);

        $blocker = $this->deletionBlocker($location);

        if ($blocker !== null) {
            if ($request->expectsJson()) {
                return new JsonResponse(['message' => $blocker], 409);
            }

            return redirect()->to($this->url($request, 'show', $location))->with('error', $blocker);
        }

        $location->delete();

        if ($request->expectsJson()) {
            return new JsonResponse(null, 204);
        }

        return redirect()
            ->to($this->url($request, 'index'))
            ->with('success', __('locations::strings.messages.deleted', ['type' => $this->label('singular')]));
    }

    /**
     * The create or edit page
     */
    protected function formPage(Request $request, Form $form, ?Model $location): View|Responsable
    {
        return $this->views->render('resources.form', [
            ...$this->meta($request, $location),
            'title' => $location
                ? $this->title($location)
                : __('locations::strings.actions.new', ['type' => $this->label('singular')]),
            'form' => $this->views->isInertia() ? $form->render('json') : $form,
            'location' => $location ? $this->resource($request, $location)->resolve($request) : null,
            'translations' => $location ? Translations::form($location) : Translations::form($this->modelClass()),
        ]);
    }

    /**
     * The form for a new location (null) or an existing one
     */
    protected function form(Request $request, ?Model $location): Form
    {
        $form_class = $this->formClass();

        return $form_class::make()
            ->model($location ?? $this->modelClass())
            ->action($location ? $this->url($request, 'update', $location) : $this->url($request, 'store'))
            ->method($location ? 'PATCH' : 'POST')
            ->submit($location
                ? __('locations::strings.actions.save')
                : __('locations::strings.actions.create', ['type' => $this->label('singular')]))
            ->summary();
    }

    /**
     * Validate the request against the form and save the location
     */
    protected function save(Request $request, Form $form, Model $location): Model
    {
        $data = $form->validate($request);

        $form->fillModel($location, $data);

        $this->saving($request, $location, $data);

        $location->save();

        $form->saveRelationships($location, $data);

        return $location->refresh();
    }

    /**
     * Called before a location is saved, with the validated data
     *
     * With javaabu/translatable, the Dhivehi name follows the Dhivehi translation of the name.
     *
     * @param  array<string, mixed>  $data
     */
    protected function saving(Request $request, Model $location, array $data): void
    {
        foreach (config('locations.dhivehi_locales', ['dv']) as $locale) {
            if (array_key_exists("name_{$locale}", $data)) {
                $location->forceFill(['dhivehi_name' => $data["name_{$locale}"]]);
            }
        }
    }

    /**
     * The search and filter form of the index page
     *
     * @param  array<string, mixed>  $filters
     */
    protected function filterForm(Request $request, array $filters): mixed
    {
        $form = Form::make()
            ->id("locations-{$this->resourceName()}-filters")
            ->model($filters)
            ->action($this->url($request, 'index'))
            ->method('GET')
            ->submit(__('locations::strings.actions.filter'))
            ->schema([
                TextInput::make('search')
                    ->label(__('locations::strings.actions.search'))
                    ->placeholder(__('locations::strings.search', ['type' => $this->label('plural')])),
                ...$this->filterFields($request),
            ]);

        return $this->views->isInertia() ? $form->render('json') : $form;
    }

    /**
     * The filters in the query string: `search` and the type's filters
     *
     * @return array<string, mixed>
     */
    protected function filters(Request $request): array
    {
        $keys = ['search', ...LocationOptions::FILTERS[$this->type]];

        return collect($keys)
            ->mapWithKeys(fn (string $key) => [$key => $request->query($key)])
            ->filter(fn ($value) => is_string($value) || is_array($value))
            ->all();
    }

    /**
     * One index row: the cells, links and what the user may do
     *
     * @return array<string, mixed>
     */
    protected function row(Request $request, Model $location): array
    {
        return [
            'id' => $location->getKey(),
            'cells' => $this->cells($location),
            'links' => $this->links($request, $location),
            'can' => $this->abilities($request, $location),
        ];
    }

    /**
     * Label and value pairs of the show page
     *
     * @return array<int, array{key: string, label: string, value: string|int|float|null, dir: string}>
     */
    protected function details(Model $location): array
    {
        $details = [];

        foreach ($this->detailValues($location) as $key => $value) {
            $details[] = [
                'key' => $key,
                'label' => __("locations::strings.fields.{$key}"),
                'value' => $value,
                'dir' => $this->columnDirection($key),
            ];
        }

        return $details;
    }

    /**
     * The values the show page lists: key => value
     *
     * @return array<string, string|int|float|null>
     */
    protected function detailValues(Model $location): array
    {
        return $this->cells($location);
    }

    /**
     * Links to the location's children on the show page, e.g. an atoll's islands
     *
     * @return array<int, array{label: string, url: string}>
     */
    protected function related(Request $request, Model $location): array
    {
        $related = [];

        foreach ($this->children() as $relation => $resource) {
            $route = LocationRoutes::siblingPrefix($request, $resource) . '.index';

            if (! Route::has($route)) {
                continue;
            }

            $type = array_search($resource, Locations::RESOURCES, true);

            $related[] = [
                'label' => __("locations::strings.types.{$type}.plural") . ' (' . $location->{$relation}()->count() . ')',
                'url' => route($route, [$this->type => $location->getKey()]),
            ];
        }

        return $related;
    }

    /**
     * Why the location can't be deleted, or null when it can
     */
    protected function deletionBlocker(Model $location): ?string
    {
        foreach ($this->children() as $relation => $resource) {
            if ($location->{$relation}()->exists()) {
                $type = array_search($resource, Locations::RESOURCES, true);

                return __('locations::strings.messages.has_children', [
                    'type' => $this->label('singular'),
                    'children' => __("locations::strings.types.{$type}.plural"),
                ]);
            }
        }

        return null;
    }

    /**
     * What every page gets: the type, labels, URLs, what the user may do and, for the
     * Inertia pages, their translated text
     *
     * @return array<string, mixed>
     */
    protected function meta(Request $request, ?Model $location = null): array
    {
        $user = $this->user($request);
        $gate = $this->gate($request);

        return [
            'type' => $this->type,
            'resource' => $this->resourceName(),
            'labels' => [
                'singular' => $this->label('singular'),
                'plural' => $this->label('plural'),
            ],
            'guard' => LocationRoutes::guard($request),
            'urls' => array_filter([
                'index' => $this->url($request, 'index'),
                'create' => $this->url($request, 'create'),
                'store' => $this->url($request, 'store'),
                ...($location ? $this->links($request, $location) : []),
            ]),
            'can' => [
                'create' => $user !== null && $gate->allows('create', $this->modelClass()),
                ...($location ? $this->abilities($request, $location) : ['view' => false, 'update' => false, 'delete' => false]),
            ],
            'status' => [
                'success' => $request->hasSession() ? $request->session()->get('success') : null,
                'error' => $request->hasSession() ? $request->session()->get('error') : null,
            ],
            'strings' => $this->views->isInertia() ? $this->strings() : null,
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function links(Request $request, Model $location): array
    {
        return [
            'show' => $this->url($request, 'show', $location),
            'edit' => $this->url($request, 'edit', $location),
            'update' => $this->url($request, 'update', $location),
            'destroy' => $this->url($request, 'destroy', $location),
        ];
    }

    /**
     * @return array{view: bool, update: bool, delete: bool}
     */
    protected function abilities(Request $request, Model $location): array
    {
        if (! $this->user($request)) {
            return ['view' => false, 'update' => false, 'delete' => false];
        }

        $gate = $this->gate($request);

        return [
            'view' => $gate->allows('view', $location),
            'update' => $gate->allows('update', $location),
            'delete' => $gate->allows('delete', $location),
        ];
    }

    protected function resource(Request $request, Model $location): LocationResource
    {
        return (new LocationResource($location))->withMeta(
            $this->links($request, $location),
            $this->abilities($request, $location),
        );
    }

    /**
     * The translated text of the Inertia pages (the Blade views use __() directly)
     *
     * @return array<string, string>
     */
    protected function strings(): array
    {
        return [
            'new' => __('locations::strings.actions.new', ['type' => $this->label('singular')]),
            'back' => __('locations::strings.actions.back', ['type' => $this->label('plural')]),
            'view' => __('locations::strings.actions.view'),
            'edit' => __('locations::strings.actions.edit'),
            'delete' => __('locations::strings.actions.delete'),
            'actions' => __('locations::strings.actions.actions'),
            'confirm_delete' => __('locations::strings.messages.confirm_delete', ['type' => $this->label('singular')]),
            'empty' => __('locations::strings.messages.empty', ['type' => $this->label('plural')]),
            'empty_filtered' => __('locations::strings.messages.empty_filtered', ['type' => $this->label('plural')]),
            'details' => __('locations::strings.sections.details'),
            'related' => __('locations::strings.sections.related'),
        ];
    }

    /**
     * The page title of a location
     */
    protected function title(Model $location): string
    {
        return $location->optionLabel();
    }

    /**
     * Columns that hold Thaana are right to left, codes and numbers left to right
     */
    protected function columnDirection(string $key): string
    {
        if (str_contains($key, 'dhivehi')) {
            return 'rtl';
        }

        if (str_contains($key, 'code') || in_array($key, ['latitude', 'longitude', 'area', 'sort_order'], true)) {
            return 'ltr';
        }

        return 'auto';
    }

    protected function label(string $form): string
    {
        return __("locations::strings.types.{$this->type}.{$form}");
    }

    protected function query(Request $request): Builder
    {
        return $this->modelClass()::query();
    }

    protected function find(Request $request, string $key): Model
    {
        return $this->query($request)->findOrFail($key);
    }

    protected function authorizeAbility(Request $request, string $ability, mixed $arguments): void
    {
        abort_unless($this->user($request) && $this->gate($request)->allows($ability, $arguments), 403);
    }

    protected function gate(Request $request): Gate
    {
        $gate = app(Gate::class);
        $user = $this->user($request);

        return $user ? $gate->forUser($user) : $gate;
    }

    protected function user(Request $request): ?Model
    {
        $user = $request->user(LocationRoutes::guard($request));

        return $user instanceof Model ? $user : null;
    }

    /** @return class-string<Model> */
    protected function modelClass(): string
    {
        return Locations::model($this->type);
    }

    protected function resourceName(): string
    {
        return Locations::resource($this->type);
    }

    /**
     * The URL of one of this resource's routes, honouring the prefix the app registered them under
     */
    protected function url(Request $request, string $action, ?Model $location = null): string
    {
        $name = LocationRoutes::namePrefix($request) . ".{$action}";

        return route($name, $location ? [$this->type => $location->getKey()] : []);
    }
}
