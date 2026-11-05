<?php

namespace Javaabu\Locations\Stencil;

/**
 * A searchable select of islands
 */
class IslandSelect extends LocationSelect
{
    protected string $type = 'island-select';

    protected string $location_type = 'island';

    protected function defaultParentFilter(): string
    {
        return 'atoll';
    }
}
