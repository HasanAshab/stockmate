<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        Warehouse::factory()->count(5)->create();

        $this->command->info('Created 5 warehouses');
    }
}
