<?php

namespace Tests\Feature;

use App\Models\InventoryMaterial;
use App\Models\InventoryStockMovement;
use App\Models\User;
use Tests\TestCase;

class InventoryTraceabilityTest extends TestCase
{
    private function material(int $stock = 10): InventoryMaterial
    {
        return InventoryMaterial::create([
            'code' => 'M1', 'name' => 'Ramette A4', 'category' => 'Papeterie', 'unit' => 'ramette',
            'initial_quantity' => $stock, 'available_quantity' => $stock, 'minimum_threshold' => 2, 'unit_price' => 1000, 'condition' => 'Bon',
        ]);
    }

    private function move(User $u, InventoryMaterial $m, array $over = [])
    {
        return $this->actingAs($u)->post(route('inventory.movements.store'), array_merge([
            'material_id' => $m->id, 'movement_date' => '2026-10-01', 'movement_type' => 'sortie',
            'requested_quantity' => 3, 'granted_quantity' => 3,
        ], $over));
    }

    public function test_mouvements_coherents_et_rapprochement(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $m = $this->material(10);

        $this->move($user, $m, ['voucher_number' => 'BS-1']);                                  // 10 -> 7
        $this->move($user, $m, ['movement_type' => 'retour', 'granted_quantity' => 1, 'requested_quantity' => 1, 'voucher_number' => 'R-1']); // 8
        $this->move($user, $m, ['movement_type' => 'entree', 'granted_quantity' => 5, 'requested_quantity' => 5, 'voucher_number' => 'BL-1']); // 13

        $m->refresh();
        $this->assertSame(13, $m->available_quantity);
        $rec = $m->reconciliation();
        $this->assertSame(13, $rec['expected']);
        $this->assertSame(0, $rec['gap']);
        $this->assertSame(13, $rec['last_stock_after']);

        $this->actingAs($user)->get(route('inventory.index'))->assertOk();
        $this->actingAs($user)->get(route('inventory.materials.history', $m))->assertOk();
        $this->actingAs($user)->get(route('inventory.reconciliation'))->assertOk();
    }

    public function test_les_doublons_et_les_sorties_excessives_sont_refuses(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $m = $this->material(10);

        // Même formulaire envoyé deux fois (même jeton) : un seul mouvement
        $this->move($user, $m, ['request_token' => 'tok-1', 'voucher_number' => 'BS-1']);
        $this->move($user, $m, ['request_token' => 'tok-1', 'voucher_number' => 'BS-1']);
        $this->assertSame(1, InventoryStockMovement::count());
        $this->assertSame(7, $m->fresh()->available_quantity);

        // Même numéro de pièce réutilisé pour le même matériel et le même type
        $this->move($user, $m, ['request_token' => 'tok-2', 'voucher_number' => 'BS-1'])->assertSessionHasErrors('voucher_number');
        $this->assertSame(1, InventoryStockMovement::count());

        // Stock insuffisant, accordé > demandé
        $this->move($user, $m, ['voucher_number' => 'BS-2', 'requested_quantity' => 50, 'granted_quantity' => 50])->assertSessionHasErrors('granted_quantity');
        $this->move($user, $m, ['voucher_number' => 'BS-3', 'requested_quantity' => 1, 'granted_quantity' => 2])->assertSessionHasErrors('granted_quantity');
        $this->assertSame(7, $m->fresh()->available_quantity);
    }

    public function test_le_stock_ne_se_modifie_pas_sans_mouvement(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $m = $this->material(10);

        $this->actingAs($user)->put(route('inventory.materials.update', $m), [
            'code' => 'M1', 'name' => 'Ramette A4 bis', 'category' => 'Papeterie', 'unit' => 'ramette',
            'initial_quantity' => 10, 'available_quantity' => 999, 'minimum_threshold' => 2, 'unit_price' => 1000, 'condition' => 'Bon',
        ]);
        $m->refresh();
        $this->assertSame('Ramette A4 bis', $m->name);
        $this->assertSame(10, $m->available_quantity);
        $this->assertSame(0, $m->reconciliation()['gap']);
    }

    public function test_acces_reserve_a_la_logistique_et_a_l_admin(): void
    {
        $this->withoutVite();
        $m = $this->material();
        $plain = User::factory()->create(['role' => User::ROLE_USER]);
        $this->actingAs($plain)->get(route('inventory.reconciliation'))->assertStatus(403);
        $this->actingAs($plain)->get(route('inventory.materials.history', $m))->assertStatus(403);
    }
}
