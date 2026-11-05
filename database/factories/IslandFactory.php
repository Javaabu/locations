<?php

namespace Javaabu\Locations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Locations\Enums\IslandCategories;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\Island;

/**
 * @extends Factory<Island>
 */
class IslandFactory extends Factory
{
    protected $model = Island::class;

    public function definition(): array
    {
        return [
            'atoll_id' => Locations::atollModel()::factory(),
            'code' => 'LD' . $this->faker->unique()->numerify('9###'),
            'name' => $this->faker->unique()->lastName() . 'dhoo',
            'category' => IslandCategories::RESIDENTIAL,
            'is_capital' => false,
            'latitude' => $this->faker->randomFloat(6, -0.7, 7.1),
            'longitude' => $this->faker->randomFloat(6, 72.6, 73.8),
            'area' => $this->faker->randomFloat(2, 1, 500),
        ];
    }

    public function category(IslandCategories $category): static
    {
        return $this->state(['category' => $category]);
    }

    public function capital(): static
    {
        return $this->state(['is_capital' => true]);
    }
}
