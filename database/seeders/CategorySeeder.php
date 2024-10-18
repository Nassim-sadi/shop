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
        $electronics = Category::create(['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'All electronics']);
        $smartphones = $electronics->children()->create(['name' => 'Smartphones', 'slug' => 'smartphones', 'description' => 'All smartphones']);
        $smartphones->children()->createMany([
            ['name' => 'Android Phones', 'slug' => 'android-phones', 'description' => 'Android smartphones'],
            ['name' => 'iPhones', 'slug' => 'iphones', 'description' => 'Apple iPhones'],
        ]);

        $laptops = $electronics->children()->create(['name' => 'Laptops', 'slug' => 'laptops', 'description' => 'All laptops']);
        $laptops->children()->createMany([
            ['name' => 'Gaming Laptops', 'slug' => 'gaming-laptops', 'description' => 'Laptops for gaming'],
            ['name' => 'Business Laptops', 'slug' => 'business-laptops', 'description' => 'Laptops for business'],
        ]);

        $electronics->children()->createMany([
            ['name' => 'Tablets', 'slug' => 'tablets', 'description' => 'All tablets'],
            ['name' => 'Headphones', 'slug' => 'headphones', 'description' => 'All headphones'],
            ['name' => 'Cameras', 'slug' => 'cameras', 'description' => 'All cameras'],
            ['name' => 'Video and Sound', 'slug' => 'video-sound', 'description' => 'Video and sound systems'],
            ['name' => 'Games and Accessories', 'slug' => 'games-accessories', 'description' => 'Video games and accessories'],
        ]);

        // House and Garden and subcategories
        $houseGarden = Category::create(['name' => 'House and Garden', 'slug' => 'house-garden', 'description' => 'Products for house and garden']);
        $furniture = $houseGarden->children()->create(['name' => 'Furniture', 'slug' => 'furniture', 'description' => 'All types of furniture']);
        $furniture->children()->createMany([
            ['name' => 'Chairs', 'slug' => 'chairs', 'description' => 'All types of chairs'],
            ['name' => 'Sofas', 'slug' => 'sofas', 'description' => 'All types of sofas'],
            ['name' => 'Tables', 'slug' => 'tables', 'description' => 'All types of tables'],
        ]);

        $gardenEquipment = $houseGarden->children()->create(['name' => 'Garden Equipment', 'slug' => 'garden-equipment', 'description' => 'Equipment for garden work']);
        $gardenEquipment->children()->createMany([
            ['name' => 'Lawn Mowers', 'slug' => 'lawn-mowers', 'description' => 'Lawn mowers for gardens'],
            ['name' => 'Garden Tools', 'slug' => 'garden-tools', 'description' => 'Various garden tools'],
        ]);

        $houseGarden->children()->createMany([
            ['name' => 'Decor', 'slug' => 'decor', 'description' => 'Home decor'],
            ['name' => 'Tools and DIY', 'slug' => 'tools-diy', 'description' => 'Tools and do-it-yourself products'],
        ]);

        // Lighting and subcategories
        $lighting = Category::create(['name' => 'Lighting', 'slug' => 'lighting', 'description' => 'All lighting products']);
        $indoorLighting = $lighting->children()->create(['name' => 'Indoor Lighting', 'slug' => 'indoor-lighting', 'description' => 'Lighting for indoors']);
        $indoorLighting->children()->createMany([
            ['name' => 'Ceiling Lights', 'slug' => 'ceiling-lights', 'description' => 'Indoor ceiling lights'],
            ['name' => 'Wall Lights', 'slug' => 'wall-lights', 'description' => 'Indoor wall lights'],
        ]);

        $outdoorLighting = $lighting->children()->create(['name' => 'Outdoor Lighting', 'slug' => 'outdoor-lighting', 'description' => 'Lighting for outdoors']);
        $outdoorLighting->children()->createMany([
            ['name' => 'Garden Lights', 'slug' => 'garden-lights', 'description' => 'Outdoor garden lights'],
            ['name' => 'Street Lights', 'slug' => 'street-lights', 'description' => 'Outdoor street lights'],
        ]);

        $lighting->children()->createMany([
            ['name' => 'Smart Lighting', 'slug' => 'smart-lighting', 'description' => 'Smart lighting solutions'],
        ]);

        // Home Appliances and subcategories
        $homeAppliances = Category::create(['name' => 'Home Appliances', 'slug' => 'home-appliances', 'description' => 'All home appliances']);
        $refrigerators = $homeAppliances->children()->create(['name' => 'Refrigerators', 'slug' => 'refrigerators', 'description' => 'Refrigerators and freezers']);
        $refrigerators->children()->createMany([
            ['name' => 'Single Door', 'slug' => 'single-door', 'description' => 'Single door refrigerators'],
            ['name' => 'Double Door', 'slug' => 'double-door', 'description' => 'Double door refrigerators'],
        ]);

        $washingMachines = $homeAppliances->children()->create(['name' => 'Washing Machines', 'slug' => 'washing-machines', 'description' => 'Washing machines and dryers']);
        $washingMachines->children()->createMany([
            ['name' => 'Front Load', 'slug' => 'front-load', 'description' => 'Front load washing machines'],
            ['name' => 'Top Load', 'slug' => 'top-load', 'description' => 'Top load washing machines'],
        ]);

        $homeAppliances->children()->createMany([
            ['name' => 'Kitchen Appliances', 'slug' => 'kitchen-appliances', 'description' => 'Small and large kitchen appliances'],
            ['name' => 'Heating and Cooling', 'slug' => 'heating-cooling', 'description' => 'Heating and cooling appliances'],
        ]);

        // Automotive and Motorcycles and subcategories
        $automotive = Category::create(['name' => 'Automotive and Motorcycles', 'slug' => 'automotive-motorcycles', 'description' => 'Products for automotive and motorcycles']);
        $carAccessories = $automotive->children()->create(['name' => 'Car Accessories', 'slug' => 'car-accessories', 'description' => 'Accessories for cars']);
        $carAccessories->children()->createMany([
            ['name' => 'Car Covers', 'slug' => 'car-covers', 'description' => 'Covers for cars'],
            ['name' => 'Car Audio', 'slug' => 'car-audio', 'description' => 'Audio systems for cars'],
        ]);

        $motorcycleAccessories = $automotive->children()->create(['name' => 'Motorcycle Accessories', 'slug' => 'motorcycle-accessories', 'description' => 'Accessories for motorcycles']);
        $motorcycleAccessories->children()->createMany([
            ['name' => 'Helmets', 'slug' => 'helmets', 'description' => 'Motorcycle helmets'],
            ['name' => 'Motorcycle Jackets', 'slug' => 'motorcycle-jackets', 'description' => 'Jackets for motorcycles'],
        ]);

        $automotive->children()->createMany([
            ['name' => 'Tires and Wheels', 'slug' => 'tires-wheels', 'description' => 'Tires and wheels for vehicles'],
            ['name' => 'Car Care', 'slug' => 'car-care', 'description' => 'Products for car maintenance and care'],
        ]);
    }
}
