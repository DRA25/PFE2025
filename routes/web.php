<?php



use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');


Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



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
