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

        foreach ($rows as $line) {
            $drenNom = trim((string) ($line['dren'] ?? ''));
            $nom = trim((string) ($line['nom'] ?? ''));
            if ($drenNom === '' || $nom === '') {
                continue;
            }

            $dren = Dren::query()->where('nom', $drenNom)->first();
            if (! $dren) {
                $errors++;
                continue;
            }

            $cisco = Cisco::query()->firstOrNew(['dren_id' => $dren->id, 'nom' => $nom]);
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
        $rows = $this->parseCsvRows($request, 'centres_correction_file');
        $created = 0;
        $updated = 0;
        $errors = 0;

        foreach ($rows as $line) {
            $typeExamen = $this->normalizeExamType($line['type_examen'] ?? null);
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

            $dren = Dren::query()->where('nom', $drenNom)->first();
            if (! $dren) {
                $errors++;
                continue;
            }
            $cisco = Cisco::query()->where('dren_id', $dren->id)->where('nom', $ciscoNom)->first();
            if (! $cisco) {
                $errors++;
                continue;
            }

            $centre = CentreCorrection::query()->firstOrNew([
                'cisco_id' => $cisco->id,
                'nom' => $nom,
                'type_examen' => $typeExamen,
            ]);
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
        $rows = $this->parseCsvRows($request, 'centres_ecrit_file');
        $created = 0;
        $updated = 0;
        $errors = 0;

        foreach ($rows as $line) {
            $typeExamen = $this->normalizeExamType($line['type_examen'] ?? null);
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

            $dren = Dren::query()->where('nom', $drenNom)->first();
            if (! $dren) {
                $errors++;
                continue;
            }
            $cisco = Cisco::query()->where('dren_id', $dren->id)->where('nom', $ciscoNom)->first();
            if (! $cisco) {
                $errors++;
                continue;
            }
            $centreCorrection = CentreCorrection::query()
                ->where('cisco_id', $cisco->id)
                ->where('nom', $ccNom)
                ->where('type_examen', $typeExamen)
                ->first();
            if (! $centreCorrection) {
                $errors++;
                continue;
            }

            $centreEcrit = CentreEcrit::query()->firstOrNew([
                'centre_correction_id' => $centreCorrection->id,
                'nom' => $nom,
                'type_examen' => $typeExamen,
            ]);
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
            return strtolower(trim((string) $header));
        }, $headers);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (! is_array($row)) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $header) {
                $assoc[$header] = trim((string) ($row[$index] ?? ''));
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
}
