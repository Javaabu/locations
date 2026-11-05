<?php

namespace Javaabu\Locations\Enums;

/**
 * Island categories of the Maldives Land and Survey Authority's island register
 */
enum IslandCategories: string
{
    case RESIDENTIAL = 'residential';
    case TOURISM = 'tourism';
    case INDUSTRIAL = 'industrial';
    case INSTITUTIONAL = 'institutional';
    case UNINHABITED = 'uninhabited';

    public function label(): string
    {
        return __("locations::strings.island_categories.{$this->value}");
    }

    /**
     * Whether people live on islands of this category (councils govern residential islands)
     */
    public function isInhabited(): bool
    {
        return $this === self::RESIDENTIAL;
    }

    /**
     * @return array<string, string> value => translated label
     */
    public static function labels(): array
    {
        $labels = [];

        foreach (self::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        return $labels;
    }

    /**
     * The category for a category name of the island register, e.g. `Tourism Island`
     */
    public static function fromRegister(string $category): ?self
    {
        return match (strtolower(trim($category))) {
            'residential island' => self::RESIDENTIAL,
            'tourism island' => self::TOURISM,
            'industrial island' => self::INDUSTRIAL,
            'institutional island' => self::INSTITUTIONAL,
            'uninhabited island' => self::UNINHABITED,
            default => null,
        };
    }
}
