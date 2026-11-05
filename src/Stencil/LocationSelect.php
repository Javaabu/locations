<?php

namespace Javaabu\Locations\Stencil;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Support\LocationOptions;
use Javaabu\Stencil\Fields\Select;

/**
 * A searchable select of locations of one type, optionally depending on another field
 * (an island select that lists the islands of the chosen atoll)
 */
abstract class LocationSelect extends Select
{
    /** The location type: country, state, city, atoll or island */
    protected string $location_type;

    /** The field this one depends on, e.g. `atoll_id` */
    protected ?string $parent_field = null;

    /** The filter the parent's value is sent as, e.g. `atoll` */
    protected ?string $parent_filter = null;

    protected ?Closure $modify_query = null;

    /** @var array<string, mixed> Filters of the options endpoint, e.g. `['category' => 'residential']` */
    protected array $filters = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__("locations::strings.types.{$this->location_type}.singular"));
        $this->searchable();
        $this->allowAnyOption();
    }

    /**
     * List the locations of the parent field's value: `IslandSelect::make('island_id')->dependsOn('atoll_id')`
     *
     * The parent's select reloads this one's options when it changes (through `Route::locationOptions()`).
     */
    public function dependsOn(string $field, ?string $filter = null): static
    {
        $this->parent_field = $field;
        $this->parent_filter = $filter ?? $this->defaultParentFilter();

        return $this;
    }

    public function getParentField(): ?string
    {
        return $this->parent_field;
    }

    /**
     * Change the query the options come from: `->query(fn (Builder $query) => $query->inhabited())`
     */
    public function query(?Closure $callback): static
    {
        $this->modify_query = $callback;

        return $this;
    }

    /**
     * Narrow the options with the options endpoint's filters: `->filters(['inhabited' => true])`
     *
     * Unlike `query()`, the filters also apply to the options a parent's change reloads.
     *
     * @param  array<string, mixed>  $filters
     */
    public function filters(array $filters): static
    {
        $this->filters = $filters;

        return $this;
    }

    public function getLocationType(): string
    {
        return $this->location_type;
    }

    /**
     * The filter a parent's value is sent as when none is given, e.g. `atoll` for islands
     */
    abstract protected function defaultParentFilter(): string;

    /**
     * @return array<int|string, string>
     */
    public function getOptions(): array
    {
        if ($this->options !== null) {
            return parent::getOptions();
        }

        $query = $this->optionsQuery();

        if ($query === null) {
            return [];
        }

        return $query->get()
            ->mapWithKeys(fn (Model $location) => [$location->getKey() => $location->optionLabel()])
            ->all();
    }

    /**
     * The locations to list: all of them, or the parent value's when the field depends on one
     */
    public function optionsQuery(): ?Builder
    {
        $query = Locations::model($this->location_type)::query()->ordered();
        $filters = $this->filters;

        if ($this->parent_field !== null) {
            $parent = $this->getForm()->getStateValue($this->parent_field);

            if (blank($parent)) {
                return null;
            }

            $filters[$this->parent_filter] = $parent;
        }

        $query = LocationOptions::filter($this->location_type, $query, $filters);

        if ($this->modify_query) {
            $query = $this->evaluate($this->modify_query, ['query' => $query]) ?? $query;
        }

        return $query;
    }

    protected function getTypeRules(): array
    {
        $rules = parent::getTypeRules();
        $model = Locations::model($this->location_type);

        $exists = Rule::exists($model, (new $model)->getKeyName());

        if ($this->isMultiple()) {
            return $rules;
        }

        return [...$rules, $exists];
    }

    public function getAllValidationRules(): array
    {
        $rules = parent::getAllValidationRules();

        if (! $this->isMultiple()) {
            return $rules;
        }

        $model = Locations::model($this->location_type);
        $rules["{$this->getStatePath()}.*"] = [Rule::exists($model, (new $model)->getKeyName())];

        return $rules;
    }

    protected function getFieldProps(): array
    {
        $props = parent::getFieldProps();
        $child = $this->childField();

        if ($child !== null) {
            $props['child'] = $child->getSelector();
        }

        if ($this->parent_field !== null) {
            $url = LocationOptions::url($this->location_type);

            $props['ajaxUrl'] = $url && $this->filters
                ? $url . '?' . http_build_query(['filter' => $this->filters])
                : $url;
            $props['filterField'] = $this->parent_filter;
            $props['parent'] = $this->getForm()->getField($this->parent_field)?->getSelector() ?? "#{$this->parent_field}";
        }

        return array_filter($props, fn ($value) => $value !== null);
    }

    /**
     * The location select of the form that depends on this one
     */
    protected function childField(): ?self
    {
        foreach ($this->getForm()->getFields() as $field) {
            if ($field instanceof self && $field !== $this && $field->getParentField() === $this->getName()) {
                return $field;
            }
        }

        return null;
    }
}
