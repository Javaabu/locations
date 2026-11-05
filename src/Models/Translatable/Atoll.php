<?php

namespace Javaabu\Locations\Models\Translatable;

use Javaabu\Locations\Models\Atoll as BaseAtoll;
use Javaabu\Locations\Models\Translatable\Concerns\TranslatesName;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

/**
 * The atoll model when javaabu/translatable is enabled: its name is translatable
 */
class Atoll extends BaseAtoll implements JsonTranslatable
{
    use IsJsonTranslatable;
    use TranslatesName;

    protected array $translatable = ['name'];
}
