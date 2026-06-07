<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $suppliers = Supplier::all();

        Product::factory()
            ->count(50)
            ->recycle([$categories, $suppliers])
            ->create();

        $this->command->info('Created 50 products');
    }
}
