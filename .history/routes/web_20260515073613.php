<?php

use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ReferenceManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BepcRepartitionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DecisionCentreController;
use App\Http\Controllers\ReferenceImportController;
use App\Http\Controllers\RepartitionReportController;
use App\Http\Controllers\Vacation2026Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'bepc.repartition.create' : 'login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'nonlogistique'])->group(function () {
    Route::get('/bepc/repartition', [BepcRepartitionController::class, 'create'])
        ->name('bepc.repartition.create');
    Route::post('/bepc/repartition', [BepcRepartitionController::class, 'store'])
        ->name('bepc.repartition.store');
    Route::get('/repartition/livre/preview', [RepartitionReportController::class, 'livrePreview'])
        ->name('repartition.livre.preview');
    Route::get('/repartition/livre/controle', [RepartitionReportController::class, 'livreControle'])
        ->name('repartition.livre.controle');
    Route::get('/repartition/livre/controle/auto', [RepartitionReportController::class, 'livreControleAuto'])
        ->name('repartition.livre.controle.auto');
    Route::match(['get', 'post'], '/repartition/livre/controle/export/word', [RepartitionReportController::class, 'livreControleWord'])
        ->name('repartition.livre.controle.word');
    Route::match(['get', 'post'], '/repartition/livre/pv/auto/word', [RepartitionReportController::class, 'livrePvAutoWord'])
        ->name('repartition.livre.pv.auto.word');
    Route::get('/repartition/livre/pdf', [RepartitionReportController::class, 'livrePdf'])
        ->name('repartition.livre.pdf');
    Route::get('/repartition/livre/export/xlsx', [RepartitionReportController::class, 'livreExcel'])
        ->name('repartition.livre.excel');
    Route::get('/imports', [ReferenceImportController::class, 'index'])
        ->name('imports.index');
    Route::post('/imports/references', [ReferenceImportController::class, 'importReferences'])
        ->name('imports.references');
    Route::post('/imports/drens', [ReferenceImportController::class, 'importDrens'])
        ->name('imports.drens');
    Route::post('/imports/ciscos', [ReferenceImportController::class, 'importCiscos'])
        ->name('imports.ciscos');
    Route::post('/imports/centres-correction', [ReferenceImportController::class, 'importCentresCorrection'])
        ->name('imports.centres.correction');
    Route::post('/imports/centres-ecrit', [ReferenceImportController::class, 'importCentresEcrit'])
        ->name('imports.centres.ecrit');
});

Route::middleware('auth')->group(function () {
    Route::get('/repartition/dashboard', [RepartitionReportController::class, 'dashboard'])
        ->name('repartition.dashboard');
    Route::put('/repartition/besoins-specifiques/{candidate}', [RepartitionReportController::class, 'updateSpecialCandidate'])
        ->whereNumber('candidate')
        ->name('repartition.special-candidates.update');
    Route::get('/repartition/simulation/soubique', [RepartitionReportController::class, 'subjectSoubiqueSimulation'])
        ->name('repartition.simulation.soubique');
    Route::get('/repartition/simulation/soubique/pv', [RepartitionReportController::class, 'subjectSoubiquePv'])
        ->name('repartition.simulation.soubique.pv');
    Route::get('/repartition/tirage', [RepartitionReportController::class, 'subjectPrintSimulation'])
        ->name('repartition.tirage');
    Route::get('/repartition/tirage/export/xlsx', [RepartitionReportController::class, 'subjectPrintExcel'])
        ->name('repartition.tirage.excel');
    Route::get('/repartition/groupes', [RepartitionReportController::class, 'groupes'])
        ->name('repartition.groupes');
    Route::get('/repartition/recap-saisie', [RepartitionReportController::class, 'saisieRecap'])
        ->name('repartition.saisie.recap');
    Route::get('/repartition/statistiques/rapport', [RepartitionReportController::class, 'statsReport'])
        ->name('repartition.stats.report');
    Route::get('/repartition/statistiques/rapport/pdf', [RepartitionReportController::class, 'statsReportPdf'])
        ->name('repartition.stats.report.pdf');
    Route::get('/repartition/statistiques/rapport/word', [RepartitionReportController::class, 'statsReportWord'])
        ->name('repartition.stats.report.word');
    Route::get('/repartition/statistiques/rapport/simple/word', [RepartitionReportController::class, 'statsReportWordSimple'])
        ->name('repartition.stats.report.simple.word');
    Route::get('/repartition/statistiques/rapport/simple/pdf', [RepartitionReportController::class, 'statsReportPdfSimple'])
        ->name('repartition.stats.report.simple.pdf');
    Route::get('/repartition/statistiques/rapport/centres/xlsx', [RepartitionReportController::class, 'statsReportCentresExcel'])
        ->name('repartition.stats.report.centres.excel');
    Route::get('/repartition/export/excel', [RepartitionReportController::class, 'exportExcel'])
        ->name('repartition.export.excel');
    Route::get('/repartition/export/dispatching/preview', [RepartitionReportController::class, 'dispatchingPreview'])
        ->name('repartition.export.dispatching.preview');
    Route::get('/repartition/export/dispatching', [RepartitionReportController::class, 'exportDispatchingExcel'])
        ->name('repartition.export.dispatching');
    Route::get('/repartition/options-langues/stats', [RepartitionReportController::class, 'languageOptionStats'])
        ->name('repartition.options.langues.stats');
});

