<?php

namespace Database\Seeders;

use App\Models\ProductOption;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // create product options color and size
        $productOption = ProductOption::create([
            'name' => 'color',
        ]);

        $values = ['red', 'green', 'blue', 'yellow', 'black', 'white'];

        foreach ($values as $value) {
            $productOption->values()->create([
                'value' => $value,
            ]);
        }

        $productOption = ProductOption::create([
            'name' => 'size',
        ]);

        $values = ['S', 'M', 'L', 'XL', 'XXL'];

        foreach ($values as $value) {
            $productOption->values()->create([
                'value' => $value,
            ]);
        }

        $productOption = ProductOption::create([
            'name' => 'Material',
        ]);

        $values = ['Cotton', 'Polyester', 'Denim', 'Leather', 'Wool', 'Nylon', 'Silk', 'Rayon', 'Linen'];

        foreach ($values as $value) {
            $productOption->values()->create([
                'value' => $value,
            ]);
        }
    }
}
