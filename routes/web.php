<?php


use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectionDashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Scentre\ScentreDemandePieceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

Route::get('/espace-admin', function () {
    return Inertia::render('Admin/Dashboard');
})->name('espaceadmin');



Route::get('/notifications', [NotificationController::class, 'index']);
Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);



Route::get('/export/etat-trimestriel', [ExportController::class, 'exportEtatTrimestriel'])
    ->name('export.etat-trimestriel');

Route::get('/export/etat-trimestriel-all', [ExportController::class, 'exportEtatTrimestrielAllCentres'])
    ->name('export.etat-trimestriel-all');

Route::get('/export/demande-derogation/{draNumber}', [ExportController::class, 'exportDemandeDerogation'])
    ->name('export.demande-derogation');

Route::get('/export/bordereau-operations/{draNumber}', [ExportController::class, 'exportBordereauOperations'])
    ->name('export.bordereau-operations');






require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

require __DIR__ . '/admin.php';
require __DIR__ . '/achat.php';
require __DIR__ . '/scf.php';
require __DIR__ . '/paiment.php';
require __DIR__ . '/magasin.php';
require __DIR__ . '/atelier.php';
require __DIR__ . '/centre.php';
require __DIR__ . '/fournisseur.php';
require __DIR__ . '/about.php';
require __DIR__ . '/scentre.php';
require __DIR__ . '/dashboard.php';
