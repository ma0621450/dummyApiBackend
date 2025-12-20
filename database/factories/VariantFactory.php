<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Variant>
 */
class VariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'sku' => $this->faker->unique()->bothify('SKU-####-????'),
            'price' => $this->faker->randomFloat(2, 10, 2000),
            'stock' => $this->faker->numberBetween(0, 50),
            'attributes' => [
                'Color' => $this->faker->safeColorName(),
                'Storage' => $this->faker->randomElement(['64GB', '128GB', '256GB']),
            ],
            'image_url' => "https://picsum.photos/seed/" . $this->faker->uuid() . "/400/300",
        ];
    }
}
