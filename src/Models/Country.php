<?php

namespace Javaabu\Locations\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Javaabu\Locations\Database\Factories\CountryFactory;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Concerns\IsLocation;

/**
 * A country, keyed by its ISO 3166-1 alpha-2 code
 *
 * @property int $id
 * @property string $code ISO 3166-1 alpha-2, e.g. `MV`
 * @property string|null $alpha3_code ISO 3166-1 alpha-3, e.g. `MDV`
 * @property string|null $numeric_code ISO 3166-1 numeric, e.g. `462`
 * @property string $name
 * @property string|null $dhivehi_name
 * @property string|null $official_name
 * @property string|null $calling_code e.g. `960`, without the `+`
 * @property-read string $flag
 */
class Country extends Model
{
    /** @use HasFactory<CountryFactory> */
    use HasFactory;

    use IsLocation;

    public const string MALDIVES = 'MV';

    protected $fillable = [
        'code',
        'alpha3_code',
        'numeric_code',
        'name',
        'dhivehi_name',
        'official_name',
        'calling_code',
    ];

    protected function resourceKey(): string
    {
        return 'countries';
    }

    protected static function newFactory(): CountryFactory
    {
        return CountryFactory::new();
    }

    public function states(): HasMany
    {
        return $this->hasMany(Locations::stateModel(), 'country_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(Locations::cityModel(), 'country_id');
    }

    public function isMaldives(): bool
    {
        return $this->code === self::MALDIVES;
    }

    /**
     * The flag emoji, from the code's regional indicator symbols
     */
    public function getFlagAttribute(): string
    {
        $code = strtoupper((string) $this->code);

        if (! preg_match('/^[A-Z]{2}$/', $code)) {
            return '';
        }

        return mb_chr(0x1F1E6 + ord($code[0]) - 65) . mb_chr(0x1F1E6 + ord($code[1]) - 65);
    }

    /**
     * The calling code with a plus, e.g. `+960`
     */
    public function formattedCallingCode(): ?string
    {
        return $this->calling_code ? "+{$this->calling_code}" : null;
    }

    public function scopeCode(Builder $query, string $code): Builder
    {
        return $query->where($query->qualifyColumn('code'), strtoupper($code));
    }

    /**
     * Find a country by its alpha-2 code
     */
    public static function findByCode(string $code): ?static
    {
        return static::query()->code($code)->first();
    }

    protected function searchableColumns(): array
    {
        return ['name', 'dhivehi_name', 'code', 'alpha3_code', 'official_name'];
    }
}
