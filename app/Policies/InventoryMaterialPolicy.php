<?php

namespace App\Policies;

use App\Models\InventoryMaterial;
use App\Models\User;

class InventoryMaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessLogistics();
    }

    public function create(User $user): bool
    {
        return $user->canAccessLogistics();
    }

    public function update(User $user, InventoryMaterial $inventoryMaterial): bool
    {
        return $user->canAccessLogistics();
    }

    public function delete(User $user, InventoryMaterial $inventoryMaterial): bool
    {
        return $user->isAdmin();
    }
}
