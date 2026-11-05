<?php

namespace Javaabu\Locations\Stencil;

/**
 * A searchable select of citys
 */
class CitySelect extends LocationSelect
{
    protected string $type = 'city-select';

    protected string $location_type = 'city';

    protected function defaultParentFilter(): string
    {
        return 'state';
    }
}
