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

        $electronics = Category::create(['image' => 'test.jpg', 'name' => 'Electronics', 'slug' => 'electronics', 'description' => 'All electronics',            'order' => Category::whereNull('parent_id')->max('order') + 1,]);
        $smartphones = $electronics->children()->create(['image' => 'test.jpg', 'name' => 'Smartphones', 'slug' => 'smartphones', 'description' => 'All smartphones', 'order' => $calculateOrder($electronics->id),]);

        $childrenData = [
            ['image' => 'test.jpg', 'name' => 'Android Phones', 'slug' => 'android-phones', 'description' => 'Android smartphones'],
            ['image' => 'test.jpg', 'name' => 'iPhones', 'slug' => 'iphones', 'description' => 'Apple iPhones'],
        ];
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($smartphones->id); // Calculate order for each child
            $smartphones->children()->create($data);
        }
        $laptops = $electronics->children()->create(['image' => 'test.jpg', 'name' => 'Laptops', 'slug' => 'laptops', 'description' => 'All laptops', 'order' => $calculateOrder($electronics->id),]);

        $childrenData = [
            ['image' => 'test.jpg', 'name' => 'Windows laptops', 'slug' => 'windows-laptops', 'description' => 'Windows laptops'],
            ['image' => 'test.jpg', 'name' => 'Mac laptops', 'slug' => 'mac-laptops', 'description' => 'Mac laptops'],
            ['image' => 'test.jpg', 'name' => 'Chromebooks', 'slug' => 'chromebooks', 'description' => 'Chromebooks'],
        ];
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($laptops->id); // Calculate order for each child
            $laptops->children()->create($data);
        }
        $childrenData = [
            ['image' => 'test.jpg', 'name' => 'Tablets', 'slug' => 'tablets', 'description' => 'Tablets'],
            ['image' => 'test.jpg', 'name' => 'Headphones', 'slug' => 'headphones', 'description' => 'Headphones'],
            ['image' => 'test.jpg', 'name' => 'Cameras', 'slug' => 'cameras', 'description' => 'Cameras'],
            ['image' => 'test.jpg', 'name' => 'Video and Sound', 'slug' => 'video-sound', 'description' => 'Video and Sound'],
            ['image' => 'test.jpg', 'name' => 'Games and Accessories', 'slug' => 'games-accessories', 'description' => 'Games and Accessories'],


        ];
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($electronics->id); // Calculate order for each child
            $electronics->children()->create($data);
        }

        // House and Garden and subcategories
        $houseGarden = Category::create(['image' => 'test.jpg', 'name' => 'House and Garden', 'slug' => 'house-garden', 'description' => 'Products for house and garden', 'order' => Category::whereNull('parent_id')->max('order') + 1,]);
        $furniture = $houseGarden->children()->create(['image' => 'test.jpg', 'name' => 'Furniture', 'slug' => 'furniture', 'description' => 'All types of furniture', 'order' => $calculateOrder($houseGarden->id),]);

        $childrenData = [
            ['image' => 'test.jpg', 'name' => 'Chairs', 'slug' => 'chairs', 'description' => 'All types of chairs'],
            ['image' => 'test.jpg', 'name' => 'Sofas', 'slug' => 'sofas', 'description' => 'All types of sofas'],
            ['image' => 'test.jpg', 'name' => 'Tables', 'slug' => 'tables', 'description' => 'All types of tables'],
            ['image' => 'test.jpg', 'name' => 'Beds', 'slug' => 'beds', 'description' => 'All types of beds']
        ];

        // Create children for Furniture and assign unique order values
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($furniture->id); // Calculate order for each child
            $furniture->children()->create($data);
        }

        $gardenEquipment = $houseGarden->children()->create(['image' => 'test.jpg', 'name' => 'Garden Equipment', 'slug' => 'garden-equipment', 'description' => 'Equipment for garden work', 'order' => $calculateOrder($houseGarden->id),]);

        $childrenData = [
            ['image' => 'test.jpg', 'name' => 'Lawn Mowers', 'slug' => 'lawn-mowers', 'description' => 'Lawn mowers forountains'],
            ['image' => 'test.jpg', 'name' => 'Garden Tools', 'slug' => 'garden-tools', 'description' => 'Various garden tools'],
        ];

        // Create children for Garden Equipment and assign unique order values
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($gardenEquipment->id); // Calculate order for each child
            $gardenEquipment->children()->create($data);
        }

        // Create subcategories for House and Garden
        $childrenData = [
            ['image' => 'test.jpg', 'name' => 'Home Decor', 'slug' => 'home-decor', 'description' => 'Home decor'],
            ['image' => 'test.jpg', 'name' => 'Tools and DIY', 'slug' => 'tools-diy', 'description' => 'Tools and do-it-yourself products'],
        ];

        // Create children for House and Garden and assign unique order values
        foreach ($childrenData as $data) {
            $data['order'] = $calculateOrder($houseGarden->id); // Calculate order for each child
            $houseGarden->children()->create($data);
        }

        // // Lighting and subcategories
        // $lighting = Category::create(['image' => 'test.jpg', 'name' => 'Lighting', 'slug' => 'lighting', 'description' => 'All lighting products', 'order' => Category::whereNull('parent_id')->max('order') + 1,]);
        // $indoorLighting = $lighting->children()->create(['image' => 'test.jpg', 'name' => 'Indoor Lighting', 'slug' => 'indoor-lighting', 'description' => 'Lighting for indoors']);
        // $indoorLighting->children()->createMany([
        //     ['image' => 'test.jpg', 'name' => 'Ceiling Lights', 'slug' => 'ceiling-lights', 'description' => 'Indoor ceiling lights'],
        //     ['image' => 'test.jpg', 'name' => 'Wall Lights', 'slug' => 'wall-lights', 'description' => 'Indoor wall lights'],
        // ]);

        // $outdoorLighting = $lighting->children()->create(['image' => 'test.jpg', 'name' => 'Outdoor Lighting', 'slug' => 'outdoor-lighting', 'description' => 'Lighting for outdoors']);
        // $outdoorLighting->children()->createMany([
        //     ['image' => 'test.jpg', 'name' => 'Garden Lights', 'slug' => 'garden-lights', 'description' => 'Outdoor garden lights'],
        //     ['image' => 'test.jpg', 'name' => 'Street Lights', 'slug' => 'street-lights', 'description' => 'Outdoor street lights'],
        // ]);

        // $lighting->children()->createMany([
        //     ['image' => 'test.jpg', 'name' => 'Smart Lighting', 'slug' => 'smart-lighting', 'description' => 'Smart lighting solutions'],
        // ]);

        // // Home Appliances and subcategories
        // $homeAppliances = Category::create(['image' => 'test.jpg', 'name' => 'Home Appliances', 'slug' => 'home-appliances', 'description' => 'All home appliances', 'order' => Category::whereNull('parent_id')->max('order') + 1,]);
        // $refrigerators = $homeAppliances->children()->create(['image' => 'test.jpg', 'name' => 'Refrigerators', 'slug' => 'refrigerators', 'description' => 'Refrigerators and freezers']);
        // $refrigerators->children()->createMany([
        //     ['image' => 'test.jpg', 'name' => 'Single Door', 'slug' => 'single-door', 'description' => 'Single door refrigerators'],
        //     ['image' => 'test.jpg', 'name' => 'Double Door', 'slug' => 'double-door', 'description' => 'Double door refrigerators'],
        // ]);

        // $washingMachines = $homeAppliances->children()->create(['image' => 'test.jpg', 'name' => 'Washing Machines', 'slug' => 'washing-machines', 'description' => 'Washing machines and dryers']);
        // $washingMachines->children()->createMany([
        //     ['image' => 'test.jpg', 'name' => 'Front Load', 'slug' => 'front-load', 'description' => 'Front load washing machines'],
        //     ['image' => 'test.jpg', 'name' => 'Top Load', 'slug' => 'top-load', 'description' => 'Top load washing machines'],
        // ]);

        // $homeAppliances->children()->createMany([
        //     ['image' => 'test.jpg', 'name' => 'Kitchen Appliances', 'slug' => 'kitchen-appliances', 'description' => 'Small and large kitchen appliances'],
        //     ['image' => 'test.jpg', 'name' => 'Heating and Cooling', 'slug' => 'heating-cooling', 'description' => 'Heating and cooling appliances'],
        // ]);

        // // Automotive and Motorcycles and subcategories
        // $automotive = Category::create(['image' => 'test.jpg', 'name' => 'Automotive and Motorcycles', 'slug' => 'automotive-motorcycles', 'description' => 'Products for automotive and motorcycles', 'order' => Category::whereNull('parent_id')->max('order') + 1,]);
        // $carAccessories = $automotive->children()->create(['image' => 'test.jpg', 'name' => 'Car Accessories', 'slug' => 'car-accessories', 'description' => 'Accessories for cars']);
        // $carAccessories->children()->createMany([
        //     ['image' => 'test.jpg', 'name' => 'Car Covers', 'slug' => 'car-covers', 'description' => 'Covers for cars'],
        //     ['image' => 'test.jpg', 'name' => 'Car Audio', 'slug' => 'car-audio', 'description' => 'Audio systems for cars'],
        // ]);

        // $motorcycleAccessories = $automotive->children()->create(['image' => 'test.jpg', 'name' => 'Motorcycle Accessories', 'slug' => 'motorcycle-accessories', 'description' => 'Accessories for motorcycles']);
        // $motorcycleAccessories->children()->createMany([
        //     ['image' => 'test.jpg', 'name' => 'Helmets', 'slug' => 'helmets', 'description' => 'Motorcycle helmets'],
        //     ['image' => 'test.jpg', 'name' => 'Motorcycle Jackets', 'slug' => 'motorcycle-jackets', 'description' => 'Jackets for motorcycles'],
        // ]);

        // $automotive->children()->createMany([
        //     ['image' => 'test.jpg', 'name' => 'Tires and Wheels', 'slug' => 'tires-wheels', 'description' => 'Tires and wheels for vehicles'],
        //     ['image' => 'test.jpg', 'name' => 'Car Care', 'slug' => 'car-care', 'description' => 'Products for car maintenance and care'],
        // ]);
    }
}
