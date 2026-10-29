<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\VacationEstimationService;
use App\Services\VacationDecreeService;

$svc = new VacationEstimationService(new VacationDecreeService());
$start = microtime(true);
$est = $svc->buildEstimate();
$elapsed = round(microtime(true) - $start, 2);

echo "=== TOTAUX (secondes: $elapsed) ===\n";
foreach ($est['totals'] as $k => $v) {
    if (is_array($v)) {
        continue;
    }
    echo '  ' . $k . ': ' . (is_float($v) ? number_format($v, 0, ',', ' ') : $v) . "\n";
}
echo "types de centres: " . json_encode($est['totals']['centre_types']) . "\n";

echo "\n=== PAR EXAMEN ===\n";
foreach ($est['by_exam'] as $e => $d) {
    echo "  $e: agents={$d['agents']} montant=" . number_format($d['montant'], 0, ',', ' ') . " activites={$d['activites']}\n";
}

echo "\n=== PAR PHASE (agents par session) ===\n";
foreach ($est['by_phase'] as $e => $d) {
    echo "  $e: agents={$d['agents']} montant=" . number_format($d['montant'], 0, ',', ' ') . "\n";
}

echo "\n=== PAR ACTIVITE (top 15) ===\n";
$i = 0;
foreach ($est['by_activity'] as $a) {
    if ($i++ >= 15) break;
    echo "  {$a['examen']} [{$a['niveau']}] {$a['libelle']}: agents={$a['agents']} j={$a['jours']} taux={$a['rate']} montant=" . number_format($a['montant'], 0, ',', ' ') . " affectes={$a['assigned']}\n";
}

echo "\n=== SYNTHESE (top 10) ===\n";
$i = 0;
foreach ($est['synthese'] as $a) {
    if ($i++ >= 10) break;
    echo "  {$a['examen']} [{$a['niveau']}] {$a['libelle']}: besoin={$a['agents']} affectes={$a['assigned']} ecart={$a['ecart']} montant=" . number_format($a['montant'], 0, ',', ' ') . "\n";
}

echo "\n=== TOTAL ROWS: " . $est['rows']->count() . "\n";
echo "=== PAR DREN count: " . $est['by_dren']->count() . "\n";
echo "=== PAR CISCO count: " . $est['by_cisco']->count() . "\n";
echo "=== PAR CENTRE count: " . $est['by_centre']->count() . "\n";

echo "\n=== SIMULATION exemple (filtre centre 5) ===\n";
$e2 = $svc->buildEstimate('', '', null, null, null, 5);
echo "  centres=" . $e2['totals']['centres'] . " candidats=" . $e2['totals']['candidats'] . " salles=" . $e2['totals']['salles'] . " agents=" . $e2['totals']['agents_estimes'] . " montant=" . number_format($e2['totals']['montant_estime'], 0, ',', ' ') . "\n";
foreach ($e2['by_activity'] as $a) {
    echo "    {$a['examen']} [{$a['niveau']}] {$a['libelle']}: agents={$a['agents']} j={$a['jours']} montant=" . number_format($a['montant'], 0, ',', ' ') . "\n";
}