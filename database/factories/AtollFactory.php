<?php

namespace Javaabu\Locations\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Javaabu\Locations\Models\Atoll;

/**
 * @extends Factory<Atoll>
 */
class AtollFactory extends Factory
{
    protected $model = Atoll::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper($this->faker->unique()->lexify('???')),
            'name' => $this->faker->unique()->words(2, true),
            'is_city' => false,
        ];
    }

    /**
     * Haa Alifu, as the seed data has it
     */
    public function haaAlifu(): static
    {
        return $this->state([
            'code' => 'HA',
            'dhivehi_code' => 'ހއ',
            'letter_code' => 'A',
            'iso_code' => 'MV-07',
            'name' => 'Haa Alifu',
            'dhivehi_name' => 'ހާ އަލިފު',
            'official_name' => 'Thiladhunmathi Uthuruburi',
            'official_dhivehi_name' => 'ތިލަދުންމަތީ އުތުރުބުރި',
            'sort_order' => 1,
        ]);
    }

    /**
     * Malé City, as the seed data has it
     */
    public function male(): static
    {
        return $this->state([
            'code' => 'MLE',
            'dhivehi_code' => null,
            'letter_code' => 'T',
            'iso_code' => 'MV-MLE',
            'name' => 'Malé City',
            'dhivehi_name' => 'މާލެ ސިޓީ',
            'official_name' => 'Malé City',
            'official_dhivehi_name' => 'މާލެ ސިޓީ',
            'is_city' => true,
            'sort_order' => 21,
        ]);
    }
}
