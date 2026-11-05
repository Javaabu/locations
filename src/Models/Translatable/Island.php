<?php

namespace Javaabu\Locations\Models\Translatable;

use Javaabu\Locations\Models\Island as BaseIsland;
use Javaabu\Locations\Models\Translatable\Concerns\TranslatesName;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

/**
 * The island model when javaabu/translatable is enabled: its name is translatable
 */
class Island extends BaseIsland implements JsonTranslatable
{
    use IsJsonTranslatable;
    use TranslatesName;

    protected array $translatable = ['name'];
}
