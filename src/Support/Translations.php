<?php

namespace Javaabu\Locations\Support;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Translatable;
use Throwable;

/**
 * The one place the package talks to the optional javaabu/translatable
 */
class Translations
{
    /**
     * Whether javaabu/translatable is installed, turned on and has active languages
     */
    public static function enabled(): bool
    {
        if (! class_exists(Translatable::class)) {
            return false;
        }

        try {
            return Translatable::enabled();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Whether the model (or class) translates the field (or any field)
     */
    public static function supports(object|string|null $model, ?string $field = null): bool
    {
        if (! class_exists(Translatable::class)) {
            return false;
        }

        try {
            return Translatable::supports($model, $field);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * The per-locale inputs and values of the translatable `name` field, for the Inertia pages
     *
     * @return array{values: array<string, mixed>, inputs: array<string, array<int, array<string, mixed>>>}|null
     */
    public static function form(Model|string $model): ?array
    {
        if (! static::supports($model, 'name')) {
            return null;
        }

        return [
            'values' => $model instanceof Model ? Translatable::values($model) : [],
            'inputs' => [
                'name' => Translatable::inputs('name', $model)
                    ->values()
                    ->map(fn ($input) => $input->toArray())
                    ->all(),
            ],
        ];
    }

    /**
     * Expand rules for the translatable fields of the model into per-locale rules
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    public static function rules(Model|string $model, array $rules): array
    {
        if (! static::supports($model)) {
            return $rules;
        }

        return Translatable::rules($model, $rules);
    }
}
