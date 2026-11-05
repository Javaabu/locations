<?php

namespace Javaabu\Locations\Policies;

/**
 * Countries: view_countries, edit_countries and delete_countries
 */
class CountryPolicy extends LocationPolicy
{
    protected string $resource = 'countries';
}
