<?php

namespace App\Http\Controllers;

use App\Models\CentreCorrection;
use App\Models\CentreEcrit;
use App\Models\Dren;
use App\Models\DispatchingPointCoordinate;
use App\Models\TranscriptionCentre;
use App\Models\Vacation2026Parametre;
use App\Services\MapDataService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MapController extends Controller
{
    public function __construct(private readonly MapDataService $map)
    {
    }

    private function canEdit(): bool
    {
        $u = auth()->user();

        return (bool) ($u && ($u->canAccessStats() || $u->canAccessExams()));
    }

    private function abortUnlessEditor(): void
    {
        abort_unless($this->canEdit(), 403);
    }

    private function filters(Request $r): array
    {
        return [
            'exam' => in_array($r->query('exam'), ['CEPE', 'BEPC'], true) ? $r->query('exam') : '',
            'dren_id' => $r->integer('dren_id') ?: null,
            'cisco_id' => $r->integer('cisco_id') ?: null,
            'type' => (string) $r->query('type', ''),
        ];
    }

    /** Les agents mobilisés (module Vacation) ne sont visibles que par l'administrateur. */
    private function withStaff(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }

    public function index(Request $request)
    {
        $f = $this->filters($request);
        $payload = $this->payload($f);

        return view('carte.index', [
            'filters' => $f,
            'payload' => $payload,
            'drens' => Dren::query()->orderBy('nom')->get(['id', 'nom']),
            'params' => Vacation2026Parametre::values(),
            'canEdit' => $this->canEdit(),
            'withStaff' => $this->withStaff(),
        ]);
    }

    public function data(Request $request)
    {
        return response()->json($this->payload($this->filters($request)));
    }

    private function payload(array $f): array
    {
        $centres = $this->map->centres($f, $this->withStaff());
        $params = Vacation2026Parametre::values();

        return $centres + [
            'dispatching' => $this->map->dispatching($f),
            'geo' => [
                'online' => $params['carte_tuiles_en_ligne'] === '1',
                'online_url' => $params['carte_geojson_url'],
                'local_url' => asset('geo/adm1-geoboundaries/regions.geojson'),
                'local_fallback_url' => asset('geo/madagascar-regions.geojson'),
            ],
            'with_staff' => $this->withStaff(),
        ];
    }

    // ---------------------------------------------------------------- Coordonnées

    public function coordinates()
    {
        $this->abortUnlessEditor();

        return view('carte.coordonnees', [
            'corrections' => CentreCorrection::with('cisco')->orderBy('nom')->get(),
            'ecrits' => CentreEcrit::with('centreCorrection')->orderBy('nom')->get(),
            'transcriptions' => TranscriptionCentre::orderBy('nom')->get(),
            'dispatching' => $this->map->dispatching(),
            'params' => Vacation2026Parametre::values(),
        ]);
    }

    /** Valeur vide = effacer ; valeur invalide = rejet explicite. */
    private function parse(?string $lat, ?string $lng, string $label, array &$errors): ?array
    {
        $lat = trim(str_replace(',', '.', (string) $lat));
        $lng = trim(str_replace(',', '.', (string) $lng));
        if ($lat === '' && $lng === '') {
            return ['lat' => null, 'lng' => null];
        }
        if (! MapDataService::validCoordinates($lat, $lng)) {
            $errors[] = "{$label} : coordonnées hors de Madagascar ({$lat} ; {$lng}). Latitude attendue entre -26 et -11,5, longitude entre 42,5 et 51.";

            return null;
        }

        return ['lat' => round((float) $lat, 6), 'lng' => round((float) $lng, 6)];
    }

    public function saveCoordinates(Request $request)
    {
        $this->abortUnlessEditor();
        $errors = [];
        $saved = 0;
        $models = ['correction' => CentreCorrection::class, 'ecrit' => CentreEcrit::class, 'transcription' => TranscriptionCentre::class];

        foreach ($models as $kind => $class) {
            foreach ((array) $request->input("c.$kind", []) as $id => $v) {
                $m = $class::find((int) $id);
                if (! $m || ! is_array($v)) {
                    continue;
                }
                $p = $this->parse($v['lat'] ?? '', $v['lng'] ?? '', (string) $m->nom, $errors);
                if ($p === null) {
                    continue;
                }
                if ($m->latitude !== $p['lat'] || $m->longitude !== $p['lng']) {
                    $m->forceFill(['latitude' => $p['lat'], 'longitude' => $p['lng']])->save();
                    $saved++;
                }
            }
        }

        foreach ((array) $request->input('p', []) as $row) {
            if (! is_array($row) || trim((string) ($row['axe'] ?? '')) === '' || trim((string) ($row['point'] ?? '')) === '') {
                continue;
            }
            $p = $this->parse($row['lat'] ?? '', $row['lng'] ?? '', $row['axe'].' / '.$row['point'], $errors);
            if ($p === null) {
                continue;
            }
            $keys = ['axe' => (string) $row['axe'], 'point' => (string) $row['point']];
            if ($p['lat'] === null) {
                DispatchingPointCoordinate::where($keys)->delete();
            } else {
                DispatchingPointCoordinate::updateOrCreate($keys, ['latitude' => $p['lat'], 'longitude' => $p['lng']]);
            }
            $saved++;
        }

        $redirect = redirect()->route('carte.coordonnees');
        if ($errors) {
            $redirect = $redirect->withErrors($errors);
        }

        return $redirect->with('success', "{$saved} coordonnée(s) enregistrée(s)".($errors ? ', '.count($errors).' ligne(s) refusée(s).' : '.'));
    }

    public function saveSettings(Request $request)
    {
        $this->abortUnlessEditor();
        $data = $request->validate([
            'carte_geojson_url' => ['nullable', 'url', 'max:500'],
            'carte_depart_nom' => ['nullable', 'string', 'max:120'],
            'carte_depart_lat' => ['nullable', 'numeric'],
            'carte_depart_lng' => ['nullable', 'numeric'],
        ]);
        if (($data['carte_depart_lat'] ?? '') !== '' && $data['carte_depart_lat'] !== null
            && ! MapDataService::validCoordinates($data['carte_depart_lat'], $data['carte_depart_lng'] ?? null)) {
            return back()->withErrors(['carte_depart_lat' => 'Le point de départ doit se trouver à Madagascar.'])->withInput();
        }
        Vacation2026Parametre::put('carte_tuiles_en_ligne', $request->boolean('carte_tuiles_en_ligne') ? '1' : '0');
        foreach (['carte_geojson_url', 'carte_depart_nom', 'carte_depart_lat', 'carte_depart_lng'] as $k) {
            if (array_key_exists($k, $data) && $data[$k] !== null && $data[$k] !== '') {
                Vacation2026Parametre::put($k, (string) $data[$k]);
            }
        }

        return redirect()->route('carte.coordonnees')->with('success', 'Paramètres de la carte enregistrés.');
    }

    // ---------------------------------------------------------------- CSV

    public function template(): StreamedResponse
    {
        $this->abortUnlessEditor();
        $rows = [];
        foreach (CentreCorrection::orderBy('nom')->get() as $c) {
            $rows[] = ['correction', $c->id, '', '', $c->nom, $c->latitude, $c->longitude];
        }
        foreach (CentreEcrit::orderBy('nom')->get() as $c) {
            $rows[] = ['ecrit', $c->id, '', '', $c->nom, $c->latitude, $c->longitude];
        }
        foreach (TranscriptionCentre::orderBy('nom')->get() as $c) {
            $rows[] = ['transcription', $c->id, '', '', $c->nom, $c->latitude, $c->longitude];
        }
        foreach ($this->map->dispatching()['axes'] as $a) {
            foreach ($a['points'] as $p) {
                $rows[] = ['point', '', $a['axe'], $p['point'], $p['point'], $p['source'] === 'saisi' ? $p['lat'] : '', $p['source'] === 'saisi' ? $p['lng'] : ''];
            }
        }

        return response()->streamDownload(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fwrite($h, "\xEF\xBB\xBF");
            fputcsv($h, ['type', 'id', 'axe', 'point', 'nom', 'latitude', 'longitude'], ';');
            foreach ($rows as $r) {
                fputcsv($h, $r, ';');
            }
            fclose($h);
        }, 'coordonnees-centres.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(Request $request)
    {
        $this->abortUnlessEditor();
        $request->validate(['fichier' => ['required', 'file', 'max:4096']]);
        $path = $request->file('fichier')->getRealPath();
        $first = (string) fgets(fopen($path, 'r'));
        $sep = substr_count($first, ';') >= substr_count($first, ',') ? ';' : ',';
        $h = fopen($path, 'r');
        $head = array_map(fn ($x) => strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $x))), (array) fgetcsv($h, 0, $sep));
        foreach (['type', 'latitude', 'longitude'] as $need) {
            if (! in_array($need, $head, true)) {
                return back()->withErrors(["Colonne « {$need} » absente. Utilisez le modèle CSV fourni."]);
            }
        }
        $errors = [];
        $saved = 0;
        $line = 1;
        $models = ['correction' => CentreCorrection::class, 'ecrit' => CentreEcrit::class, 'transcription' => TranscriptionCentre::class];
        while (($raw = fgetcsv($h, 0, $sep)) !== false) {
            $line++;
            if (count($raw) < 3 || trim(implode('', $raw)) === '') {
                continue;
            }
            $r = array_combine($head, array_pad(array_slice($raw, 0, count($head)), count($head), ''));
            $type = strtolower(trim($r['type']));
            $p = $this->parse($r['latitude'], $r['longitude'], "Ligne {$line}", $errors);
            if ($p === null) {
                continue;
            }
            if (isset($models[$type])) {
                $m = $models[$type]::find((int) ($r['id'] ?? 0));
                if (! $m) {
                    $errors[] = "Ligne {$line} : {$type} #".($r['id'] ?? '').' introuvable.';
                    continue;
                }
                $m->forceFill(['latitude' => $p['lat'], 'longitude' => $p['lng']])->save();
                $saved++;
            } elseif ($type === 'point') {
                $keys = ['axe' => trim((string) ($r['axe'] ?? '')), 'point' => trim((string) ($r['point'] ?? ''))];
                if ($keys['axe'] === '' || $keys['point'] === '') {
                    $errors[] = "Ligne {$line} : axe et point obligatoires.";
                    continue;
                }
                if ($p['lat'] === null) {
                    DispatchingPointCoordinate::where($keys)->delete();
                } else {
                    DispatchingPointCoordinate::updateOrCreate($keys, ['latitude' => $p['lat'], 'longitude' => $p['lng']]);
                }
                $saved++;
            } else {
                $errors[] = "Ligne {$line} : type « {$type} » inconnu (correction, ecrit, transcription, point).";
            }
        }
        fclose($h);

        $back = redirect()->route('carte.coordonnees')->with('success', "Import terminé : {$saved} ligne(s) appliquée(s), ".count($errors).' refusée(s).');

        return $errors ? $back->withErrors(array_slice($errors, 0, 30)) : $back;
    }
}
