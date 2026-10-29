<?php

namespace Tests\Feature;

use App\Models\HrAgent;
use App\Models\HrEvent;
use App\Models\User;
use App\Services\HrActivityTotalsService;
use Carbon\Carbon;
use Tests\TestCase;

class HrActivityTotalsTest extends TestCase
{
    private function event(HrAgent $a, string $type, string $from, string $to, string $status = 'valide'): void
    {
        HrEvent::create(['agent_id' => $a->id, 'type' => $type, 'status' => $status, 'date_debut' => $from, 'date_fin' => $to]);
    }

    public function test_pas_de_double_comptage_et_categories_distinctes(): void
    {
        $agent = HrAgent::create(['nom' => 'RAKOTO', 'matricule' => '123']);
        $this->event($agent, 'conge', '2026-03-01', '2026-03-10');            // 10 jours
        $this->event($agent, 'formation', '2026-03-08', '2026-03-12');        // 5 jours dont 3 communs avec le congé
        $this->event($agent, 'atelier', '2026-04-01', '2026-04-02');          // 2 jours
        $this->event($agent, 'autorisation_absence', '2026-04-05', '2026-04-05', 'demande'); // non validé : ignoré
        $this->event($agent, 'conge', '2025-12-30', '2026-01-02');            // 2 jours dans 2026 (borné à la période)

        $svc = app(HrActivityTotalsService::class);
        $row = $svc->totals(Carbon::parse('2026-01-01'), Carbon::parse('2026-12-31'))->first();

        $this->assertSame(12, $row['raw']['conge']);
        $this->assertSame(5, $row['raw']['formation']);
        $this->assertSame(2, $row['raw']['atelier']);
        $this->assertArrayNotHasKey('autorisation_absence', $row['raw']);
        // Union : 12 (congé) + 2 jours de formation hors congé + 2 atelier = 16 ; 3 jours de chevauchement
        $this->assertSame(16, $row['union_days']);
        $this->assertSame(3, $row['overlap_days']);
        $this->assertSame(12, $row['consolidated']['conge']);
        $this->assertSame(2, $row['consolidated']['formation']);
    }

    public function test_filtre_de_periode(): void
    {
        $agent = HrAgent::create(['nom' => 'RABE']);
        $this->event($agent, 'formation', '2026-03-01', '2026-03-10');
        $row = app(HrActivityTotalsService::class)->totals(Carbon::parse('2026-03-05'), Carbon::parse('2026-03-06'))->first();
        $this->assertSame(2, $row['raw']['formation']);
    }

    public function test_pages_reservees_a_l_administrateur(): void
    {
        $this->withoutVite();
        $agent = HrAgent::create(['nom' => 'RABE']);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $this->actingAs($admin)->get(route('hr.activity.index'))->assertOk();
        $this->actingAs($admin)->get(route('hr.activity.show', $agent))->assertOk();
        $this->actingAs($user)->get(route('hr.activity.index'))->assertStatus(403);
        $this->actingAs($user)->get(route('hr.activity.show', $agent))->assertStatus(403);
    }
}
