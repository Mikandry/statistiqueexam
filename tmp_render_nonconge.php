<?php

// Test de non-régression : une attestation non-jouissance ne doit PAS contenir
// la nouvelle zone de validation (réservée à la fiche de congé).

use App\Models\HrAgent;
use App\Models\HrDocumentSetting;
use App\Models\HrEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

$settings = HrDocumentSetting::query()->first();
$agent = HrAgent::query()->first();

$data = [
    'agent' => $agent,
    'event' => null,
    'settings' => $settings,
    'document' => 'non-jouissance',
    'title' => 'ATTESTATION DE NON-JOUISSANCE DE CONGE',
    'today' => Carbon::today(),
    'reference' => 'N°' . now()->year . '/2 EN COURS',
    'period' => 'Année ' . now()->year,
];

$html = view('hr.documents.administrative', $data)->render();
file_put_contents('tmp-nonconge.html', $html);

echo 'Contient validation-zone : ' . (str_contains($html, 'validation-zone') ? 'OUI (PROBLÈME)' : 'non (OK)') . "\n";
echo 'Contient interessé : ' . (str_contains($html, 'interesse-box') ? 'OUI (PROBLÈME)' : 'non (OK)') . "\n";
echo 'Contient signature classique : ' . (str_contains($html, 'Antananarivo') ? 'oui' : 'non') . "\n";

$pdf = Pdf::loadView('hr.documents.administrative', $data)->setPaper('a4', 'portrait')->output();
file_put_contents('tmp-nonconge.pdf', $pdf);
echo 'PDF non-jouissance : ' . strlen($pdf) . ' octets' . "\n";