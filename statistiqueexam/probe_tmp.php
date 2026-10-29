<?php

require './vendor/autoload.php';
$app = require './bootstrap/app.php';

$compiler = $app['blade.compiler'];
$views = [
    'resources/views/vacation-2026/dashboards/eps.blade.php',
    'resources/views/vacation-2026/dashboards/centre-single.blade.php',
    'resources/views/vacation-2026/dashboards/cisco-single.blade.php',
    'resources/views/admin/references/index.blade.php',
];
foreach ($views as $view) {
    try {
        $compiled = $compiler->compile($view);
        echo "BLADE_OK: {$view} ({strlen((string) $compiled ?? '')} bytes)\n";
    } catch (\Throwable $e) {
        echo "BLADE_FAIL: {$view} -> {$e->getMessage()}\n";
    }
}