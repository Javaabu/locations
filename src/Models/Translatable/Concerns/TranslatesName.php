<?php

namespace Javaabu\Locations\Models\Translatable\Concerns;

use Javaabu\Locations\Locations;

/**
 * The translatable models: `name` is translated with javaabu/translatable's JSON storage,
 * and a Dhivehi locale without a translation falls back to the `dhivehi_name` column
 */
trait TranslatesName
{
    protected function dhivehiNameFor(string $locale): ?string
    {
        $translation = $this->translate('name', $locale, false);

        if (filled($translation)) {
            return $translation;
        }

        return $this->getAttributeFromArray('dhivehi_name');
    }

    /**
     * Keep morph types and policies pointing at the package's base model
     */
    public function getMorphClass(): string
    {
        return Locations::MODELS[Locations::typeOf($this)];
    }
}
