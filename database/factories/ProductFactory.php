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

    private $pictures = [
        "[{\"path\":\"https://drive.google.com/uc?id=1Vwrfu4CoMv7vtgYNsqxlrqY3Uw1yFi2z&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1IeA1bHF4PTTxtqZfGrfc4ERxFLc19dDt&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=17ZBMCbx-8K7eANn1zftVHgwrrPYLblQ4&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1t5V9rloVnx9q7rUHhW6j4bzlY8girDc9&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1hdfmlEIMDUHTMxOWUHxfFwTbDHvKpCKF&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1DBfex_Lx31PvHjEQ9j_ugeA1Lr2HelMG&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1Au1o3pp40VMRWfeIrwLAgEpTjErvv0II&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1ABjeHqLJP11n3ItAkvb3Qf3MBw2WIcQ_&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1akM7x6FAAIbsWk5BQSoOj_zKap-lR_gU&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1YCtZGOgKgXk698pnvUZIqN-zTZjpRkHm&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1E5ycD0rFw_ZAZCBovkK5gnTbZsX9n2dV&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1ob-p0nu7CL5XPzfy-E-CAQECHnWH0LoB&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
        "[{\"path\":\"https://drive.google.com/uc?id=1aGPZpo7ZYgTblELTtyTmvNX7damGIWMd&export=media\",\"position\":{\"heightP\":100,\"leftP\":0,\"topP\":0}}]",
    ];
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
            'pictures' => $faker->randomElement($this->pictures),
        ];
    }
}
