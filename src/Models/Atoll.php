<?php

namespace Javaabu\Locations\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Javaabu\Locations\Database\Factories\AtollFactory;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Concerns\IsLocation;

/**
 * An administrative atoll of the Maldives, or Malé City
 *
 * @property int $id
 * @property string $code The Latin abbreviation, e.g. `HA` (`MLE` for Malé City)
 * @property string|null $dhivehi_code The Thaana abbreviation, e.g. `ހއ`
 * @property string|null $letter_code The administrative letter code, e.g. `A`
 * @property string|null $iso_code ISO 3166-2:MV, e.g. `MV-07`
 * @property string $name e.g. `Haa Alifu`
 * @property string|null $dhivehi_name e.g. `ހާ އަލިފު`
 * @property string|null $official_name e.g. `Thiladhunmathi Uthuruburi`
 * @property string|null $official_dhivehi_name e.g. `ތިލަދުންމަތީ އުތުރުބުރި`
 * @property bool $is_city Whether a city council governs the whole atoll (Malé, Addu, Fuvahmulah)
 * @property int|null $sort_order North to south, as the letter codes run
 */
class Atoll extends Model
{
    /** @use HasFactory<AtollFactory> */
    use HasFactory;

    use IsLocation;

    protected $fillable = [
        'code',
        'dhivehi_code',
        'letter_code',
        'iso_code',
        'name',
        'dhivehi_name',
        'official_name',
        'official_dhivehi_name',
        'is_city',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_city' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected function resourceKey(): string
    {
        return 'atolls';
    }

    protected static function newFactory(): AtollFactory
    {
        return AtollFactory::new();
    }

    public function islands(): HasMany
    {
        return $this->hasMany(Locations::islandModel(), 'atoll_id');
    }

    /**
     * The atoll's capital island
     */
    public function capital(): HasOne
    {
        return $this->hasOne(Locations::islandModel(), 'atoll_id')->where('is_capital', true);
    }

    /**
     * The abbreviation in a locale: `ހއ` for Dhivehi, `HA` otherwise
     */
    public function localizedCode(?string $locale = null): string
    {
        if (Locations::isDhivehiLocale($locale) && filled($this->dhivehi_code)) {
            return $this->dhivehi_code;
        }

        return (string) $this->code;
    }

    /**
     * The official name in a locale
     */
    public function localizedOfficialName(?string $locale = null): ?string
    {
        if (Locations::isDhivehiLocale($locale) && filled($this->official_dhivehi_name)) {
            return $this->official_dhivehi_name;
        }

        return $this->official_name;
    }

    /**
     * `HA. Haa Alifu` / `ހއ. ހާ އަލިފު`: the abbreviation and the name, as atolls are listed
     */
    public function optionLabel(): string
    {
        $name = $this->localizedName();
        $code = $this->localizedCode();

        if ($code === '' || $code === $name) {
            return $name;
        }

        return "{$code}. {$name}";
    }

    public function scopeCode(Builder $query, string $code): Builder
    {
        return $query->where($query->qualifyColumn('code'), $code);
    }

    /**
     * North to south, as the letter codes run, then by name
     */
    public function scopeOrdered(Builder $query, ?string $locale = null): Builder
    {
        return $query
            ->orderByRaw("case when {$query->qualifyColumn('sort_order')} is null then 1 else 0 end")
            ->orderBy($query->qualifyColumn('sort_order'))
            ->orderBy($query->qualifyColumn('name'));
    }

    public static function findByCode(string $code): ?static
    {
        return static::query()->code($code)->first();
    }

    protected function searchableColumns(): array
    {
        return ['name', 'dhivehi_name', 'code', 'dhivehi_code', 'official_name', 'official_dhivehi_name'];
    }
}
