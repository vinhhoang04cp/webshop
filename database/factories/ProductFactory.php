<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock_quantity' => $this->faker->numberBetween(0, 100),
            'category_id' => Category::factory(),
            'image_url' => $this->faker->imageUrl(640, 480, 'products'),
        ];
    }

    /**
     * Indicate that the product is active.
     */
    // public function active(): static
    // {
    //     return $this->state(fn (array $attributes) => [
    //         'is_active' => true,
    //     ]);
    // }

    /**
     * Indicate that the product is inactive.
     */
    // public function inactive(): static
    // {
    //     return $this->state(fn (array $attributes) => [
    //         'is_active' => false,
    //     ]);
    // }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }
}