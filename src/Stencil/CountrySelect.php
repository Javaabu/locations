<?php

namespace Javaabu\Locations\Stencil;

/**
 * A searchable select of countrys
 */
class CountrySelect extends LocationSelect
{
    protected string $type = 'country-select';

    protected string $location_type = 'country';

    protected function defaultParentFilter(): string
    {
        return 'country';
    }
}
