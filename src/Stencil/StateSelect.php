<?php

namespace Javaabu\Locations\Stencil;

/**
 * A searchable select of states
 */
class StateSelect extends LocationSelect
{
    protected string $type = 'state-select';

    protected string $location_type = 'state';

    protected function defaultParentFilter(): string
    {
        return 'country';
    }
}
