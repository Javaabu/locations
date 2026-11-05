<?php

namespace Javaabu\Locations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Locations\Models\Country;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        $code = $this->faker->unique()->lexify('??');

        return [
            'code' => strtoupper($code),
            'alpha3_code' => strtoupper($code) . 'X',
            'numeric_code' => $this->faker->numerify('###'),
            'name' => $this->faker->unique()->country(),
            'dhivehi_name' => null,
            'calling_code' => $this->faker->numerify('##'),
        ];
    }

    public function maldives(): static
    {
        return $this->state([
            'code' => 'MV',
            'alpha3_code' => 'MDV',
            'numeric_code' => '462',
            'name' => 'Maldives',
            'dhivehi_name' => 'ދިވެހިރާއްޖެ',
            'official_name' => 'Republic of Maldives',
            'calling_code' => '960',
        ]);
    }
}
