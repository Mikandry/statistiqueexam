<?php

namespace App\Http\Controllers;

use App\Models\CapCaeResultBatch;
use App\Services\CapCaeResultsService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CapCaeResultController extends Controller
{
    public function __construct(private readonly CapCaeResultsService $service) {}

    public function index(Request $request)
    {
        $batches = CapCaeResultBatch::query()->withCount('candidates')->latest()->limit(20)->get();
        $batch = $request->filled('batch_id')
            ? CapCaeResultBatch::query()->with('candidates')->find($request->integer('batch_id'))
            : $batches->first();

        return view('cap-cae-results.index', [
            'batches' => $batches,
            'batch' => $batch,
            'groups' => $batch ? $batch->candidates->groupBy('centre') : collect(),
        ]);
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'exam_type' => ['required', 'in:CAP,CAE'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'results_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'institution_lines' => ['nullable', 'array'],
            'institution_lines.*' => ['nullable', 'string', 'max:200'],
            'signer_function' => ['nullable', 'string', 'max:160'],
            'signer_name' => ['nullable', 'string', 'max:160'],
            'signer_place' => ['nullable', 'string', 'max:160'],
            'signature_date' => ['nullable', 'date'],
        ]);

        try {
            $batch = $this->service->import(
                $data['results_file'],
                $data['exam_type'],
                (int) $data['year'],
                $data,
                $request->user()?->id,
            );
        } catch (\InvalidArgumentException $exception) {
            return back()->withInput()->withErrors(['results_file' => $exception->getMessage()]);
        } catch (Throwable $exception) {
            Log::error('Import résultats CAP/CAE impossible', ['exception' => $exception]);
            return back()->withInput()->withErrors(['results_file' => 'Le fichier n’a pas pu être traité. Vérifiez sa structure et son format.']);
        }

        $message = "Import {$batch->exam_type} terminé : {$batch->total_candidates} candidat(s), {$batch->total_centres} centre(s).";
        if (count($batch->anomalies ?? []) > 0) $message .= ' Des anomalies sont signalées dans le détail du lot.';

        return redirect()->route('cap-cae-results.index', ['batch_id' => $batch->id])->with('success', $message);
    }

    public function updateSettings(Request $request, CapCaeResultBatch $batch)
    {
        $data = $request->validate([
            'institution_lines' => ['nullable', 'array'],
            'institution_lines.*' => ['nullable', 'string', 'max:200'],
            'signer_function' => ['nullable', 'string', 'max:160'],
            'signer_name' => ['nullable', 'string', 'max:160'],
            'signer_place' => ['nullable', 'string', 'max:160'],
            'signature_date' => ['nullable', 'date'],
        ]);
        $batch->update([
            'institution_lines' => array_values(array_filter($data['institution_lines'] ?? [])),
            'signer_function' => $data['signer_function'] ?? null,
            'signer_name' => $data['signer_name'] ?? null,
            'signer_place' => $data['signer_place'] ?? null,
            'signature_date' => $data['signature_date'] ?? null,
        ]);
        return back()->with('success', 'Paramètres de signature mis à jour.');
    }

    public function preview(CapCaeResultBatch $batch)
    {
        return view('cap-cae-results.document', $this->documentData($batch, false));
    }

    public function exportPdf(CapCaeResultBatch $batch)
    {
        return Pdf::loadView('cap-cae-results.document', $this->documentData($batch, false))
            ->setPaper('a4', 'landscape')
            ->download("admis-{$batch->exam_type}-{$batch->year}.pdf");
    }

    public function exportExcel(CapCaeResultBatch $batch): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['N° d’ordre général', 'N° d’ordre du centre', 'Nom et prénoms', 'Date de naissance', 'Localité de service', 'DREN', 'Centre'], null, 'A1');
        $row = 2;
        foreach ($batch->candidates()->orderBy('general_order')->get() as $candidate) {
            $sheet->fromArray([$candidate->general_order, $candidate->centre_order, $candidate->name, $candidate->birth_date?->format('d/m/Y'), $candidate->service_location, $candidate->dren, $candidate->centre], null, "A{$row}");
            $row++;
        }
        $this->styleSheet($sheet, $row - 1);
        return response()->streamDownload(fn () => (new Xlsx($spreadsheet))->save('php://output'), "admis-{$batch->exam_type}-{$batch->year}.xlsx");
    }

    public function diplomaPreview(CapCaeResultBatch $batch)
    {
        return view('cap-cae-results.diploma', $this->documentData($batch, true));
    }

    public function diplomaPdf(CapCaeResultBatch $batch)
    {
        return Pdf::loadView('cap-cae-results.diploma', $this->documentData($batch, true))
            ->setPaper('a4', 'portrait')
            ->download("liste-diplomes-{$batch->exam_type}-{$batch->year}.pdf");
    }

    public function diplomaExcel(CapCaeResultBatch $batch): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['N°', 'Nom et prénoms', 'Date de naissance', 'Localité de service', 'Date de délivrance du diplôme', 'Centre'], null, 'A1');
        $row = 2;
        foreach ($batch->candidates()->orderBy('centre')->orderBy('centre_order')->get() as $candidate) {
            $sheet->fromArray([$candidate->centre_order, $candidate->name, $candidate->birth_date?->format('d/m/Y'), $candidate->service_location, '', $candidate->centre], null, "A{$row}");
            $row++;
        }
        $this->styleSheet($sheet, $row - 1);
        return response()->streamDownload(fn () => (new Xlsx($spreadsheet))->save('php://output'), "liste-diplomes-{$batch->exam_type}-{$batch->year}.xlsx");
    }

    private function documentData(CapCaeResultBatch $batch, bool $diploma): array
    {
        $batch->loadMissing('candidates');
        $totalCandidates = $batch->candidates->count();
        $batch->total_candidates = $totalCandidates;
        return [
            'batch' => $batch,
            'groups' => $batch->candidates->groupBy('centre'),
            'diploma' => $diploma,
            'totalInWords' => $this->numberToWords($totalCandidates),
        ];
    }

    private function styleSheet($sheet, int $lastRow): void
    {
        $sheet->getStyle("A1:G{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        foreach (range('A', 'G') as $column) $sheet->getColumnDimension($column)->setAutoSize(true);
    }

    private function numberToWords(int $number): string
    {
        if ($number < 20) return ['zéro','un','deux','trois','quatre','cinq','six','sept','huit','neuf','dix','onze','douze','treize','quatorze','quinze','seize','dix-sept','dix-huit','dix-neuf'][$number];
        if ($number < 100) {
            $tens = [2=>'vingt',3=>'trente',4=>'quarante',5=>'cinquante',6=>'soixante',7=>'soixante',8=>'quatre-vingt',9=>'quatre-vingt'];
            $ten = intdiv($number, 10); $unit = $number % 10;
            if ($ten === 7 || $ten === 9) return $tens[$ten].'-'.$this->numberToWords(10 + $unit);
            return $tens[$ten].($unit === 0 ? '' : ($unit === 1 ? '-et-un' : '-'.$this->numberToWords($unit)));
        }
        if ($number < 1000) return ($number < 200 ? ($number === 100 ? 'cent' : 'cent '.$this->numberToWords($number - 100)) : $this->numberToWords(intdiv($number, 100)).' cent'.($number % 100 === 0 ? 's' : ' '.$this->numberToWords($number % 100)));
        if ($number < 1000000) return $this->numberToWords(intdiv($number, 1000)).' mille'.($number % 1000 ? ' '.$this->numberToWords($number % 1000) : '');
        return (string) $number;
    }
}
