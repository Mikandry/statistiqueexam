<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$rows = DB::table('repartition_salles as rs')
    ->join('centre_ecrits as ce', 'ce.id', '=', 'rs.centre_ecrit_id')
    ->join('centre_corrections as cc', 'cc.id', '=', 'ce.centre_correction_id')
    ->join('ciscos as cs', 'cs.id', '=', 'cc.cisco_id')
    ->join('drens as d', 'd.id', '=', 'cs.dren_id')
    ->selectRaw('rs.langue, COUNT(*) as rows, COUNT(DISTINCT CONCAT(centre_ecrit_id, "-", numero_salle)) as salles, COUNT(DISTINCT CONCAT(d.nom, "-", cs.nom, "-", centre_ecrit_id, "-", numero_salle)) as salles_by_cisco')
    ->whereIn('rs.langue', ['Anglais', 'Allemand'])
    ->groupBy('rs.langue')
    ->get();
foreach ($rows as $r) {
    echo $r->langue . "\t" . $r->rows . "\t" . $r->salles . "\t" . $r->salles_by_cisco . "\n";
}
