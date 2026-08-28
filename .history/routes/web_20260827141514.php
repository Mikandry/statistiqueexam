<?php

use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\ReferenceManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BepcRepartitionController;
use App\Http\Controllers\CapCaeResultController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DecisionCentreController;
use App\Http\Controllers\ExamResultController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\HrAgentController;
use App\Http\Controllers\HrDashboardController;
use App\Http\Controllers\HrDocumentController;
use App\Http\Controllers\HrPersonnelController;
use App\Http\Controllers\Admin\HrSettingsController;
use App\Http\Controllers\ReferenceImportController;
use App\Http\Controllers\RepartitionReportController;
use App\Http\Controllers\Vacation2026Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'bepc.repartition.create' : 'login');
});

Route::middleware('auth')->group(function () {
    // Page d'accueil (Hub de choix d'espace)
    Route::get('/home', [HomeCo::class, 'index'])->name('home');

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
    Route::get('/resultats-cap-cae', [CapCaeResultController::class, 'index'])
        ->name('cap-cae-results.index');
    Route::post('/resultats-cap-cae/import', [CapCaeResultController::class, 'import'])
        ->name('cap-cae-results.import');
    Route::put('/resultats-cap-cae/{batch}/settings', [CapCaeResultController::class, 'updateSettings'])
        ->name('cap-cae-results.settings');
    Route::get('/resultats-cap-cae/{batch}/preview', [CapCaeResultController::class, 'preview'])
        ->name('cap-cae-results.preview');
    Route::get('/resultats-cap-cae/{batch}/pdf', [CapCaeResultController::class, 'exportPdf'])
        ->name('cap-cae-results.pdf');
    Route::get('/resultats-cap-cae/{batch}/excel', [CapCaeResultController::class, 'exportExcel'])
        ->name('cap-cae-results.excel');
    Route::get('/resultats-cap-cae/{batch}/diplomes/preview', [CapCaeResultController::class, 'diplomaPreview'])
        ->name('cap-cae-results.diploma.preview');
    Route::get('/resultats-cap-cae/{batch}/diplomes/pdf', [CapCaeResultController::class, 'diplomaPdf'])
        ->name('cap-cae-results.diploma.pdf');
    Route::get('/resultats-cap-cae/{batch}/diplomes/excel', [CapCaeResultController::class, 'diplomaExcel'])
        ->name('cap-cae-results.diploma.excel');

    Route::get('/bepc/repartition', [BepcRepartitionController::class, 'create'])
        ->name('bepc.repartition.create');
    Route::post('/bepc/repartition', [BepcRepartitionController::class, 'store'])
        ->name('bepc.repartition.store');
    Route::get('/repartition/livre/preview', [RepartitionReportController::class, 'livrePreview'])
        ->name('repartition.livre.preview');
    Route::get('/repartition/livre/pe-ge', [RepartitionReportController::class, 'livrePeGe'])
        ->name('repartition.livre.pe-ge');
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
//old HR
// Route::middleware('auth')->group(function () {
//     Route::get('/rh', [HrDashboardController::class, 'index'])
//         ->middleware('nonlogistique')
//         ->name('hr.dashboard');
//     Route::get('/rh/personnel', [HrPersonnelController::class, 'index'])
//         ->middleware('nonlogistique')
//         ->name('hr.agents.index');
//     Route::post('/rh/personnel', [HrAgentController::class, 'store'])
//         ->middleware('nonlogistique')
//         ->name('hr.agents.store');
//     Route::put('/rh/personnel/{agent}', [HrAgentController::class, 'update'])
//         ->middleware('nonlogistique')
//         ->name('hr.agents.update');
//     Route::post('/rh/personnel/{agent}/affectations', [HrAgentController::class, 'storeAssignment'])
//         ->middleware('nonlogistique')
//         ->name('hr.agents.assignments.store');
//     Route::post('/rh/personnel/{agent}/evenements', [HrAgentController::class, 'storeEvent'])
//         ->middleware('nonlogistique')
//         ->name('hr.agents.events.store');
//     Route::get('/rh/personnel/{agent}/documents/conge/{event}', [HrDocumentController::class, 'leave'])
//         ->middleware('nonlogistique')->name('hr.documents.leave');
//     Route::get('/rh/personnel/{agent}/documents/non-jouissance', [HrDocumentController::class, 'nonLeave'])
//         ->middleware('nonlogistique')->name('hr.documents.non-leave');
//     Route::get('/rh/personnel/{agent}/documents/absence/{event}', [HrDocumentController::class, 'absence'])
//         ->middleware('nonlogistique')->name('hr.documents.absence');
//     Route::get('/rh/personnel/{agent}/documents/mission/{event}', [HrDocumentController::class, 'mission'])
//         ->middleware('nonlogistique')->name('hr.documents.mission');
//     Route::get('/rh/personnel/{agent}/documents/formation/{event}', [HrDocumentController::class, 'training'])
//         ->middleware('nonlogistique')->name('hr.documents.training');
//     Route::get('/rh/personnel/{agent}/documents/{document}/preview/{event?}', [HrDocumentController::class, 'preview'])
//         ->whereIn('document', ['non-jouissance', 'conge', 'absence', 'mission', 'formation'])
//         ->middleware('nonlogistique')->name('hr.documents.preview');
//     Route::get('/rh/personnel/{agent}/documents/{document}/word/{event?}', [HrDocumentController::class, 'word'])
//         ->whereIn('document', ['non-jouissance', 'conge', 'absence', 'mission', 'formation'])
//         ->middleware('nonlogistique')->name('hr.documents.word');

//     Route::get('/resultats-examens', [ExamResultController::class, 'index'])
//         ->name('exam-results.index');
//     Route::post('/resultats-examens', [ExamResultController::class, 'store'])
//         ->name('exam-results.store');
//     Route::put('/resultats-examens/{examResult}', [ExamResultController::class, 'update'])
//         ->name('exam-results.update');
//     Route::delete('/resultats-examens/{examResult}', [ExamResultController::class, 'destroy'])
//         ->name('exam-results.destroy');
//     Route::post('/resultats-examens/{examResult}/publier', [ExamResultController::class, 'publish'])
//         ->name('exam-results.publish');
//     Route::get('/resultats-examens/export/excel', [ExamResultController::class, 'exportExcel'])
//         ->name('exam-results.export.excel');
//     Route::get('/resultats-examens/export/pdf', [ExamResultController::class, 'exportPdf'])
//         ->name('exam-results.export.pdf');

//     Route::get('/repartition/dashboard', [RepartitionReportController::class, 'dashboard'])
//         ->name('repartition.dashboard');
//     Route::put('/repartition/besoins-specifiques/{candidate}', [RepartitionReportController::class, 'updateSpecialCandidate'])
//         ->whereNumber('candidate')
//         ->name('repartition.special-candidates.update');
//     Route::get('/repartition/simulation/soubique', [RepartitionReportController::class, 'subjectSoubiqueSimulation'])
//         ->name('repartition.simulation.soubique');
//     Route::get('/repartition/simulation/soubique/pv', [RepartitionReportController::class, 'subjectSoubiquePv'])
//         ->name('repartition.simulation.soubique.pv');
//     Route::get('/repartition/tirage', [RepartitionReportController::class, 'subjectPrintSimulation'])
//         ->name('repartition.tirage');
//     Route::get('/repartition/tirage/export/xlsx', [RepartitionReportController::class, 'subjectPrintExcel'])
//         ->name('repartition.tirage.excel');
//     Route::get('/repartition/groupes', [RepartitionReportController::class, 'groupes'])
//         ->name('repartition.groupes');
//     Route::get('/repartition/recap-saisie', [RepartitionReportController::class, 'saisieRecap'])
//         ->name('repartition.saisie.recap');
//     Route::get('/repartition/statistiques/rapport', [RepartitionReportController::class, 'statsReport'])
//         ->name('repartition.stats.report');
//     Route::get('/repartition/statistiques/rapport/pdf', [RepartitionReportController::class, 'statsReportPdf'])
//         ->name('repartition.stats.report.pdf');
//     Route::get('/repartition/statistiques/rapport/word', [RepartitionReportController::class, 'statsReportWord'])
//         ->name('repartition.stats.report.word');
//     Route::get('/repartition/statistiques/rapport/simple/word', [RepartitionReportController::class, 'statsReportWordSimple'])
//         ->name('repartition.stats.report.simple.word');
//     Route::get('/repartition/statistiques/rapport/simple/pdf', [RepartitionReportController::class, 'statsReportPdfSimple'])
//         ->name('repartition.stats.report.simple.pdf');
//     Route::get('/repartition/statistiques/rapport/centres/xlsx', [RepartitionReportController::class, 'statsReportCentresExcel'])
//         ->name('repartition.stats.report.centres.excel');
//     Route::get('/repartition/export/excel', [RepartitionReportController::class, 'exportExcel'])
//         ->name('repartition.export.excel');
//     Route::get('/repartition/export/dispatching/preview', [RepartitionReportController::class, 'dispatchingPreview'])
//         ->name('repartition.export.dispatching.preview');
//     Route::get('/repartition/export/dispatching', [RepartitionReportController::class, 'exportDispatchingExcel'])
//         ->name('repartition.export.dispatching');
//     Route::get('/repartition/options-langues/stats', [RepartitionReportController::class, 'languageOptionStats'])
//         ->name('repartition.options.langues.stats');
// });
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Tableau de bord RH
    |--------------------------------------------------------------------------
    */

    Route::get('/rh', [
        HrDashboardController::class,
        'index'
    ])
        ->middleware('nonlogistique')
        ->name('hr.dashboard');

    Route::get('/rh/mon-dossier', [
        HrPersonnelController::class,
        'myProfile',
    ])->middleware('nonlogistique')->name('hr.my-profile');

    Route::post('/rh/mon-dossier/evenements', [
        HrAgentController::class,
        'storeOwnEvent',
    ])->middleware('nonlogistique')->name('hr.my-events.store');


    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATION DU PERSONNEL
    |--------------------------------------------------------------------------
    */

    Route::middleware('admin')->group(function () {

        Route::get('/rh/personnel', [
            HrPersonnelController::class,
            'index'
        ])->name('hr.agents.index');

        Route::get('/rh/personnel/{agent}', [
            HrPersonnelController::class,
            'show'
        ])->name('hr.agents.show');

        Route::post('/rh/personnel', [
            HrAgentController::class,
            'store'
        ])->name('hr.agents.store');

        Route::put('/rh/personnel/{agent}', [
            HrAgentController::class,
            'update'
        ])->name('hr.agents.update');

        Route::post('/rh/personnel/{agent}/affectations', [
            HrAgentController::class,
            'storeAssignment'
        ])->name('hr.agents.assignments.store');

        Route::post('/rh/personnel/{agent}/evenements', [
            HrAgentController::class,
            'storeEvent'
        ])->name('hr.agents.events.store');

        Route::put('/rh/personnel/{agent}/evenements/{event}/statut', [
            HrAgentController::class,
            'updateEventStatus',
        ])->name('hr.agents.events.status');
    });


    /*
    |--------------------------------------------------------------------------
    | DOCUMENTS
    |--------------------------------------------------------------------------
    |
    | Admin → n'importe quel agent
    | User  → son propre agent
    |
    */

    Route::get(
        '/rh/personnel/{agent}/documents/conge/{event}',
        [HrDocumentController::class, 'leave']
    )
        ->middleware('nonlogistique')
        ->name('hr.documents.leave');

    Route::get(
        '/rh/personnel/{agent}/documents/non-jouissance',
        [HrDocumentController::class, 'nonLeave']
    )
        ->middleware('nonlogistique')
        ->name('hr.documents.non-leave');

    Route::get(
        '/rh/personnel/{agent}/documents/absence/{event}',
        [HrDocumentController::class, 'absence']
    )
        ->middleware('nonlogistique')
        ->name('hr.documents.absence');

    Route::get(
        '/rh/personnel/{agent}/documents/mission/{event}',
        [HrDocumentController::class, 'mission']
    )
        ->middleware('nonlogistique')
        ->name('hr.documents.mission');

    Route::get(
        '/rh/personnel/{agent}/documents/formation/{event}',
        [HrDocumentController::class, 'training']
    )
        ->middleware('nonlogistique')
        ->name('hr.documents.training');

    Route::get(
        '/rh/personnel/{agent}/documents/{document}/preview/{event?}',
        [HrDocumentController::class, 'preview']
    )
        ->whereIn(
            'document',
            [
                'non-jouissance',
                'conge',
                'absence',
                'mission',
                'formation'
            ]
        )
        ->middleware('nonlogistique')
        ->name('hr.documents.preview');
        Route::get(
    '/rh/personnel/{agent}/documents/non-interruption',
    [HrDocumentController::class, 'nonInterruption']
)->middleware('nonlogistique')
 ->name('hr.documents.non-interruption');

        Route::get(
            '/rh/personnel/{agent}/documents/prise-service',
            [HrDocumentController::class, 'serviceStart']
        )->middleware('nonlogistique')
         ->name('hr.documents.service-start');

    Route::get(
        '/rh/personnel/{agent}/documents/administrative',
        [HrDocumentController::class, 'administrative']
    )->middleware('nonlogistique')
     ->name('hr.documents.administrative');

    Route::get(
        '/rh/personnel/{agent}/documents',
        [HrDocumentController::class, 'index']
    )->middleware('nonlogistique')
     ->name('hr.documents.index');

Route::get(
    '/rh/personnel/{agent}/documents/autre/{event?}',
    [HrDocumentController::class, 'other']
)->middleware('nonlogistique')
 ->name('hr.documents.other');
 Route::get(
    '/rh/personnel/{agent}/documents/{document}/preview/{event?}',
    [HrDocumentController::class, 'preview']
)
->whereIn('document', [
    'non-interruption',
    'prise-service',
    'non-jouissance',
    'conge',
    'absence',
    'mission',
    'formation',
    'autre',
    'fiche-administrative',
])
->middleware('nonlogistique')
->name('hr.documents.preview');


Route::get(
    '/rh/personnel/{agent}/documents/{document}/word/{event?}',
    [HrDocumentController::class, 'word']
)
->whereIn('document', [
    'non-interruption',
    'prise-service',
    'non-jouissance',
    'conge',
    'absence',
    'mission',
    'formation',
    'autre',
    'fiche-administrative',
])
->middleware('nonlogistique')
->name('hr.documents.word');


Route::get(
    '/rh/personnel/{agent}/documents/{document}/pdf/{event?}',
    [HrDocumentController::class, 'pdf']
)
->whereIn('document', [
    'non-interruption',
    'prise-service',
    'non-jouissance',
    'conge',
    'absence',
    'mission',
    'formation',
    'autre',
    'fiche-administrative',
])
->middleware('nonlogistique')
->name('hr.documents.pdf');

    Route::get(
        '/rh/personnel/{agent}/documents/{document}/word/{event?}',
        [HrDocumentController::class, 'word']
    )
        ->whereIn(
            'document',
            [
                'non-interruption',
                'prise-service',
                'non-jouissance',
                'conge',
                'absence',
                'mission',
                'formation'
            ]
        )
        ->middleware('nonlogistique')
        ->name('hr.documents.word');
        Route::get('/resultats-examens', [ExamResultController::class, 'index'])
        ->name('exam-results.index');
    Route::post('/resultats-examens', [ExamResultController::class, 'store'])
        ->name('exam-results.store');
    Route::put('/resultats-examens/{examResult}', [ExamResultController::class, 'update'])
        ->name('exam-results.update');
    Route::delete('/resultats-examens/{examResult}', [ExamResultController::class, 'destroy'])
        ->name('exam-results.destroy');
    Route::post('/resultats-examens/{examResult}/publier', [ExamResultController::class, 'publish'])
        ->name('exam-results.publish');
    Route::get('/resultats-examens/export/excel', [ExamResultController::class, 'exportExcel'])
        ->name('exam-results.export.excel');
    Route::get('/resultats-examens/export/pdf', [ExamResultController::class, 'exportPdf'])
        ->name('exam-results.export.pdf');

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
    Route::get('/logistique/comptabilite-matieres', [InventoryController::class, 'index'])
        ->name('inventory.index');
    Route::post('/logistique/comptabilite-matieres/materiels', [InventoryController::class, 'storeMaterial'])
        ->name('inventory.materials.store');
    Route::put('/logistique/comptabilite-matieres/materiels/{material}', [InventoryController::class, 'updateMaterial'])
        ->name('inventory.materials.update');
    Route::delete('/logistique/comptabilite-matieres/materiels/{material}', [InventoryController::class, 'destroyMaterial'])
        ->name('inventory.materials.destroy');
    Route::post('/logistique/comptabilite-matieres/mouvements', [InventoryController::class, 'storeMovement'])
        ->name('inventory.movements.store');
    Route::post('/logistique/comptabilite-matieres/fournisseurs', [InventoryController::class, 'storeSupplier'])
        ->name('inventory.suppliers.store');
    Route::post('/logistique/comptabilite-matieres/commandes', [InventoryController::class, 'storeOrder'])
        ->name('inventory.orders.store');
    Route::get('/logistique/comptabilite-matieres/export/excel', [InventoryController::class, 'exportExcel'])
        ->name('inventory.export.excel');
    Route::get('/logistique/comptabilite-matieres/export/pdf', [InventoryController::class, 'exportPdf'])
        ->name('inventory.export.pdf');

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
    Route::get('/admin/rh/parametres', [HrSettingsController::class, 'index'])->name('admin.hr.settings');
    Route::put('/admin/rh/parametres', [HrSettingsController::class, 'update'])->name('admin.hr.settings.update');
    Route::put('/admin/rh/parametres/champs', [HrSettingsController::class, 'updateFields'])->name('admin.hr.settings.fields.update');
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
