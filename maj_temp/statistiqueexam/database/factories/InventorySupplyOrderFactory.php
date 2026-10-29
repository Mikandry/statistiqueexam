<?php

namespace Database\Factories;

use App\Models\InventoryMaterial;
use App\Models\InventorySupplyOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventorySupplyOrderFactory extends Factory
{
    protected $model = InventorySupplyOrder::class;

    public function definition(): array
    {
        $material = InventoryMaterial::query()->inRandomOrder()->first();

        return [
            'material_id' => $material?->id,
            'order_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'remaining_quantity' => (int) ($material?->available_quantity ?? 0),
            'quantity_to_order' => $this->faker->numberBetween(20, 500),
            'status' => $this->faker->randomElement(['demandee', 'validee', 'livree']),
            'observation' => null,
        ];
    }
}
