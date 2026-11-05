<?php

namespace Javaabu\Locations\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Javaabu\Locations\Locations;

/**
 * What every location model shares: its configured table, bilingual names and search
 *
 * @property string|null $name
 * @property string|null $dhivehi_name
 * @property-read string $localized_name
 */
trait IsLocation
{
    /** The resource key of the model's table in `config('locations.tables')` */
    abstract protected function resourceKey(): string;

    public function getTable(): string
    {
        return Locations::table($this->resourceKey());
    }

    /**
     * The name in a locale: the Dhivehi name for Dhivehi locales (when the location has one),
     * the English name otherwise
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

        return (string) ($this->englishName() ?? $this->getKey());
    }

    /**
     * The Dhivehi name used for a Dhivehi locale. The translatable models prefer the translation.
     */
    protected function dhivehiNameFor(string $locale): ?string
    {
        return $this->getAttributeValue('dhivehi_name');
    }

    /**
     * The English name, before any translation
     */
    protected function englishName(): ?string
    {
        return $this->getAttributeFromArray('name') ?? $this->getAttributeValue('code');
    }

    public function getLocalizedNameAttribute(): string
    {
        return $this->localizedName();
    }

    /**
     * Search names, Dhivehi names and codes
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        $columns = $this->searchableColumns();

        return $query->where(function (Builder $query) use ($search, $columns) {
            foreach ($columns as $column) {
                $query->orWhere($query->qualifyColumn($column), 'like', "%{$search}%");
            }
        });
    }

    /**
     * Order by name in the current locale's language
     */
    public function scopeOrdered(Builder $query, ?string $locale = null): Builder
    {
        if (Locations::isDhivehiLocale($locale)) {
            return $query
                ->orderByRaw("case when {$query->qualifyColumn('dhivehi_name')} is null then 1 else 0 end")
                ->orderBy($query->qualifyColumn('dhivehi_name'))
                ->orderBy($query->qualifyColumn('name'));
        }

        return $query->orderBy($query->qualifyColumn('name'));
    }

    /**
     * @return array<int, string>
     */
    protected function searchableColumns(): array
    {
        return ['name', 'dhivehi_name', 'code'];
    }

    /**
     * What the select inputs and the options endpoint show: `id` and the localized `name`
     *
     * @return array{id: int|string, name: string}
     */
    public function toOption(): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->optionLabel(),
        ];
    }

    /**
     * The label of the model in a select
     */
    public function optionLabel(): string
    {
        return $this->localizedName();
    }

    /**
     * A country's key from a model, an id or an alpha-2 code
     */
    protected static function countryKey(Model|int|string $country): mixed
    {
        if ($country instanceof Model) {
            return $country->getKey();
        }

        if (is_string($country) && ! ctype_digit($country)) {
            return Locations::countryModel()::query()->code($country)->value('id');
        }

        return $country;
    }
}
