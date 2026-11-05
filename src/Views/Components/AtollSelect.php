<?php

namespace Javaabu\Locations\Views\Components;

/**
 * `<x-locations::atoll-select>`
 */
class AtollSelect extends LocationSelect
{
    /**
     * @param  array<string, mixed>  $filters
     * @param  array<int|string, string>|null  $options
     */
    public function __construct(
        string $name = 'atoll_id',
        ?string $label = null,
        ?string $parent = null,
        mixed $parentValue = null,
        ?string $child = null,
        ?string $filterField = null,
        array $filters = [],
        ?array $options = null,
    ) {
        parent::__construct('atoll', $name, $label, $parent, $parentValue, $child, $filterField, $filters, $options);
    }
}
