<?php

namespace App\Http\Controllers;

use App\Models\DispatchingParcel;
use App\Models\Vacation2026Parametre;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;

/**
 * Saisie des colis par centre, puis « fiche de largage » (par axe ou par point) et « accusé de réception » (par point).
 * Les données (centres, candidats, salles, ordre des points) viennent du même calcul que le dispatching Excel.
 */
class DispatchingDocumentController extends RepartitionReportController
{
    private const SIGN_KEYS = [
        'remettant' => 'dispatch_remettant',
        'force_ordre' => 'dispatch_force_ordre',
        'receptionnaires' => 'dispatch_receptionnaires',
        'securite' => 'dispatch_securite',
        'agents_men' => 'dispatch_agents_men',
    ];

    // ------------------------------------------------------------------ Saisie des colis

    public function colis(Request $request)
    {
        $data = $this->dataset($request);

        return view('repartition.dispatching-colis', [
            'filters' => $data['filters'],
            'annees' => $data['annees'],
            'drens' => $data['drens'],
            'axes' => $data['axes'],
            'exam' => $data['exam'],
            'signatories' => $this->signatories($request),
            'query' => $request->only(['annee', 'type_examen', 'dren']),
        ]);
    }

    public function saveColis(Request $request)
    {
        $validated = $request->validate([
            'colis' => ['required', 'array', 'max:5000'],
            'colis.*' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'annee_row' => ['required', 'array'],
            'annee_row.*' => ['nullable', 'string', 'max:9'],
        ]);

        $saved = 0;
        foreach ($validated['colis'] as $centreId => $value) {
            $annee = (string) ($validated['annee_row'][$centreId] ?? '');
            if ($annee === '' || ! \App\Models\CentreEcrit::query()->whereKey((int) $centreId)->exists()) {
                continue;
            }
            $keys = ['centre_ecrit_id' => (int) $centreId, 'annee' => $annee];
            if ($value === null || $value === '') {
                DispatchingParcel::query()->where($keys)->delete(); // case vidée = pas de saisie
            } else {
                DispatchingParcel::query()->updateOrCreate($keys, ['colis' => (int) $value, 'saisi_par' => $request->user()?->id]);
            }
            $saved++;
        }

        if ($request->has('sign')) {
            $this->saveSignatories((array) $request->input('sign'));
        }

        return redirect()->route('repartition.dispatching.colis', $request->only(['annee', 'type_examen', 'dren']))
            ->with('success', "{$saved} centre(s) mis à jour.");
    }

    public function saveSignatoriesOnly(Request $request)
    {
        $this->saveSignatories((array) $request->input('sign'));

        return redirect()->route('repartition.dispatching.colis', $request->only(['annee', 'type_examen', 'dren']))
            ->with('success', 'Noms des signataires enregistrés.');
    }

    private function saveSignatories(array $sign): void
    {
        foreach (self::SIGN_KEYS as $field => $key) {
            if (array_key_exists($field, $sign)) {
                Vacation2026Parametre::put($key, mb_substr(trim((string) $sign[$field]), 0, 300));
            }
        }
    }

    /** Noms saisis dans la page, ou passés dans l'adresse pour un export ponctuel. */
    private function signatories(Request $request): array
    {
        $stored = Vacation2026Parametre::values();
        $out = [];
        foreach (self::SIGN_KEYS as $field => $key) {
            $out[$field] = trim((string) $request->query($field, $stored[$key] ?? ''));
        }

        return $out;
    }

    // ------------------------------------------------------------------ Données

