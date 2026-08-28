<?php

namespace App\Services;

use App\Models\CapCaeResultBatch;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;
use Throwable;

class CapCaeResultsService
{
    private const REQUIRED_COLUMNS = [
        'number' => ['n', 'numero', 'no', 'ordre'],
        'registration_number' => ['numero inscription', 'n inscription', 'inscription', 'matricule'],
        'name' => ['nom et prenoms', 'nom prenoms', 'nom et prenom', 'nom'],
        'birth_date' => ['date de naissance', 'date naissance', 'naissance'],
        'birth_place' => ['lieu de naissance', 'lieu naissance', 'localite de naissance'],
        'dren' => ['dren', 'direction regionale'],
        'centre' => ['centre', 'centre examen'],
    ];

    public function import(UploadedFile $file, string $examType, int $year, array $signature, ?int $userId): CapCaeResultBatch
    {
        $sheet = IOFactory::load($file->getRealPath())->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);
        [$headerRow, $headers] = $this->findHeaders($rows);
        $missing = array_diff(array_keys(self::REQUIRED_COLUMNS), array_keys($headers));

        if ($missing !== []) {
            throw new \InvalidArgumentException('Colonnes obligatoires absentes : '.implode(', ', array_map($this->columnLabel(...), $missing)).'.');
        }

        $prepared = [];
        $anomalies = [];
        $seen = [];
        foreach (array_slice($rows, $headerRow) as $offset => $row) {
            $sourceRow = $headerRow + $offset + 1;
            $values = array_map(fn ($value) => is_string($value) ? trim(preg_replace('/\s+/u', ' ', $value)) : $value, $row);
            if (count(array_filter($values, fn ($value) => $value !== null && $value !== '')) === 0) {
                continue;
            }

            $name = $this->text($row, $headers['name']);
            $centre = $this->text($row, $headers['centre']);
            $dren = $this->text($row, $headers['dren']);
            $birthDate = $this->parseDate($row[$headers['birth_date']] ?? null);
            $errors = [];
            if ($name === '') $errors[] = 'nom/prénoms manquant';
            if ($centre === '') $errors[] = 'centre manquant';
            if ($dren === '') $errors[] = 'DREN manquante';
            if (($row[$headers['birth_date']] ?? null) !== null && trim((string) $row[$headers['birth_date']]) !== '' && $birthDate === null) {
                $errors[] = 'date de naissance invalide';
            }

            $key = Str::of($name.'|'.$centre.'|'.($birthDate?->format('Y-m-d') ?? ''))->ascii()->lower()->squish()->toString();
            if ($key !== '||' && isset($seen[$key])) {
                $errors[] = 'doublon possible avec la ligne '.$seen[$key];
            } else {
                $seen[$key] = $sourceRow;
            }
            if ($errors !== []) {
                $anomalies[] = ['row' => $sourceRow, 'messages' => $errors];
                if (in_array('nom/prénoms manquant', $errors, true) || in_array('centre manquant', $errors, true) || in_array('DREN manquante', $errors, true)) continue;
            }

            $prepared[] = [
                'source_number' => $this->text($row, $headers['number']),
                'registration_number' => $this->text($row, $headers['registration_number']),
                'name' => $name,
                'birth_date' => $birthDate?->format('Y-m-d'),
                'birth_place' => $this->text($row, $headers['birth_place']),
                'dren' => $dren,
                'centre' => $centre,
                'source_row' => $sourceRow,
                'source_data' => $values,
                '_centre_key' => Str::of($centre)->ascii()->lower()->squish()->toString(),
            ];
        }

        if ($prepared === []) {
            throw new \InvalidArgumentException('Aucun candidat valide n’a été trouvé dans le fichier.');
        }

        usort($prepared, fn (array $left, array $right) => [$left['_centre_key'], $left['source_row']] <=> [$right['_centre_key'], $right['source_row']]);
        $centreOrders = [];
        foreach ($prepared as $index => &$candidate) {
            $centreOrders[$candidate['_centre_key']] = ($centreOrders[$candidate['_centre_key']] ?? 0) + 1;
            $candidate['general_order'] = $index + 1;
            $candidate['centre_order'] = $centreOrders[$candidate['_centre_key']];
            unset($candidate['_centre_key']);
        }
        unset($candidate);

        return DB::transaction(function () use ($file, $examType, $year, $signature, $userId, $prepared, $anomalies): CapCaeResultBatch {
            $batch = CapCaeResultBatch::query()->create([
                'exam_type' => $examType,
                'list_status' => $signature['list_status'] ?? 'definitive',
                'year' => $year,
                'source_filename' => $file->getClientOriginalName(),
                'total_candidates' => count($prepared),
                'total_centres' => collect($prepared)->pluck('centre')->unique()->count(),
                'institution_lines' => array_values(array_filter($signature['institution_lines'] ?? ['MINISTÈRE DE L’ÉDUCATION NATIONALE'])),
                'signer_function' => $signature['signer_function'] ?? null,
                'signer_name' => $signature['signer_name'] ?? null,
                'signer_place' => $signature['signer_place'] ?? null,
                'signature_date' => $signature['signature_date'] ?? null,
                'pv_date' => $signature['pv_date'] ?? null,
                'anomalies' => $anomalies,
                'created_by' => $userId,
            ]);
            $batch->candidates()->createMany($prepared);
            return $batch->load('candidates');
        });
    }

    private function findHeaders(array $rows): array
    {
        foreach ($rows as $index => $row) {
            $normalized = [];
            foreach ($row as $column => $value) {
                $key = $this->normalizeHeader((string) $value);
                foreach (self::REQUIRED_COLUMNS as $field => $aliases) {
                    if (in_array($key, $aliases, true) || in_array($key, array_map([$this, 'normalizeHeader'], $aliases), true)) {
                        $normalized[$field] = $column;
                        break;
                    }
                }
            }
            if (count($normalized) >= 4) return [$index, $normalized];
        }
        throw new \InvalidArgumentException('La ligne d’en-têtes du fichier Excel est introuvable.');
    }

    private function normalizeHeader(string $value): string
    {
        return Str::of($value)->ascii()->lower()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->toString();
    }

    private function text(array $row, string $column): string
    {
        return trim(preg_replace('/\s+/u', ' ', (string) ($row[$column] ?? '')));
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') return null;
        try {
            if (is_numeric($value)) return Carbon::instance(SpreadsheetDate::excelToDateTimeObject((float) $value))->startOfDay();
            foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd.m.Y'] as $format) {
                try { return Carbon::createFromFormat($format, trim((string) $value))->startOfDay(); } catch (Throwable) { }
            }
        } catch (Throwable) { }
        return null;
    }

    private function columnLabel(string $column): string
    {
        return [
            'number' => 'N°', 'registration_number' => 'Numéro d’inscription', 'name' => 'Nom et prénoms', 'birth_date' => 'Date de naissance',
            'birth_place' => 'Lieu de naissance', 'dren' => 'DREN', 'centre' => 'Centre',
        ][$column] ?? $column;
    }
}
