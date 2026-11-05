<?php

namespace Javaabu\Locations\Models\Translatable;

use Javaabu\Locations\Models\Country as BaseCountry;
use Javaabu\Locations\Models\Translatable\Concerns\TranslatesName;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

/**
 * The country model when javaabu/translatable is enabled: its name is translatable
 */
class Country extends BaseCountry implements JsonTranslatable
{
    use IsJsonTranslatable;
    use TranslatesName;

    protected array $translatable = ['name'];
}
