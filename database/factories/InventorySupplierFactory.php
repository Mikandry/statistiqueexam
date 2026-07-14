<?php

namespace Database\Factories;

use App\Models\InventorySupplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventorySupplierFactory extends Factory
{
    protected $model = InventorySupplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'supplied_products' => $this->faker->words(6, true),
        ];
    }
}
