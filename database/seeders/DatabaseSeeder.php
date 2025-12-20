<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Brands
        $brands = \App\Models\Brand::factory(10)->create();

        // 2. Create Categories Hierarchy
        $categories = [
            'Electronics' => ['Smartphones', 'Laptops', 'Tablets', 'Accessories'],
            'Audio' => ['Headphones', 'Speakers', 'Microphones'],
            'Computers' => ['Desktops', 'Monitors', 'Storage'],
        ];

        foreach ($categories as $parentName => $children) {
            $parent = \App\Models\Category::factory()->create([
                'name' => $parentName,
                'slug' => \Illuminate\Support\Str::slug($parentName),
                'parent_id' => null,
            ]);

            foreach ($children as $childName) {
                $childCategory = \App\Models\Category::factory()->create([
                    'name' => $childName,
                    'slug' => \Illuminate\Support\Str::slug($childName),
                    'parent_id' => $parent->id,
                ]);

                // 3. Create Products for each leaf category
                \App\Models\Product::factory(rand(5, 10))->create([
                    'category_id' => $childCategory->id,
                    'brand_id' => $brands->random()->id,
                ])->each(function ($product) {
                    // 4. Create Variants for each product
                    \App\Models\Variant::factory(rand(1, 3))->create([
                        'product_id' => $product->id,
                    ]);
                });
            }
        }
    }
}
