<?php
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = DB::table('repartition_salles as rs')
    ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
    ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
    ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
    ->join('drens as d', 'd.id', '=', 'cs.dren_id')
    ->select('d.nom as dren', 'cs.nom as cisco', 'rs.centre_ecrit_id', 'rs.numero_salle', 'rs.langue', 'rs.effectif')
    ->where('d.nom', 'ALAOTRA MANGORO')
    ->where('cs.nom', 'AMBATONDRAZAKA')
    ->whereIn('rs.langue', ['Anglais', 'Allemand'])
    ->limit(40)
    ->get();

foreach ($rows as $r) {
    echo $r->centre_ecrit_id . "\t" . $r->numero_salle . "\t" . $r->langue . "\t" . $r->effectif . "\n";
}
