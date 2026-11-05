<?php

namespace Javaabu\Locations\Models\Translatable;

use Javaabu\Locations\Models\State as BaseState;
use Javaabu\Locations\Models\Translatable\Concerns\TranslatesName;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

/**
 * The state model when javaabu/translatable is enabled: its name is translatable
 */
class State extends BaseState implements JsonTranslatable
{
    use IsJsonTranslatable;
    use TranslatesName;

    protected array $translatable = ['name'];
}
