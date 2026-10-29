<?php

namespace Tests\Feature;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\DispatchingPointOrder;
use App\Models\Dren;
use App\Models\RepartitionSalle;
use App\Models\User;
use Tests\TestCase;

class DispatchingOrderTest extends TestCase
{
    public function test_l_ordre_de_livraison_est_enregistre_et_retrouve(): void
    {
        $this->withoutVite()->withoutMiddleware();
        $dren = Dren::create(['nom' => 'D1']);
        $cisco = Cisco::create(['dren_id' => $dren->id, 'nom' => 'C1']);
        foreach (['ALPHA', 'BRAVO', 'CHARLIE'] as $i => $point) {
            $corr = CentreCorrection::create(['cisco_id' => $cisco->id, 'nom' => "COR $point", 'type_examen' => 'CEPE']);
            $ecrit = CentreEcrit::create(['centre_correction_id' => $corr->id, 'nom' => "ECR $point", 'type_examen' => 'CEPE']);
            RepartitionSalle::create([
                'centre_ecrit_id' => $ecrit->id, 'annee' => '2026', 'langue' => 'TOTAL', 'numero_salle' => 1, 'effectif' => 20,
                'axe_dispatching' => 'AXE 1', 'point_largage' => $point,
            ]);
        }
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Ordre voulu : CHARLIE, ALPHA, BRAVO (≠ ordre alphabétique)
        $this->actingAs($user)->post(route('repartition.export.dispatching.order.save'), [
            'order_axe' => 'AXE 1', 'point_order_positions' => ['CHARLIE' => 1, 'ALPHA' => 2, 'BRAVO' => 3],
        ])->assertRedirect();
        $this->assertSame(3, DispatchingPointOrder::count());

        // Réouverture sans paramètre : l'ordre enregistré est restitué
        $resp = $this->actingAs($user)->get(route('repartition.export.dispatching.preview', ['annee' => '2026', 'type_examen' => 'CEPE']));
        $resp->assertOk();
        $order = $resp->viewData('dispatchingOrder');
        $this->assertSame(['CHARLIE', 'ALPHA', 'BRAVO'], $order['ordered_points']->all());

        // Une mise à jour remplace sans dupliquer
        $this->actingAs($user)->post(route('repartition.export.dispatching.order.save'), [
            'order_axe' => 'AXE 1', 'point_order_positions' => ['BRAVO' => 1, 'CHARLIE' => 2, 'ALPHA' => 3],
        ]);
        $this->assertSame(3, DispatchingPointOrder::count());
    }
}