    /**
     * @return array{filters:array, annees:array, drens:array, exam:string, axes:array<string,array>}
     *   axes[axe] = ['axe', 'candidats', 'salles', 'colis'(?int), 'points'=>[point => [...]]]
     */
    private function dataset(Request $request): array
    {
        [$rows, $filters, $annees, $drens] = $this->getFilteredRows($request);
        $dispatch = $this->buildDispatchingRows($rows);
        $dispatch = $this->sortDispatchingRowsByPointOrder($dispatch, $this->buildDispatchingPointOrder($request, $dispatch));

        $axeFilter = trim((string) $request->query('axe', ''));
        $pointFilter = trim((string) $request->query('point', ''));
        if ($axeFilter !== '') {
            $dispatch = $dispatch->where('axe_dispatching', $axeFilter)->values();
        }
        if ($pointFilter !== '') {
            $dispatch = $dispatch->where('point_largage', $pointFilter)->values();
        }

        $parcels = DispatchingParcel::query()->get()->keyBy(fn ($p) => $p->centre_ecrit_id.'|'.$p->annee);

        $axes = [];
        foreach ($dispatch->groupBy('axe_dispatching') as $axe => $axeRows) {
            $points = [];
            $n = 0;
            foreach ($axeRows->groupBy('point_largage') as $point => $pRows) {
                $lines = [];
                $entered = false;
                $colis = 0;
                foreach ($pRows as $r) {
                    $parcel = $parcels->get($r['centre_ecrit_id'].'|'.$r['annee']);
                    if ($parcel) {
                        $entered = true;
                        $colis += (int) $parcel->colis;
                    }
                    $lines[] = [
                        'n' => ++$n,
                        'centre_ecrit_id' => $r['centre_ecrit_id'],
                        'annee' => $r['annee'],
                        'nom' => (string) $r['centre_ecrit'],
                        'cisco' => (string) $r['cisco'],
                        'candidats' => (int) $r['candidats'],
                        'salles' => (int) $r['salles'],
                        'colis' => $parcel ? (int) $parcel->colis : null,
                    ];
                }
                $points[(string) $point] = [
                    'point' => (string) $point,
                    'lines' => $lines,
                    'candidats' => array_sum(array_column($lines, 'candidats')),
                    'salles' => array_sum(array_column($lines, 'salles')),
                    'colis' => $entered ? $colis : null,
                    'colis_saisis' => count(array_filter($lines, fn ($l) => $l['colis'] !== null)),
                ];
            }
            $withColis = array_filter($points, fn ($p) => $p['colis'] !== null);
            $axes[(string) $axe] = [
                'axe' => (string) $axe,
                'points' => $points,
                'candidats' => array_sum(array_column($points, 'candidats')),
                'salles' => array_sum(array_column($points, 'salles')),
                'colis' => $withColis ? array_sum(array_column($withColis, 'colis')) : null,
            ];
        }

        $types = $dispatch->pluck('type_examen')->filter()->unique()->sort()->values()->all();
        $examName = $types === [] ? ($filters['type_examen'] !== 'ALL' ? $filters['type_examen'] : '') : implode(' ET ', $types);
        $year = $filters['annee'] !== '' ? $filters['annee'] : ($dispatch->pluck('annee')->unique()->count() === 1 ? (string) $dispatch->first()['annee'] : '');

        return [
            'filters' => $filters, 'annees' => $annees, 'drens' => $drens, 'axes' => $axes,
            'exam' => trim($examName.' '.$year),
        ];
    }

    // ------------------------------------------------------------------ Documents

    /** Pages : fiche = une par axe ; accusé = une par point de largage. */
    private function pages(string $doc, array $data): array
    {
        $pages = [];
        foreach ($data['axes'] as $axe) {
            if ($doc === 'fiche') {
                $pages[] = ['axe' => $axe['axe'], 'points' => array_values($axe['points']), 'total' => $axe];
            } else {
                foreach ($axe['points'] as $p) {
                    $pages[] = ['axe' => $axe['axe'], 'points' => [$p], 'total' => null];
                }
            }
        }

        return $pages;
    }

