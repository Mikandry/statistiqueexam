<?php

namespace Database\Factories;

use App\Models\InventoryMaterial;
use App\Models\InventorySupplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryMaterialFactory extends Factory
{
    protected $model = InventoryMaterial::class;

    public function definition(): array
    {
        $initial = $this->faker->numberBetween(50, 5000);
        $available = $this->faker->numberBetween(0, $initial);
        $price = $this->faker->randomFloat(2, 500, 25000);

        return [
            'supplier_id' => InventorySupplier::query()->inRandomOrder()->value('id'),
            'code' => strtoupper($this->faker->unique()->bothify('MAT-####')),
            'name' => $this->faker->randomElement(['Copies doubles', 'Enveloppes', 'Stylos rouges', 'Registres', 'Chemises cartonnées', 'Scellés']),
            'category' => $this->faker->randomElement(['Papeterie', 'Sécurité', 'Bureau', 'Impression']),
            'description' => $this->faker->sentence(),
            'unit' => $this->faker->randomElement(['paquet', 'unité', 'carton', 'boîte']),
            'initial_quantity' => $initial,
            'available_quantity' => $available,
            'minimum_threshold' => $this->faker->numberBetween(10, 150),
            'unit_price' => $price,
            'total_value' => $available * $price,
            'acquired_at' => $this->faker->date(),
            'condition' => 'Bon',
            'observations' => null,
        ];
    }
}
