<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Create the first product
        $product1 = Product::create([
            'category_id' => 1, // Assuming category ID is 1
            'name' => 'Product 1',
            'slug' => 'product-1',
            'description' => 'Short description for product 1',
            'long_description' => 'This is a detailed long description for product 1.',
            'status' => true,
            'featured' => false, // Set featured as needed
            'thumbnail_image_path' => 'https://via.placeholder.com/150', // Thumbnail image
            'created_by' => 1, // Assuming admin user with ID 1
            'updated_by' => 1, // Assuming admin user with ID 1
        ]);

        // Create options for the first product
        $option1 = ProductOption::create([
            'product_id' => $product1->id,
            'name' => 'Color',
        ]);

        $option2 = ProductOption::create([
            'product_id' => $product1->id,
            'name' => 'Size',
        ]);

        $option3 = ProductOption::create([
            'product_id' => $product1->id,
            'name' => 'Material',
        ]);

        // Create values for 'Color' option
        ProductOptionValue::create([
            'product_option_id' => $option1->id,
            'value' => 'Red',
            'quantity' => 100,
            'price' => 110.00,
            'cost' => 60.00,
            'discount' => 10.00,
            'weight' => 0.5,
            'sku' => 'SKU12345-RED',
            'stock' => 50,
            'status' => true,
            'main_image_path' => 'https://via.placeholder.com/150/FF0000', // Image for Red color
        ]);
        ProductOptionValue::create([
            'product_option_id' => $option1->id,
            'value' => 'Blue',
            'quantity' => 100,
            'price' => 110.00,
            'cost' => 60.00,
            'discount' => 10.00,
            'weight' => 0.5,
            'sku' => 'SKU12345-BLUE',
            'stock' => 50,
            'status' => true,
            'main_image_path' => 'https://via.placeholder.com/150/0000FF', // Image for Blue color
        ]);
        ProductOptionValue::create([
            'product_option_id' => $option1->id,
            'value' => 'Green',
            'quantity' => 100,
            'price' => 110.00,
            'cost' => 60.00,
            'discount' => 10.00,
            'weight' => 0.5,
            'sku' => 'SKU12345-GREEN',
            'stock' => 50,
            'status' => true,
            'main_image_path' => 'https://via.placeholder.com/150/008000', // Image for Green color
        ]);

        // Create values for 'Size' option
        ProductOptionValue::create([
            'product_option_id' => $option2->id,
            'value' => 'Small',
            'quantity' => 100,
            'price' => 100.00,
            'cost' => 55.00,
            'discount' => 5.00,
            'weight' => 0.3,
            'sku' => 'SKU12345-S',
            'stock' => 50,
            'status' => true,
            'main_image_path' => 'https://via.placeholder.com/150',
        ]);
        ProductOptionValue::create([
            'product_option_id' => $option2->id,
            'value' => 'Medium',
            'quantity' => 100,
            'price' => 105.00,
            'cost' => 60.00,
            'discount' => 5.00,
            'weight' => 0.4,
            'sku' => 'SKU12345-M',
            'stock' => 50,
            'status' => true,
            'main_image_path' => 'https://via.placeholder.com/150',
        ]);
        ProductOptionValue::create([
            'product_option_id' => $option2->id,
            'value' => 'Large',
            'quantity' => 100,
            'price' => 110.00,
            'cost' => 65.00,
            'discount' => 5.00,
            'weight' => 0.5,
            'sku' => 'SKU12345-L',
            'stock' => 50,
            'status' => true,
            'main_image_path' => 'https://via.placeholder.com/150',
        ]);

        // Create values for 'Material' option
        ProductOptionValue::create([
            'product_option_id' => $option3->id,
            'value' => 'Cotton',
            'quantity' => 100,
            'price' => 115.00,
            'cost' => 70.00,
            'discount' => 5.00,
            'weight' => 0.6,
            'sku' => 'SKU12345-COTTON',
            'stock' => 50,
            'status' => true,
            'main_image_path' => 'https://via.placeholder.com/150',
        ]);
        ProductOptionValue::create([
            'product_option_id' => $option3->id,
            'value' => 'Leather',
            'quantity' => 100,
            'price' => 125.00,
            'cost' => 75.00,
            'discount' => 5.00,
            'weight' => 0.7,
            'sku' => 'SKU12345-LEATHER',
            'stock' => 50,
            'status' => true,
            'main_image_path' => 'https://via.placeholder.com/150',
        ]);
        ProductOptionValue::create([
            'product_option_id' => $option3->id,
            'value' => 'Wool',
            'quantity' => 100,
            'price' => 130.00,
            'cost' => 80.00,
            'discount' => 5.00,
            'weight' => 0.8,
            'sku' => 'SKU12345-WOOL',
            'stock' => 50,
            'status' => true,
            'main_image_path' => 'https://via.placeholder.com/150',
        ]);

        // Create the second product
        $product2 = Product::create([
            'category_id' => 1,
            'name' => 'Product 2',
            'slug' => 'product-2',
            'description' => 'Short description for product 2',
            'long_description' => 'This is a detailed long description for product 2.',
            'status' => true,
            'featured' => true,
            'thumbnail_image_path' => 'https://via.placeholder.com/150',
            'created_by' => 1, // Assuming admin user with ID 1
            'updated_by' => 1, // Assuming admin user with ID 1
        ]);

        // Repeat the process for options and values for product2
        $option4 = ProductOption::create([
            'product_id' => $product2->id,
            'name' => 'Color',
        ]);

        $option5 = ProductOption::create([
            'product_id' => $product2->id,
            'name' => 'Size',
        ]);

        $option6 = ProductOption::create([
            'product_id' => $product2->id,
            'name' => 'Material',
        ]);

        // Repeat values for 'Color', 'Size', 'Material' options for product2
        // (same structure as for product1)

        // For brevity, I will not repeat all the entries here, but you can follow the same structure.
    }
}