Route::middleware(['auth', 'logistics'])->group(function () {
    Route::get('/repartition/livraison/cepe', [RepartitionReportController::class, 'cepeLivraison'])
        ->name('repartition.livraison.cepe');
    Route::get('/repartition/livraison/cepe/export/xlsx', [RepartitionReportController::class, 'cepeLivraisonExcel'])
        ->name('repartition.livraison.cepe.excel');

    Route::get('/repartition/logistique/bepc-feuilles', [RepartitionReportController::class, 'bepcCopies'])
        ->name('repartition.logistique.bepc-copies');
    Route::post('/repartition/logistique/bepc-feuilles/code-postal', [RepartitionReportController::class, 'saveBepcCopyPostalCode'])
        ->name('repartition.logistique.bepc-copies.postal-code');
    Route::get('/repartition/logistique/bepc-feuilles/export/xlsx', [RepartitionReportController::class, 'bepcCopiesExcel'])
        ->name('repartition.logistique.bepc-copies.excel');
    Route::get('/repartition/logistique/bepc-feuilles/export/word', [RepartitionReportController::class, 'bepcCopiesWord'])
        ->name('repartition.logistique.bepc-copies.word');
    Route::get('/repartition/logistique/bepc-feuilles/pdf', [RepartitionReportController::class, 'bepcCopiesPdf'])
        ->name('repartition.logistique.bepc-copies.pdf');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/repartition/vacations', [RepartitionReportController::class, 'vacations'])
        ->name('repartition.vacations');
    Route::get('/vacation-2026', [Vacation2026Controller::class, 'index'])
        ->name('vacation2026.index');
    Route::post('/vacation-2026/import', [Vacation2026Controller::class, 'import'])
        ->name('vacation2026.import');
    Route::post('/vacation-2026/settings', [Vacation2026Controller::class, 'updateSetting'])
        ->name('vacation2026.settings.update');
    Route::post('/vacation-2026/assignments', [Vacation2026Controller::class, 'assign'])
        ->name('vacation2026.assignments.store');
    Route::delete('/vacation-2026/assignments/{assignment}', [Vacation2026Controller::class, 'removeAssignment'])
        ->name('vacation2026.assignments.destroy');
    Route::put('/vacation-2026/activities/{activity}', [Vacation2026Controller::class, 'updateActivity'])
        ->name('vacation2026.activities.update');
    Route::post('/vacation-2026/activities', [Vacation2026Controller::class, 'storeActivity'])
        ->name('vacation2026.activities.store');
    Route::get('/vacation-2026/exports/{document}/word', [Vacation2026Controller::class, 'exportWord'])
        ->name('vacation2026.exports.word');
    Route::get('/vacation-2026/exports/{document}/xlsx', [Vacation2026Controller::class, 'exportExcel'])
        ->name('vacation2026.exports.xlsx');
    Route::get('/vacation-2026/exports/{document}/pdf', [Vacation2026Controller::class, 'exportPdf'])
        ->name('vacation2026.exports.pdf');

    Route::get('/admin/users', [UserManagementController::class, 'index'])
        ->name('admin.users.index');
    Route::post('/admin/users', [UserManagementController::class, 'store'])
        ->name('admin.users.store');
    Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])
        ->name('admin.users.update');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])
        ->name('admin.users.destroy');

    Route::get('/admin/statistics', [StatisticController::class, 'index'])
        ->name('admin.statistics.index');
    Route::put('/admin/statistics/dispatching/centre', [StatisticController::class, 'updateCentreDispatching'])
        ->name('admin.statistics.dispatching.update');
    Route::put('/admin/statistics/{stat}', [StatisticController::class, 'update'])
        ->name('admin.statistics.update');
    Route::post('/admin/statistics/bulk-update', [StatisticController::class, 'updateBulk'])
        ->name('admin.statistics.bulk-update');
    Route::post('/admin/statistics/settings/general', [StatisticController::class, 'updateGeneralSettings'])
        ->name('admin.statistics.settings.general');
    Route::delete('/admin/statistics/centre/{centreEcritId}', [StatisticController::class, 'destroyCentre'])
        ->name('admin.statistics.destroy-centre');

    Route::get('/admin/references', [ReferenceManagementController::class, 'index'])
        ->name('admin.references.index');
    Route::get('/admin/audit-logs', [AuditLogController::class, 'index'])
        ->name('admin.audit-logs.index');
    Route::post('/admin/references/drens', [ReferenceManagementController::class, 'storeDren'])
        ->name('admin.references.drens.store');
    Route::put('/admin/references/drens/{dren}', [ReferenceManagementController::class, 'updateDren'])
        ->name('admin.references.drens.update');
    Route::delete('/admin/references/drens/{dren}', [ReferenceManagementController::class, 'destroyDren'])
        ->name('admin.references.drens.destroy');
    Route::post('/admin/references/ciscos', [ReferenceManagementController::class, 'storeCisco'])
        ->name('admin.references.ciscos.store');
    Route::put('/admin/references/ciscos/{cisco}', [ReferenceManagementController::class, 'updateCisco'])
        ->name('admin.references.ciscos.update');
    Route::delete('/admin/references/ciscos/{cisco}', [ReferenceManagementController::class, 'destroyCisco'])
        ->name('admin.references.ciscos.destroy');
    Route::post('/admin/references/centres-correction', [ReferenceManagementController::class, 'storeCentreCorrection'])
        ->name('admin.references.centres-correction.store');
    Route::put('/admin/references/centres-correction/{centreCorrection}', [ReferenceManagementController::class, 'updateCentreCorrection'])
        ->name('admin.references.centres-correction.update');
    Route::delete('/admin/references/centres-correction/{centreCorrection}', [ReferenceManagementController::class, 'destroyCentreCorrection'])
        ->name('admin.references.centres-correction.destroy');
    Route::post('/admin/references/centres-ecrit', [ReferenceManagementController::class, 'storeCentreEcrit'])
        ->name('admin.references.centres-ecrit.store');
    Route::put('/admin/references/centres-ecrit/{centreEcrit}', [ReferenceManagementController::class, 'updateCentreEcrit'])
        ->name('admin.references.centres-ecrit.update');
    Route::delete('/admin/references/centres-ecrit/{centreEcrit}', [ReferenceManagementController::class, 'destroyCentreEcrit'])
        ->name('admin.references.centres-ecrit.destroy');
    Route::post('/admin/references/dispatching-axes', [ReferenceManagementController::class, 'storeDispatchingAxis'])
        ->name('admin.references.dispatching-axes.store');
    Route::put('/admin/references/dispatching-axes/{index}', [ReferenceManagementController::class, 'updateDispatchingAxis'])
        ->whereNumber('index')
        ->name('admin.references.dispatching-axes.update');
    Route::delete('/admin/references/dispatching-axes/{index}', [ReferenceManagementController::class, 'destroyDispatchingAxis'])
        ->whereNumber('index')
        ->name('admin.references.dispatching-axes.destroy');
    Route::post('/admin/references/dispatching-drop-points', [ReferenceManagementController::class, 'storeDispatchingDropPoint'])
        ->name('admin.references.dispatching-drop-points.store');
    Route::put('/admin/references/dispatching-drop-points/{index}', [ReferenceManagementController::class, 'updateDispatchingDropPoint'])
        ->whereNumber('index')
        ->name('admin.references.dispatching-drop-points.update');
    Route::delete('/admin/references/dispatching-drop-points/{index}', [ReferenceManagementController::class, 'destroyDispatchingDropPoint'])
        ->whereNumber('index')
        ->name('admin.references.dispatching-drop-points.destroy');
    Route::post('/admin/references/special-candidates', [ReferenceManagementController::class, 'storeSpecialCandidates'])
        ->name('admin.references.special-candidates.store');

    Route::post('/repartition/statistiques/rapport/import', [RepartitionReportController::class, 'importPreviousStats'])
        ->name('repartition.stats.report.import');
    Route::post('/repartition/statistiques/rapport/import-dren', [RepartitionReportController::class, 'importPreviousDrenRecap'])
        ->name('repartition.stats.report.import-dren');
});

Route::get('/decision-centre', [DecisionCentreController::class, 'index'])->name('decision.centre');

Route::get('/repartition/centres-saisie', [RepartitionReportController::class, 'centresSaisie'])
    ->name('repartition.centres.saisie');
Route::get('/repartition/centres-saisie/pdf', [RepartitionReportController::class, 'centresSaisiePdf'])
    ->name('repartition.centres.saisie.pdf');
