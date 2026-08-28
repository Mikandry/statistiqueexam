<?php

namespace Tests\Feature;

use App\Models\CapCaeResultBatch;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class CapCaeResultImportTest extends TestCase
{
    public function test_it_imports_results_and_generates_independent_orders(): void
    {
        $this->withoutMiddleware();
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['Colonne inutile', 'Centre', 'Date de naissance', 'N°', 'DREN', 'Nom et prénoms', 'Localité de service'], null, 'A1');
        $sheet->fromArray(['x', 'Centre B', '02/02/2000', 44, 'DREN 2', 'Nom B', 'Ville B'], null, 'A2');
        $sheet->fromArray(['x', 'Centre A', '01/01/2000', 8, 'DREN 1', 'Nom A', 'Ville A'], null, 'A3');
        $sheet->fromArray(['x', 'Centre B', '03/03/2000', 2, 'DREN 2', 'Nom C', 'Ville C'], null, 'A4');
        ob_start();
        (new Xlsx($spreadsheet))->save('php://output');
        $content = ob_get_clean();

        $response = $this->actingAs($user)->post(route('cap-cae-results.import'), [
            'exam_type' => 'CAP',
            'year' => 2026,
            'results_file' => UploadedFile::fake()->createWithContent('resultats.xlsx', $content),
            'institution_lines' => ['MINISTÈRE DE L’ÉDUCATION NATIONALE'],
        ]);
        $batch = CapCaeResultBatch::query()->firstOrFail();
        $response->assertRedirect(route('cap-cae-results.index', ['batch_id' => $batch->id]));
        $this->assertSame(['Centre A', 'Centre B', 'Centre B'], $batch->candidates()->orderBy('general_order')->pluck('centre')->all());
        $this->assertSame([1, 2, 3], $batch->candidates()->orderBy('general_order')->pluck('general_order')->all());
        $this->assertSame([1, 1, 2], $batch->candidates()->orderBy('general_order')->pluck('centre_order')->all());
        $this->actingAs($user)->get(route('cap-cae-results.preview', $batch))->assertOk();
    }
}
