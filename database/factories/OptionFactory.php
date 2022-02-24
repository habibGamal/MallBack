<?php

namespace Database\Factories;

use App\Models\Option;
use Illuminate\Database\Eloquent\Factories\Factory;

class OptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Option::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = $this->faker;
        $option = $faker->randomElement(['colors_option', 'sizes_option']);
        $option_value = $option == 'colors_option'
            ? $faker->randomElements(['Red', 'Yellow', 'Green', 'Pink', 'Black', 'White', 'Blue'], 3)
            : $faker->randomElements(['M', 'L', 'XL', 'XXL', '3XL', '4XL'], 3);
        return [
            'name' => $option,
            'body' => json_encode($option_value),
        ];
    }
}
