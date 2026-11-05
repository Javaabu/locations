<?php

namespace Javaabu\Locations\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Javaabu\Locations\Database\Factories\CityFactory;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Concerns\IsLocation;

/**
 * A city of a country, optionally in one of its states
 *
 * @property int $id
 * @property int $country_id
 * @property int|null $state_id
 * @property string|null $code
 * @property string $name
 * @property string|null $dhivehi_name
 * @property float|null $latitude
 * @property float|null $longitude
 */
class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    use IsLocation;

    protected $fillable = [
        'country_id',
        'state_id',
        'code',
        'name',
        'dhivehi_name',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    protected function resourceKey(): string
    {
        return 'cities';
    }

    protected static function newFactory(): CityFactory
    {
        return CityFactory::new();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Locations::countryModel(), 'country_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(Locations::stateModel(), 'state_id');
    }

    public function scopeOfCountry(Builder $query, Model|int|string $country): Builder
    {
        return $query->where($query->qualifyColumn('country_id'), static::countryKey($country));
    }

    public function scopeOfState(Builder $query, Model|int|string $state): Builder
    {
        return $query->where($query->qualifyColumn('state_id'), $state instanceof Model ? $state->getKey() : $state);
    }
}
