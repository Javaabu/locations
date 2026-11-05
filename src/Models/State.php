<?php

namespace Javaabu\Locations\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Javaabu\Locations\Database\Factories\StateFactory;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Concerns\IsLocation;

/**
 * A state, province, region or other first-level subdivision of a country
 *
 * @property int $id
 * @property int $country_id
 * @property string|null $code ISO 3166-2, e.g. `LK-1`
 * @property string $name
 * @property string|null $dhivehi_name
 * @property string|null $type The ISO 3166-2 subdivision category, e.g. `Province`
 */
class State extends Model
{
    /** @use HasFactory<StateFactory> */
    use HasFactory;

    use IsLocation;

    protected $fillable = [
        'country_id',
        'code',
        'name',
        'dhivehi_name',
        'type',
    ];

    protected function resourceKey(): string
    {
        return 'states';
    }

    protected static function newFactory(): StateFactory
    {
        return StateFactory::new();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Locations::countryModel(), 'country_id');
    }

    public function cities(): HasMany
    {
        return $this->hasMany(Locations::cityModel(), 'state_id');
    }

    /**
     * States of a country, given its id or its alpha-2 code
     */
    public function scopeOfCountry(Builder $query, Model|int|string $country): Builder
    {
        return $query->where($query->qualifyColumn('country_id'), static::countryKey($country));
    }
}
