<?php

use App\Http\Controllers\Atelier\GestionAtelierController;
use App\Http\Controllers\Magasin\GestionMagasinController;
use App\Http\Controllers\RoleUserController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'role:admin'])->group(function () {
    // User CRUD
    Route::resource('users', UserController::class)->except(['show']);

    // Roles
    Route::get('/roles', [RoleUserController::class, 'index'])->name('roles.index');
    Route::post('/roles/{user}/assign', [RoleUserController::class, 'assignRoles'])->name('roles.assign');
    Route::post('/roles/{user}/remove/{role}', [RoleUserController::class, 'removeRole'])->name('roles.remove');
});


Route::middleware(['auth', 'role:admin'])->group(function () {


    // User CRUD Routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Role Management Routes
    Route::get('/roles', [RoleUserController::class, 'index'])->name('roles.index');
    Route::post('/roles/{user}/assign', [RoleUserController::class, 'assignRoles'])->name('roles.assign');
    Route::post('/roles/{user}/remove/{role}', [RoleUserController::class, 'removeRole'])->name('roles.remove');
});




//GestionAtelier and GestionMagasin Routes
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::prefix('gestionatelier')->group(function () {
        Route::get('/', [GestionAtelierController::class, 'index'])->name('gestionatelier.index');
        Route::get('/create', [GestionAtelierController::class, 'create'])->name('gestionatelier.create');
        Route::post('/', [GestionAtelierController::class, 'store'])->name('gestionatelier.store');
        Route::get('/{gestionatelier}/edit', [GestionAtelierController::class, 'edit'])->name('gestionatelier.edit');
        Route::put('/{gestionatelier}', [GestionAtelierController::class, 'update'])->name('gestionatelier.update');
        Route::delete('/{gestionatelier}', [GestionAtelierController::class, 'destroy'])->name('gestionatelier.destroy');
    });

    Route::prefix('gestionmagasin')->controller(GestionMagasinController::class)->name('gestionmagasin.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{gestionmagasin}/edit', 'edit')->name('edit');
        Route::put('/{gestionmagasin}', 'update')->name('update');
        Route::delete('/{gestionmagasin}', 'destroy')->name('destroy');
    });
});

