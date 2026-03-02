<?php

namespace App\Http\Controllers;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Cisco;
use App\Models\Dren;
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

    public function importDrens(Request $request): RedirectResponse
    {
        $rows = $this->parseCsvRows($request, 'drens_file');
        $created = 0;
        $updated = 0;

        foreach ($rows as $line) {
            $nom = trim((string) ($line['nom'] ?? ''));
            if ($nom === '') {
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

        return back()->with('status', "Import DREN terminé: {$created} créé(s), {$updated} existant(s).");
    }

    public function importCiscos(Request $request): RedirectResponse
    {
        $rows = $this->parseCsvRows($request, 'ciscos_file');
        $created = 0;
        $updated = 0;
        $errors = 0;
        $drensByNormalized = Dren::query()
            ->get(['id', 'nom'])
            ->keyBy(fn (Dren $dren) => $this->normalizeLabel($dren->nom));

        foreach ($rows as $line) {
            $drenNom = trim((string) ($line['dren'] ?? ''));
            $nom = trim((string) ($line['nom'] ?? ''));
            if ($drenNom === '' || $nom === '') {
                continue;
            }

            $dren = $drensByNormalized->get($this->normalizeLabel($drenNom));
            if (! $dren) {
                $errors++;
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

        return back()->with('status', "Import CISCO terminé: {$created} créé(s), {$updated} existant(s), {$errors} rejeté(s).");
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
        $drensByNormalized = Dren::query()
            ->get(['id', 'nom'])
            ->keyBy(fn (Dren $dren) => $this->normalizeLabel($dren->nom));

        foreach ($rows as $line) {
            $typeExamen = $forcedTypeExamen ?? $this->normalizeExamType($line['type_examen'] ?? null);
            $drenNom = trim((string) ($line['dren'] ?? ''));
            $ciscoNom = trim((string) ($line['cisco'] ?? ''));
            $nom = trim((string) ($line['nom'] ?? ''));
            if ($typeExamen === null) {
                $errors++;
                continue;
            }
            if ($drenNom === '' || $ciscoNom === '' || $nom === '') {
                continue;
            }

            $dren = $drensByNormalized->get($this->normalizeLabel($drenNom));
            if (! $dren) {
                $errors++;
                continue;
            }
            $cisco = Cisco::query()
                ->where('dren_id', $dren->id)
                ->get(['id', 'dren_id', 'nom'])
                ->first(fn (Cisco $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($ciscoNom));
            if (! $cisco) {
                $errors++;
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

        return back()->with('status', "Import centres de correction terminé: {$created} créé(s), {$updated} existant(s), {$errors} rejeté(s).");
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
        $drensByNormalized = Dren::query()
            ->get(['id', 'nom'])
            ->keyBy(fn (Dren $dren) => $this->normalizeLabel($dren->nom));

        foreach ($rows as $line) {
            $typeExamen = $forcedTypeExamen ?? $this->normalizeExamType($line['type_examen'] ?? null);
            $drenNom = trim((string) ($line['dren'] ?? ''));
            $ciscoNom = trim((string) ($line['cisco'] ?? ''));
            $ccNom = trim((string) ($line['centre_correction'] ?? ''));
            $nom = trim((string) ($line['nom'] ?? ''));
            if ($typeExamen === null) {
                $errors++;
                continue;
            }
            if ($drenNom === '' || $ciscoNom === '' || $ccNom === '' || $nom === '') {
                continue;
            }

            $dren = $drensByNormalized->get($this->normalizeLabel($drenNom));
            if (! $dren) {
                $errors++;
                continue;
            }
            $cisco = Cisco::query()
                ->where('dren_id', $dren->id)
                ->get(['id', 'dren_id', 'nom'])
                ->first(fn (Cisco $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($ciscoNom));
            if (! $cisco) {
                $errors++;
                continue;
            }
            $centreCorrection = CentreCorrection::query()
                ->where('cisco_id', $cisco->id)
                ->where('type_examen', $typeExamen)
                ->get(['id', 'cisco_id', 'nom', 'type_examen'])
                ->first(fn (CentreCorrection $item) => $this->normalizeLabel($item->nom) === $this->normalizeLabel($ccNom));
            if (! $centreCorrection) {
                $errors++;
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

        return back()->with('status', "Import centres d'écrit terminé: {$created} créé(s), {$updated} existant(s), {$errors} rejeté(s).");
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
}
