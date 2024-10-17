<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentCategory = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'All electronics']);

        // Create subcategories
        $parentCategory->children()->createMany([
            ['name' => 'Smartphones', 'slug' => 'smartphones', 'description' => 'All smartphones'],
            ['name' => 'Laptops', 'slug' => 'laptops', 'description' => 'All laptops'],
            ['name' => 'Tablets', 'slug' => 'tablets', 'description' => 'All tablets'],
            ['name' => 'Headphones', 'slug' => 'headphones', 'description' => 'All headphones'],
            ['name' => 'Cameras', 'slug' => 'cameras', 'description' => 'All cameras'],
        ]);
    }
}
