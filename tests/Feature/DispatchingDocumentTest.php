<?php

namespace Tests\Feature;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\DispatchingParcel;
use App\Models\Dren;
use App\Models\RepartitionSalle;
use App\Models\User;
use Tests\TestCase;

class DispatchingDocumentTest extends TestCase
{
    private array $ecrit = [];

    private function admin(): User
    {
        $this->withoutVite();

        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    private function seedData(): void
    {
        $dren = Dren::create(['nom' => 'ANALAMANGA', 'province' => 'Antananarivo']);
        $cisco = Cisco::create(['dren_id' => $dren->id, 'nom' => 'CISCO A']);
        foreach ([['EPP X', 'Point A', 12], ['EPP W', 'Point A', 5], ['EPP V', 'Point B', 3]] as [$nom, $point, $salles]) {
            $corr = CentreCorrection::create(['cisco_id' => $cisco->id, 'nom' => $nom, 'type_examen' => 'CEPE']);
            $e = CentreEcrit::create(['centre_correction_id' => $corr->id, 'nom' => $nom, 'type_examen' => 'CEPE']);
            for ($s = 1; $s <= $salles; $s++) {
                RepartitionSalle::create(['centre_ecrit_id' => $e->id, 'annee' => '2026', 'langue' => 'TOTAL', 'numero_salle' => $s, 'effectif' => 25,
                    'axe_dispatching' => 'AXE 1', 'point_largage' => $point]);
            }
            $this->ecrit[$nom] = $e;
        }
    }

    public function test_saisie_des_colis_et_arretage_par_point(): void
    {
        $admin = $this->admin();
        $this->seedData();

        $this->actingAs($admin)->get(route('repartition.dispatching.colis'))->assertOk()->assertSee('Point A')->assertSee('Arrêtage du point');

        $this->actingAs($admin)->put(route('repartition.dispatching.colis.save'), [
            'colis' => [$this->ecrit['EPP X']->id => 4, $this->ecrit['EPP W']->id => 2],
            'annee_row' => [$this->ecrit['EPP X']->id => '2026', $this->ecrit['EPP W']->id => '2026'],
        ])->assertRedirect();
        $this->assertSame(6, (int) DispatchingParcel::sum('colis'));

        // Valeur négative refusée ; case vidée supprime la saisie.
        $this->actingAs($admin)->put(route('repartition.dispatching.colis.save'), [
            'colis' => [$this->ecrit['EPP X']->id => -1], 'annee_row' => [$this->ecrit['EPP X']->id => '2026'],
        ])->assertSessionHasErrors();
        $this->actingAs($admin)->put(route('repartition.dispatching.colis.save'), [
            'colis' => [$this->ecrit['EPP W']->id => ''], 'annee_row' => [$this->ecrit['EPP W']->id => '2026'],
        ]);
        $this->assertSame(4, (int) DispatchingParcel::sum('colis'));
    }

    public function test_pdf_fiche_et_accuse_et_word(): void
    {
        $admin = $this->admin();
        $this->seedData();
        DispatchingParcel::create(['centre_ecrit_id' => $this->ecrit['EPP X']->id, 'annee' => '2026', 'colis' => 4]);
        DispatchingParcel::create(['centre_ecrit_id' => $this->ecrit['EPP W']->id, 'annee' => '2026', 'colis' => 2]);

        $r = $this->actingAs($admin)->get(route('repartition.dispatching.document.pdf', ['doc' => 'fiche', 'axe' => 'AXE 1']));
        $r->assertOk();
        $this->assertStringStartsWith('%PDF', $r->getContent());

        $r = $this->actingAs($admin)->get(route('repartition.dispatching.document.pdf', ['doc' => 'accuse', 'axe' => 'AXE 1', 'point' => 'Point A']));
        $r->assertOk();

        $r = $this->actingAs($admin)->get(route('repartition.dispatching.document.word', ['doc' => 'fiche', 'axe' => 'AXE 1', 'remettant' => 'RAKOTO Jean']));
        $r->assertOk();
        $file = $r->baseResponse->getFile()->getPathname();
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($file) === true);
        $xml = $zip->getFromName('word/document.xml');
        $this->assertStringContainsString('DISPATCHING DE SUJET DU CEPE 2026', $xml);
        $this->assertStringContainsString('POINT DE LARGAGE : Point A', $xml);
        $this->assertStringContainsString('Colis : 6', $xml);       // 4 + 2 : arrêtage du point A
        $this->assertStringContainsString('Colis : ........................', $xml); // point B : rien saisi
        $footers = '';
        for ($i = 1; $i <= 4; $i++) {
            $footers .= (string) $zip->getFromName("word/footer{$i}.xml");
        }
        $this->assertStringContainsString('Le remettant', $footers);
        $this->assertStringContainsString("Les forces de l'ordre", $footers);
        $this->assertStringContainsString('RAKOTO Jean', $footers);
        $zip->close();

        $this->actingAs($admin)->get(route('repartition.dispatching.document.pdf', ['doc' => 'fiche', 'axe' => 'INCONNU']))->assertSessionHasErrors();
    }

    public function test_accuse_a_une_page_par_point_et_pied_de_page_dedie(): void
    {
        $admin = $this->admin();
        $this->seedData();
        $r = $this->actingAs($admin)->get(route('repartition.dispatching.document.word', ['doc' => 'accuse', 'axe' => 'AXE 1']));
        $zip = new \ZipArchive();
        $zip->open($r->baseResponse->getFile()->getPathname());
        $xml = $zip->getFromName('word/document.xml');
        $this->assertSame(2, substr_count($xml, 'ACCUSÉ DE RÉCEPTION'));   // point A et point B
        $footers = '';
        for ($i = 1; $i <= 4; $i++) {
            $footers .= (string) $zip->getFromName("word/footer{$i}.xml");
        }
        $this->assertStringContainsString('Sécurité (conservation des colis)', $footers);
        $this->assertStringContainsString('Les agents du MEN réceptionnaires', $footers);
        $this->assertStringNotContainsString("Les forces de l'ordre", $footers);
    }
}
