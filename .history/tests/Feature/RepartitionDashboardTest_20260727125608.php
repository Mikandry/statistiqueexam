<?php

namespace Tests\Feature;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\RepartitionSalle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepartitionDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_uses_the_latest_selected_session_for_stats_by_default(): void
    {
        $dren = Dren::create(['nom' => 'DREN Test']);
        $cisco = Cisco::create(['dren_id' => $dren->id, 'nom' => 'CISCO Test']);
        $centreCorrection = CentreCorrection::create([
            'cisco_id' => $cisco->id,
            'nom' => 'Centre correction A',
            'type_examen' => 'BEPC',
        ]);
        $centreEcrit = CentreEcrit::create([
            'centre_correction_id' => $centreCorrection->id,
            'nom' => 'Centre écrit A',
            'type_examen' => 'BEPC',
        ]);

        RepartitionSalle::create([
            'centre_ecrit_id' => $centreEcrit->id,
            'annee' => '2024-2025',
            'langue' => 'Anglais',
            'numero_salle' => 1,
            'effectif' => 10,
            'axe_dispatching' => 'A1',
            'point_largage' => 'P1',
        ]);

        RepartitionSalle::create([
            'centre_ecrit_id' => $centreEcrit->id,
            'annee' => '2025-2026',
            'langue' => 'Anglais',
            'numero_salle' => 2,
            'effectif' => 20,
            'axe_dispatching' => 'A1',
            'point_largage' => 'P1',
        ]);

        $response = $this->get(route('repartition.dashboard'));

        $response->assertOk();
        $response->assertViewHas('globalStats', function (array $stats) {
            return $stats['total_candidats'] === 20;
        });
    }
}
