<?php

namespace Tests\Feature;

use App\Models\HrAgent;
use App\Models\User;
use Tests\TestCase;

class HrPersonnelTest extends TestCase
{
    public function test_it_creates_an_agent_and_calculates_temporary_assignment(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $response = $this->actingAs($user)->post(route('hr.agents.store'), [
            'matricule' => 'MAT-001',
            'nom' => 'RAKOTO',
            'prenoms' => 'Jean',
            'fonction' => 'Chef de bureau',
            'service' => 'Service administratif',
            'statut' => 'Fonctionnaire',
        ]);
        $response->assertSessionHas('success');

        $agent = HrAgent::query()->firstOrFail();
        $this->actingAs($user)->post(route('hr.agents.assignments.store', $agent), [
            'direction' => 'Direction partenaire',
            'service' => 'Service accueil',
            'date_debut' => today()->subDay()->toDateString(),
            'date_fin' => today()->addDay()->toDateString(),
            'motif' => 'Renfort temporaire',
            'reference' => 'DEC-001',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('hr_assignments', [
            'agent_id' => $agent->id,
            'current' => 1,
            'direction' => 'Direction partenaire',
        ]);

        $this->actingAs($user)->get(route('hr.dashboard'))
            ->assertOk()
            ->assertSee('Affectation temporaire')
            ->assertSee('RAKOTO Jean');
    }
}
