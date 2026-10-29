<?php

namespace Tests\Feature;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\RepartitionSalle;
use App\Models\User;
use App\Models\Vacation2026Bareme;
use App\Services\VacationBesoinsService;
use Tests\TestCase;

class VacationBesoinsTest extends TestCase
{
    private function seedData(): void
    {
        $dren = Dren::create(['nom' => 'ANALAMANGA', 'province' => 'Antananarivo', 'is_chef_lieu_province' => true]);
        $cisco = Cisco::create(['dren_id' => $dren->id, 'nom' => 'CISCO A']);
        // Centre jumelé CEPE : 12 salles de 25 = 300 candidats
        $corr = CentreCorrection::create(['cisco_id' => $cisco->id, 'nom' => 'EPP X', 'type_examen' => 'CEPE']);
        $ecrit = CentreEcrit::create(['centre_correction_id' => $corr->id, 'nom' => 'EPP X', 'type_examen' => 'CEPE']);
        for ($s = 1; $s <= 12; $s++) {
            RepartitionSalle::create(['centre_ecrit_id' => $ecrit->id, 'annee' => '2026', 'langue' => 'TOTAL', 'numero_salle' => $s, 'effectif' => 25]);
        }
        // Centre d'écrit seul BEPC : 4 salles, TOTAL_BEPC 100 chacune
        $corrB = CentreCorrection::create(['cisco_id' => $cisco->id, 'nom' => 'CEG Y', 'type_examen' => 'BEPC']);
        $ecritB = CentreEcrit::create(['centre_correction_id' => $corrB->id, 'nom' => 'CEG Y', 'type_examen' => 'BEPC']);
        for ($s = 1; $s <= 4; $s++) {
            RepartitionSalle::create(['centre_ecrit_id' => $ecritB->id, 'annee' => '2026', 'langue' => 'TOTAL_BEPC', 'numero_salle' => $s, 'effectif' => 100]);
        }
    }

    public function test_calculs_du_decret(): void
    {
        $this->seedData();
        $lines = app(VacationBesoinsService::class)->lines();

        $get = fn ($code, $role) => $lines->where('activity_code', $code)->filter(fn ($l) => str_starts_with($l['role'], $role))->sum('persons');

        // Centre CEPE jumelé, 12 salles, 300 candidats
        $this->assertSame(24, $get('CEPE.CENTRE_ECRIT.SESSION_ECRITE', 'Surveillants de salle'));
        $this->assertSame(3, $get('CEPE.CENTRE_ECRIT.SESSION_ECRITE', 'Surveillants de cour')); // 2 + ceil(2/5)
        $this->assertSame(12, $get('CEPE.CENTRE_ECRIT.SESSION_ECRITE', 'Secrétaires'));
        $this->assertSame(2, $get('CEPE.CENTRE_ECRIT.SECRETARIAT_AVANT', 'Agents de secrétariat')); // ceil(300/250)
        $this->assertSame(8, $lines->where('activity_code', 'CEPE.CENTRE_ECRIT.SECRETARIAT_AVANT')->max('days'));
        $this->assertSame(14, $get('CEPE.CENTRE_CORRECTION.CORRECTION_COPIES', 'Correcteurs')); // 7 x ceil(300/200)
        $this->assertSame(7, $get('CEPE.CENTRE_CORRECTION.CORRECTION_COPIES', 'Coordonnateurs'));
        $this->assertSame(2, $get('CEPE.CENTRE_CORRECTION.CORRECTION_SECRETARIAT', 'Agents de secrétariat')); // ceil(300/200)

        // BEPC écrit : 4 salles -> secrétaires ceil(4/3)=2, 3 resp sécurité, 8 surveillants
        $this->assertSame(2, $get('BEPC.CENTRE_ECRIT.SESSION_ECRITE', 'Secrétaires'));
        $this->assertSame(8, $get('BEPC.CENTRE_ECRIT.SESSION_ECRITE', 'Surveillants de salle'));
        $this->assertSame(4, $lines->where('activity_code', 'BEPC.CENTRE_ECRIT.SESSION_ECRITE')->max('days'));

        // CISCO CEPE : 1 agent (300 candidats) ; Art. 12 : 3 jours, 7 x ceil(300/500) = 7
        $this->assertSame(1, $get('CEPE.CISCO.ORGANISATION', 'Agents CISCO'));
        $this->assertSame(3, $lines->where('activity_code', 'CEPE.CISCO.ENVELOPPES_ART12')->max('days'));
        $this->assertSame(7, $get('CEPE.CISCO.ENVELOPPES_ART12', 'Agents (1 par'));

        // DREN : 1 agent / 5 centres ; 2 par CISCO ; chef-lieu => +12 provincial
        $this->assertSame(1, $get('CEPE.DREN.ORGANISATION', 'Agents DREN'));
        $this->assertSame(2, $get('CEPE.DREN.SUPERVISION', 'Agents DREN'));
        $this->assertSame(12, $get('CEPE.DREN.FINALISATION_PROVINCIALE', 'Agents provinciaux'));

        // Central : 3 par province x 6
        $this->assertSame(18, $get('CEPE.MEN_CENTRAL.FINALISATION', '3 agents'));
        $this->assertGreaterThan(0, $lines->where('level', 'EPS')->sum('persons'));
    }

