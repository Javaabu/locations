<?php

namespace Javaabu\Locations\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Support\LocationOptions;

/**
 * The options of the location selects, as javaabu/forms' and @javaabu/forms' select2
 * request them: `GET /location-options/islands?filter[atoll]=3&filter[search]=dhi&per_page=-1`
 *
 * Read-only and public by default: locations aren't secret. Wrap `Route::locationOptions()`
 * in middleware of your own to change that.
 */
class LocationOptionsController extends Controller
{
    public function index(Request $request, string $type): JsonResponse
    {
        $filters = $request->input('filter', []);
        $filters = is_array($filters) ? $filters : [];

        if ($type === 'island' && ! array_key_exists('named', $filters)) {
            $filters['named'] = true;
        }

        $query = LocationOptions::filter($type, $this->query($request, $type), $filters);
        $per_page = $request->integer('per_page', 20);

        if ($per_page < 0) {
            $locations = $query->limit((int) config('locations.max_options', 2000))->get();

            return new JsonResponse(['data' => $locations->map(fn (Model $location) => $this->option($location))->all()]);
        }

        $locations = $query->paginate(min(max($per_page, 1), 100))->withQueryString();

        return new JsonResponse([
            ...$locations->toArray(),
            'data' => $locations->getCollection()->map(fn (Model $location) => $this->option($location))->all(),
        ]);
    }

    protected function query(Request $request, string $type): Builder
    {
        return Locations::model($type)::query()->ordered();
    }

    /**
     * One option: the id, the label the selects show and the code
     *
     * @return array<string, mixed>
     */
    protected function option(Model $location): array
    {
        return [
            ...$location->toOption(),
            'code' => $location->getAttribute('code'),
        ];
    }
}