    private function meta(string $doc, array $data, array $sign): array
    {
        $exam = $data['exam'] !== '' ? $data['exam'] : '.................';
        $isFiche = $doc === 'fiche';

        return [
            'doc' => $doc,
            'heading' => $isFiche ? null : 'ACCUSÉ DE RÉCEPTION',
            'title' => 'DISPATCHING DE SUJET DU '.mb_strtoupper($exam),
            'footer' => $isFiche
                ? [['Le remettant', $sign['remettant']], ["Les forces de l'ordre", $sign['force_ordre']], ['Le(s) réceptionnaire(s)', $sign['receptionnaires']]]
                : [['Le remettant', $sign['remettant']], ['Sécurité (conservation des colis)', $sign['securite']], ['Les agents du MEN réceptionnaires', $sign['agents_men']]],
            'file' => $isFiche ? 'fiche_de_largage' : 'accuse_de_reception',
        ];
    }

    public function pdf(Request $request, string $doc)
    {
        abort_unless(in_array($doc, ['fiche', 'accuse'], true), 404);
        $data = $this->dataset($request);
        $pages = $this->pages($doc, $data);
        if ($pages === []) {
            return back()->withErrors(['axe' => 'Aucune donnée de dispatching pour ces filtres.']);
        }
        $meta = $this->meta($doc, $data, $this->signatories($request));
        $seal = public_path('madagascar-seal.png');

        $pdf = Pdf::loadView('repartition.dispatching-document', [
            'meta' => $meta, 'pages' => $pages,
            'seal' => is_file($seal) ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($seal)) : null,
        ])->setPaper('a4', 'portrait')->setOption(['isHtml5ParserEnabled' => true]);

