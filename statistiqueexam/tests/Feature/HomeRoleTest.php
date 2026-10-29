<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class HomeRoleTest extends TestCase
{
    public function test_l_accueil_depend_du_role(): void
    {
        $this->withoutVite();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $this->actingAs($admin)->get('/home')->assertOk()->assertSee('Vacation 2026')->assertSee('Comptabilité des matières')->assertSee('Administrateur national');

        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $this->actingAs($user)->get('/home')->assertOk()->assertDontSee('Vacation 2026')->assertDontSee('Comptabilité des matières')->assertSee('Mon dossier personnel');

        $rh = User::factory()->create(['role' => User::ROLE_RH]);
        $this->actingAs($rh)->get('/home')->assertOk()->assertSee('Ressources humaines')->assertDontSee('Vacation 2026');

        $log = User::factory()->create(['role' => User::ROLE_LOGISTIQUE]);
        $this->actingAs($log)->get('/home')->assertRedirect(route('repartition.logistique.bepc-copies'));
    }

    public function test_les_routes_restent_protegees_meme_par_url_directe(): void
    {
        $this->withoutVite();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $rh = User::factory()->create(['role' => User::ROLE_RH]);

        foreach (['vacation2026.besoins.dashboard', 'hr.activity.index', 'admin.references.index'] as $route) {
            $this->actingAs($user)->get(route($route))->assertStatus(403);
            $this->actingAs($rh)->get(route($route))->assertStatus(403);
        }
        foreach (['inventory.index', 'inventory.reconciliation'] as $route) {
            $this->actingAs($user)->get(route($route))->assertStatus(403);
            $this->actingAs($rh)->get(route($route))->assertStatus(403);
        }
    }
}
