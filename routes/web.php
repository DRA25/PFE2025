<?php


use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DirectionDashboardController;
use App\Http\Controllers\NotificationController;
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
