<?php

namespace Javaabu\Locations\Views\Components;

/**
 * `<x-locations::country-select>`
 */
class CountrySelect extends LocationSelect
{
    /**
     * @param  array<string, mixed>  $filters
     * @param  array<int|string, string>|null  $options
     */
    public function __construct(
        string $name = 'country_id',
        ?string $label = null,
        ?string $parent = null,
        mixed $parentValue = null,
        ?string $child = null,
        ?string $filterField = null,
        array $filters = [],
        ?array $options = null,
    ) {
        parent::__construct('country', $name, $label, $parent, $parentValue, $child, $filterField, $filters, $options);
    }
}
