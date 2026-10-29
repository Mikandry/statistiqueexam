<?php

namespace App\Http\Controllers;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReferenceImportController extends Controller
{
    private const TYPE_BEPC = 'BEPC';

    private const TYPE_CEPE = 'CEPE';

    public function index()
    {
        return view('imports.index');
    }

    public function importReferences(Request $request): RedirectResponse
    {
        $forcedTypeExamen = $this->normalizeExamType($request->input('type_examen'));
        if ($forcedTypeExamen === null) {
            return back()->withErrors(['type_examen' => 'Type d\'examen invalide (BEPC ou CEPE).']);
        }

        $rows = $this->parseReferenceRows($request, 'references_file');
        $created = [
            'drens' => 0,
            'ciscos' => 0,
            'centres_correction' => 0,
            'centres_ecrit' => 0,
        ];
        $updated = [
            'drens' => 0,
            'ciscos' => 0,
            'centres_correction' => 0,
            'centres_ecrit' => 0,
        ];
        $errors = 0;
        $rejects = [];

        $drensByNormalized = Dren::query()
            ->get(['id', 'nom'])
            ->keyBy(fn (Dren $dren) => $this->normalizeLabel($dren->nom));

        foreach ($rows as $index => $line) {
            $lineNumber = $index + 2;
            $typeExamen = $forcedTypeExamen ?? $this->normalizeExamType($line['type_examen'] ?? null);
            $drenNom = trim((string) ($line['dren'] ?? $line['dren_nom'] ?? ''));
            $ciscoNom = trim((string) ($line['cisco'] ?? $line['cisco_nom'] ?? ''));
            $centreCorrectionNom = trim((string) ($line['centre_correction'] ?? $line['centre_correction_nom'] ?? ''));
            $centreEcritNom = trim((string) ($line['centre_ecrit'] ?? $line['centre_ecrit_nom'] ?? $line['nom'] ?? ''));

            if ($drenNom === '') {
                $errors++;
                $this->addReject($rejects, $lineNumber, 'Import général rejeté: colonne dren vide.');
                continue;
            }

            if ($typeExamen === null) {
                $errors++;
                $this->addReject($rejects, $lineNumber, 'Import général rejeté: type d\'examen invalide.');
                continue;
            }

            $drenKey = $this->normalizeLabel($drenNom);
            $dren = $drensByNormalized->get($drenKey);
            if (! $dren) {
                $dren = Dren::query()->create(['nom' => $drenNom]);
                $drensByNormalized->put($drenKey, $dren);
                $created['drens']++;
            } else {
                $updated['drens']++;
            }

            if ($ciscoNom === '') {
                continue;
            }

            $cisco = Cisco::query()
                ->where('dren_id', $dren->id)
                ->get(['id', 'dren_id', 'nom'])
                ->first(fn (Cisco $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($ciscoNom));

            if (! $cisco) {
                $cisco = Cisco::query()->create([
                    'dren_id' => $dren->id,
                    'nom' => $ciscoNom,
                ]);
                $created['ciscos']++;
            } else {
                $updated['ciscos']++;
            }

            if ($centreCorrectionNom === '') {
                continue;
            }

            $centreCorrection = CentreCorrection::query()
                ->where('cisco_id', $cisco->id)
                ->where('type_examen', $typeExamen)
                ->get(['id', 'cisco_id', 'nom', 'type_examen'])
                ->first(fn (CentreCorrection $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($centreCorrectionNom));

            if (! $centreCorrection) {
                $centreCorrection = CentreCorrection::query()->create([
                    'cisco_id' => $cisco->id,
                    'nom' => $centreCorrectionNom,
                    'type_examen' => $typeExamen,
                ]);
                $created['centres_correction']++;
            } else {
                $updated['centres_correction']++;
            }

            if ($centreEcritNom === '') {
                continue;
            }

            $centreEcrit = CentreEcrit::query()
                ->where('centre_correction_id', $centreCorrection->id)
                ->where('type_examen', $typeExamen)
                ->get(['id', 'centre_correction_id', 'nom', 'type_examen'])
                ->first(fn (CentreEcrit $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($centreEcritNom));

            if (! $centreEcrit) {
                CentreEcrit::query()->create([
                    'centre_correction_id' => $centreCorrection->id,
                    'nom' => $centreEcritNom,
                    'type_examen' => $typeExamen,
                ]);
                $created['centres_ecrit']++;
            } else {
                $updated['centres_ecrit']++;
            }
        }

        $response = back()
            ->with(
                'status',
                "Import général terminé: DREN {$created['drens']} créé(s), {$updated['drens']} existant(s) | ".
                "CISCO {$created['ciscos']} créé(s), {$updated['ciscos']} existant(s) | ".
                "Correction {$created['centres_correction']} créé(s), {$updated['centres_correction']} existant(s) | ".
                "Écrit {$created['centres_ecrit']} créé(s), {$updated['centres_ecrit']} existant(s) | {$errors} rejeté(s)."
            )
            ->with('import_rejects', $rejects);
        AuditLog::record($request, 'import_references', [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
        return $response;
    }

    public function importDrens(Request $request): RedirectResponse
    {
        $rows = $this->parseCsvRows($request, 'drens_file');
        $created = 0;
        $updated = 0;
        $errors = 0;
        $rejects = [];

        foreach ($rows as $index => $line) {
            $lineNumber = $index + 2;
            $nom = trim((string) ($line['nom'] ?? ''));
            if ($nom === '') {
                $errors++;
                $this->addReject($rejects, $lineNumber, 'DREN rejetée: colonne nom vide.');
                continue;
            }

            $dren = Dren::query()->firstOrNew(['nom' => $nom]);
            if ($dren->exists) {
                $updated++;
            } else {
                $dren->save();
                $created++;
            }
        }

        $response = back()
            ->with('status', "Import DREN terminé: {$created} créé(s), {$updated} existant(s), {$errors} rejeté(s).")
            ->with('import_rejects', $rejects);
        AuditLog::record($request, 'import_drens', [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
        return $response;
    }

    public function importCiscos(Request $request): RedirectResponse
    {
        $rows = $this->parseCsvRows($request, 'ciscos_file');
        $created = 0;
        $updated = 0;
        $errors = 0;
        $rejects = [];
        $drensByNormalized = Dren::query()
            ->get(['id', 'nom'])
            ->keyBy(fn (Dren $dren) => $this->normalizeLabel($dren->nom));

        foreach ($rows as $index => $line) {
            $lineNumber = $index + 2;
            $drenNom = trim((string) ($line['dren'] ?? ''));
            $nom = trim((string) ($line['nom'] ?? ''));
            if ($drenNom === '' || $nom === '') {
                $errors++;
                $this->addReject($rejects, $lineNumber, 'CISCO rejeté: colonnes dren ou nom vides.');
                continue;
            }

            $dren = $drensByNormalized->get($this->normalizeLabel($drenNom));
            if (! $dren) {
                $errors++;
                $this->addReject($rejects, $lineNumber, "CISCO {$nom} rejeté: la DREN {$drenNom} n'existe pas.");
                continue;
            }

            $existingCisco = Cisco::query()
                ->where('dren_id', $dren->id)
                ->get(['id', 'nom'])
                ->first(fn (Cisco $cisco) => $this->normalizeLabel($cisco->nom) === $this->normalizeLabel($nom));

            $cisco = $existingCisco ?? new Cisco(['dren_id' => $dren->id, 'nom' => $nom]);
            if (! $existingCisco) {
                $cisco->nom = $nom;
            }

            if ($cisco->exists) {
                $updated++;
            } else {
                $cisco->save();
                $created++;
            }
        }

        $response = back()
            ->with('status', "Import CISCO terminé: {$created} créé(s), {$updated} existant(s), {$errors} rejeté(s).")
            ->with('import_rejects', $rejects);
        AuditLog::record($request, 'import_ciscos', [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
        return $response;
    }

    public function importCentresCorrection(Request $request): RedirectResponse
    {
        $forcedTypeExamen = $this->normalizeExamType($request->input('type_examen'));
        if ($forcedTypeExamen === null) {
            return back()->withErrors(['type_examen' => 'Type d\'examen invalide (BEPC ou CEPE).']);
        }

        $rows = $this->parseCsvRows($request, 'centres_correction_file');
        $created = 0;
        $updated = 0;
        $errors = 0;
        $rejects = [];
        $drensByNormalized = Dren::query()
            ->get(['id', 'nom'])
            ->keyBy(fn (Dren $dren) => $this->normalizeLabel($dren->nom));

        foreach ($rows as $index => $line) {
            $lineNumber = $index + 2;
            $typeExamen = $forcedTypeExamen ?? $this->normalizeExamType($line['type_examen'] ?? null);
            $drenNom = trim((string) ($line['dren'] ?? ''));
            $ciscoNom = trim((string) ($line['cisco'] ?? ''));
            $nom = trim((string) ($line['nom'] ?? ''));
            if ($typeExamen === null) {
                $errors++;
                $this->addReject($rejects, $lineNumber, "Centre de correction {$nom} rejeté: type d'examen invalide.");
                continue;
            }
            if ($drenNom === '' || $ciscoNom === '' || $nom === '') {
                $errors++;
                $this->addReject($rejects, $lineNumber, 'Centre de correction rejeté: colonnes dren/cisco/nom incomplètes.');
                continue;
            }

            $dren = $drensByNormalized->get($this->normalizeLabel($drenNom));
            if (! $dren) {
                $errors++;
                $this->addReject($rejects, $lineNumber, "Centre de correction {$nom} rejeté: DREN {$drenNom} introuvable.");
                continue;
            }
            $cisco = Cisco::query()
                ->where('dren_id', $dren->id)
                ->get(['id', 'dren_id', 'nom'])
                ->first(fn (Cisco $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($ciscoNom));
            if (! $cisco) {
                $errors++;
                $this->addReject($rejects, $lineNumber, "Centre de correction {$nom} rejeté: CISCO {$ciscoNom} introuvable dans la DREN {$drenNom}.");
                continue;
            }

            $centre = CentreCorrection::query()
                ->where('cisco_id', $cisco->id)
                ->where('type_examen', $typeExamen)
                ->get(['id', 'cisco_id', 'nom', 'type_examen'])
                ->first(fn (CentreCorrection $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($nom));

            if (! $centre) {
                $centre = new CentreCorrection([
                    'cisco_id' => $cisco->id,
                    'nom' => $nom,
                    'type_examen' => $typeExamen,
                ]);
            }

            if ($centre->exists) {
                $updated++;
            } else {
                $centre->save();
                $created++;
            }
        }

        $response = back()
            ->with('status', "Import centres de correction terminé: {$created} créé(s), {$updated} existant(s), {$errors} rejeté(s).")
            ->with('import_rejects', $rejects);
        AuditLog::record($request, 'import_centres_correction', [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
        return $response;
    }

    public function importCentresEcrit(Request $request): RedirectResponse
    {
        $forcedTypeExamen = $this->normalizeExamType($request->input('type_examen'));
        if ($forcedTypeExamen === null) {
            return back()->withErrors(['type_examen' => 'Type d\'examen invalide (BEPC ou CEPE).']);
        }

        $rows = $this->parseCsvRows($request, 'centres_ecrit_file');
        $created = 0;
        $updated = 0;
        $errors = 0;
        $rejects = [];
        $drensByNormalized = Dren::query()
            ->get(['id', 'nom'])
            ->keyBy(fn (Dren $dren) => $this->normalizeLabel($dren->nom));

        foreach ($rows as $index => $line) {
            $lineNumber = $index + 2;
            $typeExamen = $forcedTypeExamen ?? $this->normalizeExamType($line['type_examen'] ?? null);
            $drenNom = trim((string) ($line['dren'] ?? ''));
            $ciscoNom = trim((string) ($line['cisco'] ?? ''));
            $ccNom = trim((string) ($line['centre_correction'] ?? ''));
            $nom = trim((string) ($line['nom'] ?? ''));
            if ($typeExamen === null) {
                $errors++;
                $this->addReject($rejects, $lineNumber, "Centre d'écrit {$nom} rejeté: type d'examen invalide.");
                continue;
            }
            if ($drenNom === '' || $ciscoNom === '' || $ccNom === '' || $nom === '') {
                $errors++;
                $this->addReject($rejects, $lineNumber, "Centre d'écrit rejeté: colonnes dren/cisco/centre_correction/nom incomplètes.");
                continue;
            }

            $dren = $drensByNormalized->get($this->normalizeLabel($drenNom));
            if (! $dren) {
                $errors++;
                $this->addReject($rejects, $lineNumber, "Centre d'écrit {$nom} rejeté: DREN {$drenNom} introuvable.");
                continue;
            }
            $cisco = Cisco::query()
                ->where('dren_id', $dren->id)
                ->get(['id', 'dren_id', 'nom'])
                ->first(fn (Cisco $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($ciscoNom));
            if (! $cisco) {
                $errors++;
                $this->addReject($rejects, $lineNumber, "Centre d'écrit {$nom} rejeté: CISCO {$ciscoNom} introuvable dans la DREN {$drenNom}.");
                continue;
            }
            $centreCorrection = CentreCorrection::query()
                ->where('cisco_id', $cisco->id)
                ->where('type_examen', $typeExamen)
                ->get(['id', 'cisco_id', 'nom', 'type_examen'])
                ->first(fn (CentreCorrection $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($ccNom));
            if (! $centreCorrection) {
                $errors++;
                $this->addReject($rejects, $lineNumber, "Centre d'écrit {$nom} rejeté: centre de correction {$ccNom} introuvable pour le CISCO {$ciscoNom}.");
                continue;
            }

            $centreEcrit = CentreEcrit::query()
                ->where('centre_correction_id', $centreCorrection->id)
                ->where('type_examen', $typeExamen)
                ->get(['id', 'centre_correction_id', 'nom', 'type_examen'])
                ->first(fn (CentreEcrit $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($nom));

            if (! $centreEcrit) {
                $centreEcrit = new CentreEcrit([
                    'centre_correction_id' => $centreCorrection->id,
                    'nom' => $nom,
                    'type_examen' => $typeExamen,
                ]);
            }

            if ($centreEcrit->exists) {
                $updated++;
            } else {
                $centreEcrit->save();
                $created++;
            }
        }

        $response = back()
            ->with('status', "Import centres d'écrit terminé: {$created} créé(s), {$updated} existant(s), {$errors} rejeté(s).")
            ->with('import_rejects', $rejects);
        AuditLog::record($request, 'import_centres_ecrit', [
            'created' => $created,
            'updated' => $updated,
            'errors' => $errors,
        ]);
        return $response;
    }

    private function parseCsvRows(Request $request, string $fieldName): array
    {
        $request->validate([
            $fieldName => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file($fieldName);
        $handle = fopen($file->getRealPath(), 'rb');
        if (! $handle) {
            return [];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $semicolonCols = str_getcsv($firstLine, ';');
        $commaCols = str_getcsv($firstLine, ',');
        $delimiter = count($semicolonCols) >= count($commaCols) ? ';' : ',';

        rewind($handle);

        $headers = fgetcsv($handle, 0, $delimiter);
        if (! is_array($headers)) {
            fclose($handle);

            return [];
        }

        $headers = array_map(function ($header) {
            return strtolower(trim($this->sanitizeCsvValue((string) $header)));
        }, $headers);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (! is_array($row)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                $assoc[$header] = trim($this->sanitizeCsvValue((string) ($row[$index] ?? '')));
            }
            $rows[] = $assoc;
        }

        fclose($handle);

        return $rows;
    }

    private function parseReferenceRows(Request $request, string $fieldName): array
    {
        $request->validate([
            $fieldName => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file($fieldName);
        $handle = fopen($file->getRealPath(), 'rb');
        if (! $handle) {
            return [];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);

            return [];
        }

        $semicolonCols = str_getcsv($firstLine, ';');
        $commaCols = str_getcsv($firstLine, ',');
        $delimiter = count($semicolonCols) >= count($commaCols) ? ';' : ',';

        rewind($handle);

        $rawRows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (! is_array($row)) {
                continue;
            }

            $rawRows[] = array_map(fn ($value) => trim($this->sanitizeCsvValue((string) $value)), $row);
        }

        fclose($handle);

        if ($rawRows === []) {
            return [];
        }

        $headerAliases = [
            'dren',
            'dren_nom',
            'cisco',
            'cisco_nom',
            'centre_correction',
            'centre_correction_nom',
            'centre_ecrit',
            'centre_ecrit_nom',
            'type_examen',
            'nom',
        ];

        $firstRowHeaders = array_map(fn ($value) => $this->normalizeReferenceHeader($value), $rawRows[0]);
        $hasHeader = count(array_intersect($firstRowHeaders, $headerAliases)) >= 2;

        if ($hasHeader) {
            $headers = $firstRowHeaders;
            $dataRows = array_slice($rawRows, 1);

            return array_map(function (array $row) use ($headers) {
                $assoc = [];
                foreach ($headers as $index => $header) {
                    $assoc[$header] = $row[$index] ?? '';
                }

                return $assoc;
            }, $dataRows);
        }

        return array_map(function (array $row) {
            return [
                'dren' => $row[0] ?? '',
                'cisco' => $row[1] ?? '',
                'centre_correction' => $row[2] ?? '',
                'centre_ecrit' => $row[3] ?? '',
                'type_examen' => $row[4] ?? '',
            ];
        }, $rawRows);
    }

    private function normalizeExamType(mixed $value): ?string
    {
        $typeExamen = strtoupper(trim((string) ($value ?? '')));
        if ($typeExamen === '') {
            return self::TYPE_BEPC;
        }

        return in_array($typeExamen, [self::TYPE_BEPC, self::TYPE_CEPE], true) ? $typeExamen : null;
    }

    private function sanitizeCsvValue(string $value): string
    {
        $clean = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
        if (! mb_check_encoding($clean, 'UTF-8')) {
            $converted = @mb_convert_encoding($clean, 'UTF-8', 'Windows-1252,ISO-8859-1,UTF-8');
            $clean = is_string($converted) ? $converted : $clean;
        }

        return $clean;
    }

    private function normalizeLabel(string $value): string
    {
        $value = mb_strtolower(trim($this->sanitizeCsvValue($value)));
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = is_string($ascii) ? $ascii : $value;
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? $value;

        return trim($value);
    }

    private function normalizeReferenceHeader(string $value): string
    {
        $normalized = str_replace("'", ' ', $this->normalizeLabel($value));

        return match ($normalized) {
            'dren', 'region', 'nom dren', 'dren nom' => 'dren',
            'cisco', 'district', 'nom cisco', 'cisco nom' => 'cisco',
            'centre correction', 'centre de correction', 'nom centre correction', 'centre correction nom' => 'centre_correction',
            'centre ecrit', 'centre d ecrit', 'centre d écrit', 'nom centre ecrit', 'centre ecrit nom', 'nom' => 'centre_ecrit',
            'type examen', 'examen', 'type' => 'type_examen',
            default => $normalized,
        };
    }

    private function addReject(array &$rejects, int $lineNumber, string $message): void
    {
        if (count($rejects) >= 120) {
            return;
        }

        $rejects[] = "Ligne {$lineNumber}: {$message}";
    }
}
