<?php

namespace Tests\Feature;

use App\Models\Vacation2026Activity;
use App\Models\Vacation2026Agent;
use App\Models\Vacation2026Assignment;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class Vacation2026AgentImportTest extends TestCase
{
    public function test_import_updates_agent_by_im_and_creates_new_agents(): void
    {
        $this->withoutMiddleware();
        $activity = Vacation2026Activity::query()->firstOrFail();
        $agent = Vacation2026Agent::query()->create([
            'nom' => 'Ancien Nom',
            'im' => '370462',
            'localite_service' => 'Ancienne localite',
        ]);
        $assignment = Vacation2026Assignment::query()->create([
            'agent_id' => $agent->id,
            'activity_id' => $activity->id,
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Nom', 'IM', 'Localite service', 'CIN'], null, 'A1');
        $sheet->fromArray(['Nouveau Nom', '370462', 'Nouvelle localite', 'CIN-1'], null, 'A2');
        $sheet->fromArray(['Nouvel Agent', '999999', 'Autre localite', 'CIN-2'], null, 'A3');
        ob_start();
        (new Xlsx($spreadsheet))->save('php://output');
        $content = ob_get_clean();

        $response = $this->post(route('vacation2026.import'), [
            'activity_id' => $activity->id,
            'agents_file' => UploadedFile::fake()->createWithContent('agents.xlsx', $content),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('vacation_2026_agents', 2);
        $this->assertDatabaseHas('vacation_2026_agents', [
            'id' => $agent->id,
            'nom' => 'Nouveau Nom',
            'im' => '370462',
            'localite_service' => 'Nouvelle localite',
        ]);
        $this->assertDatabaseHas('vacation_2026_assignments', ['id' => $assignment->id, 'agent_id' => $agent->id]);
        $this->assertDatabaseHas('vacation_2026_agents', ['nom' => 'Nouvel Agent', 'im' => '999999']);
        $response->assertSessionHas('status', fn (string $status): bool => str_contains($status, '0 rejeté(s)'));
    }
}