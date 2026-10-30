<?php

namespace Tests\Feature;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\DispatchingPointCoordinate;
use App\Models\DispatchingPointOrder;
use App\Models\Dren;
use App\Models\RepartitionSalle;
use App\Models\User;
use App\Services\MapDataService;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MapTest extends TestCase
{
    private CentreCorrection $jumele;
    private CentreCorrection $corrSeul;
    private CentreEcrit $ecritJumele;
    private CentreEcrit $ecritSeul;

    private function seedData(): void
    {
        $dren = Dren::create(['nom' => 'ANALAMANGA', 'province' => 'Antananarivo', 'is_chef_lieu_province' => true]);
        $cisco = Cisco::create(['dren_id' => $dren->id, 'nom' => 'CISCO A']);
        // Centre jumelé : écrit + correction, 12 salles x 25
        $this->jumele = CentreCorrection::create(['cisco_id' => $cisco->id, 'nom' => 'EPP X', 'type_examen' => 'CEPE', 'latitude' => -18.9, 'longitude' => 47.5]);
        $this->ecritJumele = CentreEcrit::create(['centre_correction_id' => $this->jumele->id, 'nom' => 'EPP X', 'type_examen' => 'CEPE']);
        for ($s = 1; $s <= 12; $s++) {
            RepartitionSalle::create(['centre_ecrit_id' => $this->ecritJumele->id, 'annee' => '2026', 'langue' => 'TOTAL', 'numero_salle' => $s, 'effectif' => 25,
                'axe_dispatching' => 'AXE 1', 'point_largage' => $s <= 6 ? 'Point B' : 'Point A']);
        }
        // Centre d'écrit seul, rattaché à un autre centre de correction
        $this->corrSeul = CentreCorrection::create(['cisco_id' => $cisco->id, 'nom' => 'CEG Z', 'type_examen' => 'BEPC']);
        $this->ecritSeul = CentreEcrit::create(['centre_correction_id' => $this->corrSeul->id, 'nom' => 'LYCEE Y', 'type_examen' => 'BEPC', 'latitude' => -21.45, 'longitude' => 47.08]);
        for ($s = 1; $s <= 4; $s++) {
            RepartitionSalle::create(['centre_ecrit_id' => $this->ecritSeul->id, 'annee' => '2026', 'langue' => 'TOTAL_BEPC', 'numero_salle' => $s, 'effectif' => 100,
                'axe_dispatching' => 'AXE 1', 'point_largage' => 'Point A']);
        }
    }

    public function test_les_donnees_de_la_carte_donnent_salles_candidats_role_et_agents(): void
    {
        $this->seedData();
        $out = app(MapDataService::class)->centres([], true);
        $byNom = collect($out['centres'])->keyBy('nom');

        $j = $byNom['EPP X'];
        $this->assertSame('ecrit_correction', $j['role']);
        $this->assertSame(12, $j['salles']);
        $this->assertSame(300, $j['candidates']);
        $this->assertEqualsWithDelta(-18.9, $j['lat'], 0.0001);
        $this->assertGreaterThan(0, $j['staff']['pendant']);
        $this->assertGreaterThan(0, $j['staff']['avant']);
        $this->assertGreaterThan(0, $j['staff']['apres']);

        $e = $byNom['LYCEE Y'];
        $this->assertSame('ecrit', $e['role']);
        $this->assertSame(4, $e['salles']);
        $this->assertSame(400, $e['candidates']);

        // CEG Z (correction seule) n'a pas de coordonnées : listé à part, jamais dessiné.
        $this->assertContains('CEG Z', array_column($out['without_coordinates'], 'nom'));
        $this->assertArrayNotHasKey('CEG Z', $byNom->all());

        // Sans droit « vacation », aucun agent dans la charge utile.
        $this->assertArrayNotHasKey('staff', app(MapDataService::class)->centres([], false)['centres'][0]);
    }

    public function test_trajet_du_dispatching_suit_l_ordre_enregistre_et_la_position_saisie(): void
    {
        $this->seedData();
        DispatchingPointOrder::create(['axe' => 'AXE 1', 'point' => 'Point B', 'position' => 1]);
        DispatchingPointOrder::create(['axe' => 'AXE 1', 'point' => 'Point A', 'position' => 2]);
        DispatchingPointCoordinate::create(['axe' => 'AXE 1', 'point' => 'Point B', 'latitude' => -19.5, 'longitude' => 47.0]);

        $d = app(MapDataService::class)->dispatching();
        $this->assertCount(1, $d['axes']);
        $pts = $d['axes'][0]['points'];
        $this->assertSame(['Point B', 'Point A'], array_column($pts, 'point'));
        $this->assertSame('saisi', $pts[0]['source']);
        $this->assertSame('centres', $pts[1]['source']); // centre de gravité des centres desservis
        $this->assertSame(150 + 400, $pts[1]['candidates']);
        $this->assertSame(6 + 4, $pts[1]['salles']);
        $this->assertSame(150, $pts[0]['candidates']);
        $this->assertNotNull($d['depart']['lat']);
    }

    public function test_droits_acces_et_edition(): void
    {
        $this->withoutVite();
        $this->seedData();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $log = User::factory()->create(['role' => User::ROLE_LOGISTIQUE]);

        $this->actingAs($admin)->get(route('carte.index'))->assertOk()->assertSee('Carte des centres');
        $this->actingAs($admin)->get(route('carte.coordonnees'))->assertOk();
        $this->actingAs($admin)->getJson(route('carte.data'))->assertOk()->assertJsonPath('with_staff', true);

        // Utilisateur simple : voit la carte, sans agents, ne peut pas modifier les coordonnées.
        $this->actingAs($user)->getJson(route('carte.data'))->assertOk()->assertJsonPath('with_staff', false);
        $this->actingAs($user)->get(route('carte.coordonnees'))->assertForbidden();
        $this->actingAs($user)->put(route('carte.coordonnees.save'), [])->assertForbidden();
        // La carte est ouverte à tous les comptes, y compris logistique (sans agents).
        $this->actingAs($log)->get(route('carte.index'))->assertOk();
        $this->actingAs($log)->getJson(route('carte.data'))->assertOk()->assertJsonPath('with_staff', false);
        $this->actingAs($log)->get(route('carte.coordonnees'))->assertForbidden();
    }

    public function test_saisie_des_coordonnees_valide_madagascar(): void
    {
        $this->withoutVite();
        $this->seedData();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->put(route('carte.coordonnees.save'), [
            'c' => ['correction' => [$this->corrSeul->id => ['lat' => '-18,5', 'lng' => '46,9']]],
            'p' => [['axe' => 'AXE 1', 'point' => 'Point A', 'lat' => '-20.1', 'lng' => '47.2']],
        ])->assertRedirect();
        $this->corrSeul->refresh();
        $this->assertEqualsWithDelta(-18.5, (float) $this->corrSeul->latitude, 0.0001);
        $this->assertSame(1, DispatchingPointCoordinate::count());

        // Latitude/longitude inversées (Paris) : refusé, base inchangée.
        $this->actingAs($admin)->put(route('carte.coordonnees.save'), [
            'c' => ['correction' => [$this->jumele->id => ['lat' => '48.85', 'lng' => '2.35']]],
        ])->assertSessionHasErrors();
        $this->jumele->refresh();
        $this->assertEqualsWithDelta(-18.9, (float) $this->jumele->latitude, 0.0001);

        // Case vide : efface.
        $this->actingAs($admin)->put(route('carte.coordonnees.save'), [
            'c' => ['correction' => [$this->corrSeul->id => ['lat' => '', 'lng' => '']]],
        ]);
        $this->assertNull($this->corrSeul->fresh()->latitude);
    }

    public function test_import_csv_applique_les_lignes_valides_et_refuse_les_autres(): void
    {
        $this->withoutVite();
        $this->seedData();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $csv = "type;id;axe;point;nom;latitude;longitude\n"
            ."correction;{$this->corrSeul->id};;;CEG Z;-17,5;49,4\n"
            ."ecrit;{$this->ecritJumele->id};;;EPP X;12.0;2.0\n"
            ."point;;AXE 1;Point A;Point A;-20.0;47.0\n";
        $file = UploadedFile::fake()->createWithContent('c.csv', $csv);

        $this->actingAs($admin)->post(route('carte.import'), ['fichier' => $file])->assertRedirect();
        $this->assertEqualsWithDelta(-17.5, (float) $this->corrSeul->fresh()->latitude, 0.0001);
        $this->assertNull($this->ecritJumele->fresh()->latitude);
        $this->assertSame(1, DispatchingPointCoordinate::where('point', 'Point A')->count());
    }

    public function test_validation_des_bornes(): void
    {
        $this->assertTrue(MapDataService::validCoordinates(-18.88, 47.5));
        $this->assertFalse(MapDataService::validCoordinates(47.5, -18.88));
        $this->assertFalse(MapDataService::validCoordinates('abc', 47));
    }
}
