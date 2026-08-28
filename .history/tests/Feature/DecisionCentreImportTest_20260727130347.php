<?php

namespace Tests\Feature;

use App\Models\CentreCorrection;
use App\Models\CentreDecision;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecisionCentreImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_displays_the_decision_centre_page_without_loading_error(): void
    {
        $this->actingAs(User::factory()->create());

        $response = $this->get(route('decision.centre'));

        $response->assertOk();
    }

    public function test_it_imports_previous_session_centres_for_the_new_year(): void
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

        CentreDecision::create([
            'annee' => '2024-2025',
            'centre_correction_id' => $centreCorrection->id,
            'centre_ecrit_id' => $centreEcrit->id,
            'type_examen' => 'BEPC',
            'actif' => true,
        ]);

        $this->withSession(['_token' => 'test-token']);

        $response = $this->post(route('decision.centre.import'), [
            '_token' => 'test-token',
            'annee' => '2025-2026',
            'source_annee' => '2024-2025',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('centre_decisions', [
            'annee' => '2025-2026',
            'centre_correction_id' => $centreCorrection->id,
            'centre_ecrit_id' => $centreEcrit->id,
            'type_examen' => 'BEPC',
            'actif' => true,
        ]);
    }

    public function test_imported_centres_are_shown_as_active_and_removed_from_selection(): void
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

        CentreDecision::create([
            'annee' => '2024-2025',
            'centre_correction_id' => $centreCorrection->id,
            'centre_ecrit_id' => $centreEcrit->id,
            'type_examen' => 'BEPC',
            'actif' => true,
        ]);

        $this->withSession(['_token' => 'test-token']);

        $response = $this->post(route('decision.centre.import'), [
            '_token' => 'test-token',
            'annee' => '2025-2026',
            'source_annee' => '2024-2025',
        ]);

        $response->assertRedirect();

        $indexResponse = $this->get(route('decision.centre', ['annee' => '2025-2026']));
        $indexResponse->assertOk();
        $indexResponse->assertSee('<td>Centre écrit A</td>', false);
        $indexResponse->assertDontSee('<option value="'.$centreEcrit->id.'">Centre écrit A');
    }
}