    public function test_montants_et_pages(): void
    {
        $this->seedData();
        Vacation2026Bareme::create(['code' => 'DEFAULT', 'examen' => 'CEPE', 'mode' => 'JOUR', 'montant' => 1000]);
        Vacation2026Bareme::create(['code' => 'centre.surveillant_salle', 'examen' => 'CEPE', 'mode' => 'JOUR', 'montant' => 5000]);

        $lines = app(VacationBesoinsService::class)->lines();
        $sv = $lines->where('activity_code', 'CEPE.CENTRE_ECRIT.SESSION_ECRITE')->firstWhere('rate_code', 'centre.surveillant_salle');
        $this->assertSame(24 * 5000 * 1.0, $sv['amount']);

        $this->withoutVite()->withoutMiddleware();
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        foreach (['dashboard', 'activites', 'dren', 'cisco', 'centres', 'baremes', 'parametres'] as $r) {
            $this->actingAs($user)->get(route('vacation2026.besoins.'.$r))->assertOk();
        }
        $this->actingAs($user)->get(route('vacation2026.besoins.dren.show', Dren::first()))->assertOk();
        $this->actingAs($user)->get(route('vacation2026.besoins.cisco.show', Cisco::first()))->assertOk();
        $this->actingAs($user)->get(route('vacation2026.besoins.centres.show', 'C1'))->assertOk();
        $this->actingAs($user)->get(route('vacation2026.besoins.export'))->assertOk();
        $this->actingAs($user)->put(route('vacation2026.besoins.parametres.update'), [
            'annee' => '2026', 'cepe_enveloppes_matieres' => 7, 'bepc_jours_ecrit_defaut' => 4, 'bepc_anglais_pct' => 100,
            'nb_provinces' => 0, 'eps_centres_par_cisco' => 1,
        ])->assertRedirect();
        $this->actingAs($user)->put(route('vacation2026.besoins.baremes.update'), ['rates' => ['centre__chef_centre' => ['CEPE' => 7000]]])->assertRedirect();
        $this->assertDatabaseHas('vacation_2026_baremes', ['code' => 'centre.chef_centre', 'examen' => 'CEPE']);
    }

    public function test_province_transcription_multiple_et_eps_source_unique(): void
    {
        $this->seedData();
        $this->withoutVite()->withoutMiddleware();
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $cisco = Cisco::first();

        // Pages province et structure
        $this->actingAs($user)->get(route('vacation2026.besoins.province'))->assertOk();
        $this->actingAs($user)->get(route('vacation2026.besoins.structure'))->assertOk();

        // Une CISCO peut avoir plusieurs centres de transcription (zones éloignées)
        foreach (['Transcription Nord', 'Transcription Sud', 'Transcription Est'] as $i => $nom) {
            $this->actingAs($user)->post(route('vacation2026.besoins.transcription.store'), [
                'cisco_id' => $cisco->id, 'nom' => $nom, 'ordre' => $i, 'candidats_cepe' => 100,
            ])->assertSessionHasNoErrors();
        }
        $this->assertSame(3, $cisco->transcriptionCentres()->count());

        // Doublon refusé
        $this->actingAs($user)->post(route('vacation2026.besoins.transcription.store'), ['cisco_id' => $cisco->id, 'nom' => 'Transcription Nord'])
            ->assertSessionHasErrors('nom');
        $this->assertSame(3, $cisco->transcriptionCentres()->count());

        // Les 3 centres apparaissent comme lieux distincts, et le niveau TRANSCRIPTION est calculé
        $lines = app(VacationBesoinsService::class)->lines();
        $this->assertSame(3, $lines->where('scope_kind', 'TRANSCRIPTION')->pluck('scope_key')->unique()->count());

        // Pas de dotted-name : le chef-lieu / la province sont rattachés à la DREN
        $this->assertNotNull(Dren::first()->province);
    }

    public function test_les_candidats_saisis_par_salle_alimentent_les_vacations_du_centre(): void
    {
        $this->seedData();
        $lines = app(VacationBesoinsService::class)->lines();
        // 12 salles x 25 = 300 candidats doivent être visibles sur le centre CEPE
        $centre = $lines->where('scope_kind', 'CENTRE')->where('exam', 'CEPE')->first();
        $this->assertSame(300, $centre['candidates']);
        // Un seul total de candidats CEPE (pas de double comptage entre écrit et correction)
        $this->assertSame(300, app(VacationBesoinsService::class)->compute()['stats']['candidates']['CEPE']);
    }

    public function test_acces_reserve_a_l_administrateur(): void
    {
        $this->withoutVite();
        $this->get(route('vacation2026.besoins.dashboard'))->assertRedirect(); // non connecté
        foreach ([User::ROLE_USER, User::ROLE_LOGISTIQUE] as $role) {
            $u = User::factory()->create(['role' => $role]);
            $this->actingAs($u)->get(route('vacation2026.besoins.dashboard'))->assertStatus(403);
            $this->actingAs($u)->post(route('vacation2026.besoins.transcription.store'), ['cisco_id' => 1, 'nom' => 'X'])->assertStatus(403);
        }
    }
}
