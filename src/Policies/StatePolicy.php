<?php

namespace Javaabu\Locations\Policies;

/**
 * States: view_states, edit_states and delete_states
 */
class StatePolicy extends LocationPolicy
{
    protected string $resource = 'states';
}
