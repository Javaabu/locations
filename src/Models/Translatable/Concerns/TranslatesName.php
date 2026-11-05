<?php

namespace Javaabu\Locations\Models\Translatable\Concerns;

use Javaabu\Locations\Locations;

/**
 * The translatable models: `name` is translated with javaabu/translatable's JSON storage,
 * and a Dhivehi locale without a translation falls back to the `dhivehi_name` column
 */
trait TranslatesName
{
    /**
     * The name in a locale: its translation, then (for Dhivehi locales) the `dhivehi_name`
     * column, then the English name
     */
    public function localizedName(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        if (Locations::isDhivehiLocale($locale)) {
            $dhivehi_name = $this->dhivehiNameFor($locale);

            if (filled($dhivehi_name)) {
                return $dhivehi_name;
            }
        }

        $translation = $this->isAllowedTranslationLocale($locale) ? $this->translate('name', $locale, false) : null;

        if (filled($translation)) {
            return $translation;
        }

        return (string) ($this->englishName() ?? $this->getKey());
    }

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
