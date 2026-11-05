<?php

namespace Javaabu\Locations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\State;

/**
 * @extends Factory<State>
 */
class StateFactory extends Factory
{
    protected $model = State::class;

    public function definition(): array
    {
        return [
            'country_id' => Locations::countryModel()::factory(),
            'code' => null,
            'name' => $this->faker->unique()->city(),
            'type' => 'Province',
        ];
    }
}
