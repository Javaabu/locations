<?php

namespace Javaabu\Locations\Models\Translatable;

use Javaabu\Locations\Models\City as BaseCity;
use Javaabu\Locations\Models\Translatable\Concerns\TranslatesName;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

/**
 * The city model when javaabu/translatable is enabled: its name is translatable
 */
class City extends BaseCity implements JsonTranslatable
{
    use IsJsonTranslatable;
    use TranslatesName;

    protected array $translatable = ['name'];
}
