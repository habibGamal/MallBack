<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $faker = $this->faker;
        $categories = Category::select(['id'])->get();
        $price = $faker->randomNumber(3, true);
        return [
            'name' => $faker->word(),
            'price' => $price,
            'offer_price' => $price - 50,
            'category_id' => $faker->randomElement($categories),
            'stock' => 1,
            'returnable' => 1,
            'description' => $faker->paragraphs(2, true),
            'specifications' => '[{}]',
            'brand' => $faker->word(),
            'warranty' => '3 Year',
            // 'pictures' => $jsonPictures,
        ];
    }
}
