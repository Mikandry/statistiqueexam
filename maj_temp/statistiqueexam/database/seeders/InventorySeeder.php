<?php

namespace Database\Seeders;

use App\Models\InventoryMaterial;
use App\Models\InventoryStockMovement;
use App\Models\InventorySupplier;
use App\Models\InventorySupplyOrder;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        InventorySupplier::factory()->count(5)->create();
        InventoryMaterial::factory()->count(18)->create();
        InventoryStockMovement::factory()->count(60)->create();
        InventorySupplyOrder::factory()->count(8)->create();
    }
}
