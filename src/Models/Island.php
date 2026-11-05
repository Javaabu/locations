<?php

namespace Javaabu\Locations\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Javaabu\Locations\Database\Factories\IslandFactory;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Concerns\IsLocation;

/**
 * An island of the Maldives, keyed by its feature code in the island register
 *
 * @property int $id
 * @property int $atoll_id
 * @property string $code The island register's feature code, e.g. `LD0442`
 * @property string|null $name e.g. `Malé`; null for unnamed features
 * @property string|null $dhivehi_name e.g. `މާލެ`
 * @property IslandCategories|null $category
 * @property bool $is_capital Whether it's the capital of its atoll
 * @property float|null $latitude
 * @property float|null $longitude
 * @property float|null $area Land area in hectares
 * @property-read Atoll|null $atoll
 */
class Island extends Model
{
    /** @use HasFactory<IslandFactory> */
    use HasFactory;

    use IsLocation;

    protected $fillable = [
        'atoll_id',
        'code',
        'name',
        'dhivehi_name',
        'category',
        'is_capital',
        'latitude',
        'longitude',
        'area',
    ];

    protected function casts(): array
    {
        return [
            'category' => IslandCategories::class,
            'is_capital' => 'boolean',
            'latitude' => 'float',
            'longitude' => 'float',
            'area' => 'float',
        ];
    }

    protected function resourceKey(): string
    {
        return 'islands';
    }

    protected static function newFactory(): IslandFactory
    {
        return IslandFactory::new();
    }

    public function atoll(): BelongsTo
    {
        return $this->belongsTo(Locations::atollModel(), 'atoll_id');
    }

    /**
     * Whether people live on it (a residential island)
     */
    public function isInhabited(): bool
    {
        return $this->category?->isInhabited() ?? false;
    }

    /**
     * The island with its atoll, as Maldivian addresses write it: `HA. Dhidhdhoo` / `ހއ. ދިއްދޫ`
     *
     * Malé City's islands are written without an atoll (`Malé`, `Hulhumalé`).
     */
    public function formattedName(?string $locale = null): string
    {
        $name = $this->localizedName($locale);
        $atoll = $this->relationLoaded('atoll') || $this->atoll_id ? $this->atoll : null;

        if (! $atoll || $atoll->code === 'MLE') {
            return $name;
        }

        return "{$atoll->localizedCode($locale)}. {$name}";
    }

    public function getFormattedNameAttribute(): string
    {
        return $this->formattedName();
    }

    public function optionLabel(): string
    {
        return $this->localizedName();
    }

    public function scopeCode(Builder $query, string $code): Builder
    {
        return $query->where($query->qualifyColumn('code'), strtoupper($code));
    }

    /**
     * Islands of an atoll, given the model, its id or its code (`HA`)
     */
    public function scopeOfAtoll(Builder $query, Model|int|string $atoll): Builder
    {
        if ($atoll instanceof Model) {
            $atoll = $atoll->getKey();
        }

        if (is_string($atoll) && ! ctype_digit($atoll)) {
            $atoll = Locations::atollModel()::query()->code($atoll)->value('id');
        }

        return $query->where($query->qualifyColumn('atoll_id'), $atoll);
    }

    /**
     * Islands of one or more categories
     *
     * @param  IslandCategories|string|array<int, IslandCategories|string>  $categories
     */
    public function scopeCategory(Builder $query, IslandCategories|string|array $categories): Builder
    {
        $values = array_map(
            fn (IslandCategories|string $category) => $category instanceof IslandCategories ? $category->value : $category,
            is_array($categories) ? $categories : [$categories],
        );

        return $query->whereIn($query->qualifyColumn('category'), $values);
    }

    /**
     * Residential islands: the ones people live on
     */
    public function scopeInhabited(Builder $query): Builder
    {
        return $query->category(IslandCategories::RESIDENTIAL);
    }

    /**
     * Islands with a name (the register also lists unnamed features)
     */
    public function scopeNamed(Builder $query): Builder
    {
        return $query->whereNotNull($query->qualifyColumn('name'));
    }

    /**
     * Atoll capitals
     */
    public function scopeCapitals(Builder $query): Builder
    {
        return $query->where($query->qualifyColumn('is_capital'), true);
    }

    public static function findByCode(string $code): ?static
    {
        return static::query()->code($code)->first();
    }
}
