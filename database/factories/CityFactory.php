<?php

namespace Javaabu\Locations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Locations\Locations;
use Javaabu\Locations\Models\City;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'country_id' => Locations::countryModel()::factory(),
            'state_id' => null,
            'name' => $this->faker->unique()->city(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
        ];
    }
}
