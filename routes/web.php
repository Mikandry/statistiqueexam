<?php

use App\Http\Controllers\Admin\StatisticController;
use App\Http\Controllers\Admin\ReferenceManagementController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\BepcRepartitionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReferenceImportController;
use App\Http\Controllers\RepartitionReportController;
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

Route::middleware('auth')->group(function () {
    Route::get('/bepc/repartition', [BepcRepartitionController::class, 'create'])
        ->name('bepc.repartition.create');
    Route::post('/bepc/repartition', [BepcRepartitionController::class, 'store'])
        ->name('bepc.repartition.store');

    Route::get('/repartition/dashboard', [RepartitionReportController::class, 'dashboard'])
        ->name('repartition.dashboard');
    Route::get('/repartition/livre/preview', [RepartitionReportController::class, 'livrePreview'])
        ->name('repartition.livre.preview');
    Route::get('/repartition/livre/pdf', [RepartitionReportController::class, 'livrePdf'])
        ->name('repartition.livre.pdf');
    Route::get('/repartition/export/excel', [RepartitionReportController::class, 'exportExcel'])
        ->name('repartition.export.excel');
    Route::get('/repartition/export/dispatching/preview', [RepartitionReportController::class, 'dispatchingPreview'])
        ->name('repartition.export.dispatching.preview');
    Route::get('/repartition/export/dispatching', [RepartitionReportController::class, 'exportDispatchingExcel'])
        ->name('repartition.export.dispatching');

    Route::get('/imports', [ReferenceImportController::class, 'index'])
        ->name('imports.index');
    Route::post('/imports/drens', [ReferenceImportController::class, 'importDrens'])
        ->name('imports.drens');
    Route::post('/imports/ciscos', [ReferenceImportController::class, 'importCiscos'])
        ->name('imports.ciscos');
    Route::post('/imports/centres-correction', [ReferenceImportController::class, 'importCentresCorrection'])
        ->name('imports.centres.correction');
    Route::post('/imports/centres-ecrit', [ReferenceImportController::class, 'importCentresEcrit'])
        ->name('imports.centres.ecrit');
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/repartition/vacations', [RepartitionReportController::class, 'vacations'])
        ->name('repartition.vacations');

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
    Route::delete('/admin/statistics/centre/{centreEcritId}', [StatisticController::class, 'destroyCentre'])
        ->name('admin.statistics.destroy-centre');

    Route::get('/admin/references', [ReferenceManagementController::class, 'index'])
        ->name('admin.references.index');
    Route::post('/admin/references/drens', [ReferenceManagementController::class, 'storeDren'])
        ->name('admin.references.drens.store');
    Route::put('/admin/references/drens/{dren}', [ReferenceManagementController::class, 'updateDren'])
        ->name('admin.references.drens.update');
    Route::post('/admin/references/ciscos', [ReferenceManagementController::class, 'storeCisco'])
        ->name('admin.references.ciscos.store');
    Route::put('/admin/references/ciscos/{cisco}', [ReferenceManagementController::class, 'updateCisco'])
        ->name('admin.references.ciscos.update');
    Route::post('/admin/references/centres-correction', [ReferenceManagementController::class, 'storeCentreCorrection'])
        ->name('admin.references.centres-correction.store');
    Route::put('/admin/references/centres-correction/{centreCorrection}', [ReferenceManagementController::class, 'updateCentreCorrection'])
        ->name('admin.references.centres-correction.update');
    Route::post('/admin/references/centres-ecrit', [ReferenceManagementController::class, 'storeCentreEcrit'])
        ->name('admin.references.centres-ecrit.store');
    Route::put('/admin/references/centres-ecrit/{centreEcrit}', [ReferenceManagementController::class, 'updateCentreEcrit'])
        ->name('admin.references.centres-ecrit.update');
});