        return $pdf->download($this->fileName($meta, $request, 'pdf'));
    }

    public function word(Request $request, string $doc)
    {
        abort_unless(in_array($doc, ['fiche', 'accuse'], true), 404);
        $data = $this->dataset($request);
        $pages = $this->pages($doc, $data);
        if ($pages === []) {
            return back()->withErrors(['axe' => 'Aucune donnée de dispatching pour ces filtres.']);
        }
        $meta = $this->meta($doc, $data, $this->signatories($request));

        $word = new PhpWord();
        $word->setDefaultFontName('Arial');
        $word->setDefaultFontSize(10);
        $seal = public_path('madagascar-seal.png');
        $nf = fn (?int $n) => $n === null ? '' : number_format($n, 0, ',', ' ');
        $dots = '........................';

        foreach ($pages as $page) {
            $section = $word->addSection(['marginTop' => 700, 'marginBottom' => 1300, 'marginLeft' => 900, 'marginRight' => 900]);
            $center = ['alignment' => Jc::CENTER, 'spaceAfter' => 0];

            $section->addText("REPOBLIKAN'I MADAGASIKARA", ['bold' => true], $center);
            $section->addText('Fitiavana - Tanindrazana - Fandrosoana', ['italic' => true, 'size' => 9], $center);
            if (is_file($seal)) {
                $section->addImage($seal, ['width' => 56, 'height' => 56, 'alignment' => Jc::CENTER]);
            }
            $section->addText("MINISTÈRE DE L'ÉDUCATION NATIONALE", ['bold' => true], $center);
            $section->addText("Service de l'Organisation des Examens", ['bold' => true], ['alignment' => Jc::CENTER, 'spaceAfter' => 160]);
            if ($meta['heading']) {
                $section->addText($meta['heading'], ['bold' => true, 'size' => 15], $center);
            }
            $section->addText($meta['title'], ['bold' => true, 'size' => 13], ['alignment' => Jc::CENTER, 'spaceAfter' => 160]);
            $section->addText('AXE : '.($page['axe'] !== '' ? $page['axe'] : $dots), ['bold' => true, 'size' => 12], ['spaceAfter' => 60]);
            if ($page['total'] === null) {
                $section->addText('POINT DE LARGAGE : '.$page['points'][0]['point'], ['bold' => true, 'size' => 11], ['spaceAfter' => 80]);
            }

            $table = $section->addTable(['borderSize' => 6, 'borderColor' => '94A3B8', 'cellMargin' => 50, 'width' => 5000, 'unit' => 'pct']);
            $head = ['N°' => 600, 'Nom du centre' => 3600, 'Total candidats' => 1300, 'Total salles' => 1100, 'Colis' => 1000, 'Observation' => 1700];
            $table->addRow(null, ['tblHeader' => true]);
            foreach ($head as $label => $w) {
                $table->addCell($w, ['bgColor' => 'E2E8F0'])->addText($label, ['bold' => true, 'size' => 9], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
            }
            foreach ($page['points'] as $p) {
                if ($page['total'] !== null) {
                    $table->addRow();
                    $c = $table->addCell(array_sum($head), ['gridSpan' => 6, 'bgColor' => 'F1F5F9']);
                    $c->addText('POINT DE LARGAGE : '.$p['point'], ['bold' => true], ['spaceAfter' => 0]);
                }
                foreach ($p['lines'] as $l) {
                    $table->addRow();
                    $table->addCell($head['N°'])->addText((string) $l['n'], [], ['alignment' => Jc::CENTER, 'spaceAfter' => 0]);
                    $table->addCell($head['Nom du centre'])->addText($l['nom'], [], ['spaceAfter' => 0]);
                    $table->addCell($head['Total candidats'])->addText($nf($l['candidats']), [], ['alignment' => Jc::END, 'spaceAfter' => 0]);
                    $table->addCell($head['Total salles'])->addText($nf($l['salles']), [], ['alignment' => Jc::END, 'spaceAfter' => 0]);
                    $table->addCell($head['Colis'])->addText('', [], ['spaceAfter' => 0]);
                    $table->addCell($head['Observation'])->addText('', [], ['spaceAfter' => 0]);
                }
                $table->addRow();
                $table->addCell($head['N°'] + $head['Nom du centre'], ['gridSpan' => 2, 'bgColor' => 'F8FAFC'])
                    ->addText('ARRÊTAGE — '.$p['point'], ['bold' => true], ['spaceAfter' => 0]);
                $table->addCell($head['Total candidats'], ['bgColor' => 'F8FAFC'])->addText($nf($p['candidats']), ['bold' => true], ['alignment' => Jc::END, 'spaceAfter' => 0]);
                $table->addCell($head['Total salles'], ['bgColor' => 'F8FAFC'])->addText($nf($p['salles']), ['bold' => true], ['alignment' => Jc::END, 'spaceAfter' => 0]);
                $table->addCell($head['Colis'] + $head['Observation'], ['gridSpan' => 2, 'bgColor' => 'F8FAFC'])
                    ->addText($p['colis'] === null ? 'Colis : '.$dots : 'Colis : '.$nf($p['colis']), ['bold' => true], ['spaceAfter' => 0]);
            }

            $footer = $section->addFooter();
            $ft = $footer->addTable(['width' => 5000, 'unit' => 'pct']);
            $ft->addRow();
            foreach ($meta['footer'] as $i => [$label, $name]) {
                $cell = $ft->addCell(3000);
                $cell->addText($label, ['bold' => true], ['alignment' => $i === 0 ? Jc::START : ($i === 1 ? Jc::CENTER : Jc::END), 'spaceAfter' => 400]);
                $cell->addText($name !== '' ? $name : $dots, [], ['alignment' => $i === 0 ? Jc::START : ($i === 1 ? Jc::CENTER : Jc::END), 'spaceAfter' => 0]);
            }
        }

        $tmp = tempnam(sys_get_temp_dir(), 'dsp').'.docx';
        \PhpOffice\PhpWord\IOFactory::createWriter($word, 'Word2007')->save($tmp);

        return response()->download($tmp, $this->fileName($meta, $request, 'docx'))->deleteFileAfterSend(true);
    }

    private function fileName(array $meta, Request $request, string $ext): string
    {
        $part = \Illuminate\Support\Str::slug(trim($request->query('point', '') ?: $request->query('axe', '') ?: 'tous'));

        return $meta['file'].'_'.$part.'.'.$ext;
    }
}
