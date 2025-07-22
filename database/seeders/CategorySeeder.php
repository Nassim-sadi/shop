<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::truncate();

        $calculateOrder = function ($parentId) {
            // Find the maximum order for the current parent's children
            $maxOrder = Category::where('parent_id', $parentId)->max('order');
            // Return the next order number
            return $maxOrder !== null ? $maxOrder + 1 : 1; // Start from 1 if no children exist
        };

        $electronics = Category::create(['image' => 'electronics.jpg', 'name' => 'Electronics', 'slug' => 'electronics', 'description' => 'All electronics', 'order' => Category::whereNull('parent_id')->max('order') + 1,]);
        $smartphones = $electronics->children()->create(['image' => 'smartphones.jpeg', 'name' => 'Smartphones', 'slug' => 'smartphones', 'description' => 'All smartphones', 'order' => $calculateOrder($electronics->id),]);

        $childrenData = [
            ['image' => 'android-phones.jpg', 'name' => 'Android Phones', 'slug' => 'android-phones', 'description' => 'Android smartphones'],
            ['image' => 'iphones.jpg', 'name' => 'iPhones', 'slug' => 'iphones', 'description' => 'Apple iPhones'],
        ];
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($smartphones->id); // Calculate order for each child
            $smartphones->children()->create($data);
        }

        $laptops = $electronics->children()->create(['image' => 'laptops.jpg', 'name' => 'Laptops', 'slug' => 'laptops', 'description' => 'All laptops', 'order' => $calculateOrder($electronics->id),]);

        $childrenData = [
            ['image' => 'windows-laptops.jpg', 'name' => 'Windows laptops', 'slug' => 'windows-laptops', 'description' => 'Windows laptops'],
            ['image' => 'macbooks.jpg', 'name' => 'Mac laptops', 'slug' => 'mac-laptops', 'description' => 'Mac laptops'],
            ['image' => 'chromebooks.jpg', 'name' => 'Chromebooks', 'slug' => 'chromebooks', 'description' => 'Chromebooks'],
        ];
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($laptops->id); // Calculate order for each child
            $laptops->children()->create($data);
        }
        $childrenData = [
            ['image' => 'tablets.jpg', 'name' => 'Tablets', 'slug' => 'tablets', 'description' => 'Tablets'],
            ['image' => 'headphones.webp', 'name' => 'Headphones', 'slug' => 'headphones', 'description' => 'Headphones'],
            ['image' => 'video.jpg', 'name' => 'Video and Sound', 'slug' => 'video-sound', 'description' => 'Video and Sound'],
            ['image' => 'video-games.jpeg', 'name' => 'Games and Accessories', 'slug' => 'games-accessories', 'description' => 'Games and Accessories'],
            [
                'image' => 'tv.webp',
                'name' => 'TV',
                'slug' => 'tv',
                'description' => 'All types of TVs',
            ]


        ];
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($electronics->id); // Calculate order for each child
            $electronics->children()->create($data);
        }

        // House and Garden and subcategories
        $houseGarden = Category::create(['image' => 'house-garden.webp', 'name' => 'House and Garden', 'slug' => 'house-garden', 'description' => 'Products for house and garden', 'order' => Category::whereNull('parent_id')->max('order') + 1,]);
        $furniture = $houseGarden->children()->create(['image' => 'furniture.jpg', 'name' => 'Furniture', 'slug' => 'furniture', 'description' => 'All types of furniture', 'order' => $calculateOrder($houseGarden->id),]);

        $childrenData = [
            ['image' => 'chairs.png', 'name' => 'Chairs', 'slug' => 'chairs', 'description' => 'All types of chairs'],
            ['image' => 'sofas.jpg', 'name' => 'Sofas', 'slug' => 'sofas', 'description' => 'All types of sofas'],
            ['image' => 'tables.webp', 'name' => 'Tables', 'slug' => 'tables', 'description' => 'All types of tables'],
            ['image' => 'bed.jpg', 'name' => 'Beds', 'slug' => 'beds', 'description' => 'All types of beds']
        ];

        // Create children for Furniture and assign unique order values
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($furniture->id); // Calculate order for each child
            $furniture->children()->create($data);
        }

        // Create subcategories for House and Garden
        $childrenData = [
            ['image' => 'home-decor.jpg', 'name' => 'Home Decor', 'slug' => 'home-decor', 'description' => 'Home decor'],
            ['image' => 'tools.jpg', 'name' => 'Tools and DIY', 'slug' => 'tools-diy', 'description' => 'Tools and do-it-yourself products'],
        ];

        // Create children for House and Garden and assign unique order values
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($houseGarden->id); // Calculate order for each child
            $houseGarden->children()->create($data);
        }
    }
}
