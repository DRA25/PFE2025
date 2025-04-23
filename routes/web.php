<?php

use App\Http\Controllers\AchatController;
use App\Http\Controllers\AtelierController;
use App\Http\Controllers\MagasinController;
use App\Http\Controllers\paimentController;
use App\Http\Controllers\ScfController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/roles', [UserController::class, 'assignRoles'])->name('users.assignRoles');
    Route::post('/users/{user}/remove-role/{role}', [UserController::class, 'removeRole'])->name('users.removeRole');
});

//Atelier routes
Route::middleware(['auth', 'role:service atelier|admin'])->group(function () {
    Route::get('/atelier', [AtelierController::class, 'index'])->name('atelier.index');

});

//Magasin routes
Route::middleware(['auth', 'role:service magasin|admin'])->group(function () {
    Route::get('/magasin', [MagasinController::class, 'index'])->name('magasin.index');

});

//Achat routes
Route::middleware(['auth', 'role:service achat|admin'])->group(function () {
    Route::get('/achat', [AchatController::class, 'index'])->name('achat.index');

});

//SCF routes
Route::middleware(['auth', 'role:service cf|admin'])->group(function () {
    Route::get('/scf', [ScfController::class, 'index'])->name('scf.index');

});

//Paiment routes
Route::middleware(['auth', 'role:service paiment|admin'])->group(function () {
    Route::get('/paiment', [PaimentController::class, 'index'])->name('paiment.index');

});





Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('about', function () {
    return Inertia::render('About');
})->middleware(['auth', 'verified'])->name('about');

Route::get('dra', function () {
    return Inertia::render('DRA');
})->middleware(['auth', 'verified'])->name('dra');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
