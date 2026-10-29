<?php

namespace Tests\Feature;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\Province;
use App\Models\RepartitionSalle;
use App\Models\User;
use App\Services\VacationBesoinsService;
use Tests\TestCase;

class ReferenceHierarchyTest extends TestCase
{
    private function admin(): User
    {
        $this->withoutVite()->withoutMiddleware();

        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    public function test_la_province_est_obligatoire_pour_creer_une_dren(): void
    {
        $admin = $this->admin();
        $prov = Province::where('nom', 'Toliara')->first();

        $this->actingAs($admin)->post(route('admin.references.drens.store'), ['nom' => 'DREN SANS PROVINCE'])->assertSessionHasErrors('province_id');
        $this->assertDatabaseMissing('drens', ['nom' => 'DREN SANS PROVINCE']);

        $this->actingAs($admin)->post(route('admin.references.drens.store'), ['nom' => 'DREN ATSIMO', 'province_id' => $prov->id]);
        $this->assertDatabaseHas('drens', ['nom' => 'DREN ATSIMO', 'province_id' => $prov->id, 'province' => 'Toliara']);
    }

    public function test_une_cisco_historique_se_rattache_a_une_province_sans_dren(): void
    {
        $admin = $this->admin();
        $prov = Province::where('nom', 'Fianarantsoa')->first();

        $this->actingAs($admin)->post(route('admin.references.ciscos.store'), ['nom' => 'CISCO ANCIENNE'])->assertSessionHasErrors();
        $this->actingAs($admin)->post(route('admin.references.ciscos.store'), ['nom' => 'CISCO ANCIENNE', 'province_id' => $prov->id]);
        $this->assertDatabaseHas('ciscos', ['nom' => 'CISCO ANCIENNE', 'dren_id' => null, 'province_id' => $prov->id]);

        // L'ancienne CISCO apparaît bien sous sa province dans le calcul, sans DREN inventée
        $lines = app(VacationBesoinsService::class)->lines();
        $this->assertTrue($lines->where('cisco_nom', 'CISCO ANCIENNE')->every(fn ($l) => $l['dren_id'] === null));
    }

    public function test_centre_ecrit_meme_site_que_la_correction(): void
    {
        $admin = $this->admin();
        $dren = Dren::create(['nom' => 'D', 'province' => 'Antananarivo']);
        $cisco = Cisco::create(['dren_id' => $dren->id, 'nom' => 'C']);
        $corr = CentreCorrection::create(['cisco_id' => $cisco->id, 'nom' => 'LYCEE A', 'type_examen' => 'BEPC']);

        $this->actingAs($admin)->post(route('admin.references.centres-ecrit.store'), [
            'centre_correction_id' => $corr->id, 'nom' => 'CEG B', 'type_examen' => 'BEPC', 'same_site_as_correction' => 1,
        ])->assertSessionHasNoErrors();
        $this->assertTrue((bool) CentreEcrit::where('nom', 'CEG B')->first()->same_site_as_correction);
    }

    public function test_un_sous_centre_n_a_pas_de_chef_de_centre_propre(): void
    {
        $dren = Dren::create(['nom' => 'D', 'province' => 'Antananarivo']);
        $cisco = Cisco::create(['dren_id' => $dren->id, 'nom' => 'C']);
        $corr = CentreCorrection::create(['cisco_id' => $cisco->id, 'nom' => 'PRINCIPAL', 'type_examen' => 'CEPE']);
        $ecrit = CentreEcrit::create(['centre_correction_id' => $corr->id, 'nom' => 'PRINCIPAL', 'type_examen' => 'CEPE']);
        RepartitionSalle::create(['centre_ecrit_id' => $ecrit->id, 'annee' => '2026', 'langue' => 'TOTAL', 'numero_salle' => 1, 'effectif' => 25]);
        $sous = CentreEcrit::create(['centre_correction_id' => $corr->id, 'nom' => 'ANNEXE', 'type_examen' => 'CEPE', 'centre_type' => 'SOUS-CENTRE']);
        RepartitionSalle::create(['centre_ecrit_id' => $sous->id, 'annee' => '2026', 'langue' => 'TOTAL', 'numero_salle' => 1, 'effectif' => 25]);

        $lines = app(VacationBesoinsService::class)->lines()->where('activity_code', 'CEPE.CENTRE_ECRIT.SESSION_ECRITE');
        $annexe = $lines->where('scope_nom', 'ANNEXE');
        $this->assertTrue($annexe->isNotEmpty());
        $this->assertSame(0, $annexe->filter(fn ($l) => str_starts_with($l['role'], 'Chef de centre') && ! str_contains($l['role'], 'adjoint'))->sum('persons'));
        $this->assertSame(1, $annexe->filter(fn ($l) => str_contains($l['role'], 'adjoint'))->sum('persons'));
    }
}
