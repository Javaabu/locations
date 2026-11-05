<?php

namespace Javaabu\Locations\Http\Resources;

use BackedEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Island;

/**
 * Any location as JSON: its attributes, localized names, loaded parents and, on the admin
 * routes, its links and what the user may do with it
 *
 * @property Model $resource
 */
class LocationResource extends JsonResource
{
    /** Attributes never sent */
    protected const array HIDDEN = ['translations', 'lang'];

    /** @var array<string, string>|null action => URL */
    protected ?array $links = null;

    /** @var array<string, bool>|null ability => allowed */
    protected ?array $abilities = null;

    /**
     * @param  array<string, string>  $links
     * @param  array<string, bool>  $abilities
     */
    public function withMeta(array $links, array $abilities): static
    {
        $this->links = $links;
        $this->abilities = $abilities;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $model = $this->resource;
        $attributes = collect($model->attributesToArray())->except(self::HIDDEN)->all();

        $data = [
            'id' => $model->getKey(),
            'type' => Locations::typeOf($model),
            ...$attributes,
            'name' => $model->getAttributes()['name'] ?? null,
            'localized_name' => $model->localizedName(),
            'label' => $model->optionLabel(),
        ];

        if ($model instanceof Island) {
            $data['formatted_name'] = $model->formattedName();
        }

        foreach ($attributes as $key => $value) {
            if ($model->getAttribute($key) instanceof IslandCategories) {
                $data[$key] = $model->getAttribute($key)->value;
                $data["{$key}_label"] = $model->getAttribute($key)->label();
            } elseif ($value instanceof BackedEnum) {
                $data[$key] = $value->value;
            }
        }

        foreach (['country', 'state', 'atoll'] as $relation) {
            if ($model->relationLoaded($relation)) {
                $data[$relation] = $model->getRelation($relation)
                    ? (new static($model->getRelation($relation)))->resolve($request)
                    : null;
            }
        }

        if ($this->abilities !== null) {
            $data['can'] = $this->abilities;
        }

        if ($this->links !== null) {
            $data['links'] = $this->links;
        }

        return $data;
    }
}
