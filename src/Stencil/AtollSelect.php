<?php

namespace Javaabu\Locations\Stencil;

/**
 * A searchable select of atolls
 */
class AtollSelect extends LocationSelect
{
    protected string $type = 'atoll-select';

    protected string $location_type = 'atoll';

    protected function defaultParentFilter(): string
    {
        return 'country';
    }
}
