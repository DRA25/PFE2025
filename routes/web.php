<?php

use App\Http\Controllers\AchatController;
use App\Http\Controllers\Atelier\AtelierController;
use App\Http\Controllers\Atelier\DemandepieceController;
use App\Http\Controllers\MagasinController;
use App\Http\Controllers\DRAController;
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
    Route::get('/demandepiece', [DemandepieceController::class, 'index'])->name('demandepiece.index');


});

//Magasin routes
Route::middleware(['auth', 'role:service magasin|admin'])->group(function () {
    Route::get('/magasin', [MagasinController::class, 'index'])->name('magasin.index');

});

//Achat routes
Route::middleware(['auth', 'role:service cf|service achat|admin'])->group(function () {
    Route::get('/achat/dra', [DRAController::class, 'index'])->name('dra.index');
    Route::get('/achat/dra/create', [DRAController::class, 'create'])->name('dra.create');
    Route::post('/achat/dra', [DRAController::class, 'store'])->name('dra.store');
    Route::get('/achat/dra/{dra}/edit', [DRAController::class, 'edit'])->name('dra.edit');
    Route::put('/achat/dra/{dra}', [DRAController::class, 'update'])->name('dra.update');
    Route::delete('/achat/dra/{dra}', [DRAController::class, 'destroy'])->name('dra.destroy');

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





require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
