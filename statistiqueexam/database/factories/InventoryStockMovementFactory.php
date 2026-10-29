<?php

namespace Database\Factories;

use App\Models\InventoryMaterial;
use App\Models\InventoryStockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryStockMovementFactory extends Factory
{
    protected $model = InventoryStockMovement::class;

    public function definition(): array
    {
        $material = InventoryMaterial::query()->inRandomOrder()->first();
        $before = (int) ($material?->available_quantity ?? 100);
        $granted = $this->faker->numberBetween(1, max(1, min(50, $before)));

        return [
            'material_id' => $material?->id,
            'movement_date' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'movement_type' => InventoryStockMovement::TYPE_OUT,
            'voucher_number' => strtoupper($this->faker->bothify('BS-####')),
            'requester_name' => $this->faker->name(),
            'requesting_service' => $this->faker->randomElement(['Organisation des examens', 'Logistique', 'Secrétariat', 'Statistiques']),
            'function' => $this->faker->jobTitle(),
            'requested_quantity' => $granted,
            'granted_quantity' => $granted,
            'stock_before' => $before,
            'stock_after' => max(0, $before - $granted),
            'reason' => $this->faker->sentence(),
            'signature' => $this->faker->name(),
            'observation' => null,
        ];
    }
}
