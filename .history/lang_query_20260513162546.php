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
    ->selectRaw('d.nom as dren, cs.nom as cisco, rs.langue, COUNT(DISTINCT CONCAT(centre_ecrit_id, "-", numero_salle)) as salles')
    ->whereRaw("rs.langue in ('Anglais', 'Allemand')")
    ->groupBy('d.nom', 'cs.nom', 'rs.langue')
    ->orderBy('d.nom')
    ->orderBy('cs.nom')
    ->orderBy('rs.langue')
    ->limit(30)
    ->get();

foreach ($rows as $r) {
    echo $r->dren . "\t" . $r->cisco . "\t" . $r->langue . "\t" . $r->salles . "\n";
}
