<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = DB::table('repartition_salles')->where('annee', 'like', '2026%')
    ->where(function ($q) { $q->whereNull('numero_salle')->orWhere('numero_salle', ''); })->count();
echo 'salles sans numero: '.$n.PHP_EOL;
echo 'salles numero non vide: '.DB::table('repartition_salles')->where('annee', 'like', '2026%')->whereNotNull('numero_salle')->where('numero_salle', '!=', '')->count().PHP_EOL;
foreach (DB::table('repartition_salles')->where('annee', 'like', '2026%')->whereNotNull('numero_salle')->where('numero_salle', '!=', '')->limit(8)->get() as $r) {
    echo '  ['.$r->numero_salle.'] effectif '.$r->effectif.' - annee '.$r->annee.PHP_EOL;
}