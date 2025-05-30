<?php


use App\Http\Controllers\Achat\BonAchatController;
use App\Http\Controllers\Achat\DraController;
use App\Http\Controllers\Achat\FactureController;
use App\Http\Controllers\Scf\ConsulterDraController;
use App\Http\Controllers\Scf\ScfController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



//SCF routes
Route::middleware(['auth', 'role:service cf|admin'])
->prefix('scf')
->name('scf.')
->group(function () {
// Dashboard
Route::get('/', [ScfController::class, 'index'])->name('index');
Route::get('/dras/{dra}/factures', [FactureController::class, 'show'])->name('dras.factures.show');
Route::get('/dras/{dra}/bon-achats', [BonAchatController::class, 'show'])->name('dras.bon-achats.show');

// DRA Routes
Route::prefix('dras')->name('dras.')->group(function () {
Route::get('/', [ConsulterDraController::class, 'index'])->name('index');
Route::get('/{dra}', [ConsulterDraController::class, 'show'])->name('show');
Route::put('/{dra}', [DraController::class, 'update'])->name('update');
});
});
