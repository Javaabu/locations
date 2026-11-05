<?php

namespace Javaabu\Locations\Views\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\View\Component;
use Javaabu\Forms\FormsDataBinder;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Support\LocationOptions;

/**
 * `<x-locations::island-select>` and friends: a javaabu/forms select2 of locations,
 * optionally listing only the locations of a parent field's value
 *
 * ```blade
 * <x-locations::atoll-select name="atoll_id" child="island_id" />
 * <x-locations::island-select name="island_id" parent="atoll_id" />
 * ```
 */
class LocationSelect extends Component
{
    /** @var array<int|string, string> */
    public array $options;

    public string $label;

    public string $child_selector;

    public string $ajax_url;

    public string $filter_field;

    /**
     * @param  string  $type  country, state, city, atoll or island
     * @param  string|null  $parent  the field this one depends on: a name (`atoll_id`) or a selector
     * @param  mixed  $parentValue  the parent's value, when it isn't old input or on the bound model
     * @param  string|null  $child  the field that depends on this one: a name or a selector
     * @param  string|null  $filterField  the filter the parent's value is sent as (default by type)
     * @param  array<string, mixed>  $filters  more filters for the options, e.g. `['category' => 'residential']`
     * @param  array<int|string, string>|null  $options  the options, instead of the package's
     */
    public function __construct(
        public string $type,
        public string $name,
        ?string $label = null,
        public ?string $parent = null,
        public mixed $parentValue = null,
        ?string $child = null,
        ?string $filterField = null,
        public array $filters = [],
        ?array $options = null,
    ) {
        $this->label = $label ?? __("locations::strings.types.{$type}.singular");
        $this->child_selector = $child ? static::selector($child) : '';
        $this->filter_field = $parent ? ($filterField ?? static::defaultParentFilter($type)) : '';
        $this->ajax_url = $parent ? (LocationOptions::url($type) ?? '') : '';

        if ($this->ajax_url !== '' && $filters !== []) {
            $this->ajax_url .= '?' . http_build_query(['filter' => $filters]);
        }

        $this->options = $options ?? $this->loadOptions();
    }

    /**
     * The locations of the type, or of the parent's current value
     *
     * @return array<int|string, string>
     */
    protected function loadOptions(): array
    {
        $query = Locations::model($this->type)::query()->ordered();
        $filters = $this->filters;

        if ($this->parent !== null) {
            $parent_value = $this->currentParentValue();

            if (blank($parent_value)) {
                return [];
            }

            $filters[$this->filter_field] = $parent_value;
        }

        return LocationOptions::filter($this->type, $query, $filters)
            ->limit((int) config('locations.max_options', 2000))
            ->get()
            ->mapWithKeys(fn (Model $location) => [$location->getKey() => $location->optionLabel()])
            ->all();
    }

    /**
     * The parent's value: old input, then the value given, then the form's bound model
     */
    protected function currentParentValue(): mixed
    {
        $name = static::fieldName((string) $this->parent);

        return old($name) ?? $this->parentValue ?? data_get(app(FormsDataBinder::class)->get(), $name);
    }

    public static function defaultParentFilter(string $type): string
    {
        return match ($type) {
            'island' => 'atoll',
            'city' => 'state',
            default => 'country',
        };
    }

    /**
     * A field name as a selector: `island_id` → `#island_id`
     */
    public static function selector(string $field): string
    {
        if (preg_match('/^[#.\[]/', $field)) {
            return $field;
        }

        return '#' . str_replace(['[', ']'], ['_', ''], $field);
    }

    /**
     * A selector as a field name: `#atoll_id` → `atoll_id`
     */
    public static function fieldName(string $field): string
    {
        return ltrim($field, '#');
    }

    public function render(): View
    {
        return view('locations::components.location-select');
    }
}
