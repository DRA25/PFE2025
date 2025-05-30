<?php


use App\Http\Controllers\Atelier\PieceController;
use App\Http\Controllers\Magasin\DMPieceController;
use App\Http\Controllers\Magasin\MagasinController;
use App\Http\Controllers\Magasin\MagasinDemandePieceController;
use App\Http\Controllers\Magasin\QuantiteStockeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;





// Magasin routes
Route::middleware(['auth', 'verified', 'role:service magasin|admin'])->group(function () {
// Main magasin dashboard
Route::get('/magasin', [MagasinController::class, 'index'])->name('magasin.index');
Route::get('/magasin/quantites', [QuantiteStockeController::class, 'index'])->name('magasin.quantites.index');
// Pieces management routes (under /magasin/pieces)
Route::prefix('magasin/pieces')->group(function () {
Route::get('/', [PieceController::class, 'index'])->name('magasin.pieces.index');
Route::get('/create', [PieceController::class, 'create'])->name('magasin.pieces.create');
Route::post('/', [PieceController::class, 'store'])->name('magasin.pieces.store');
Route::get('/{piece}/edit', [PieceController::class, 'edit'])->name('magasin.pieces.edit');
Route::put('/{piece}', [PieceController::class, 'update'])->name('magasin.pieces.update');
Route::delete('/{piece}', [PieceController::class, 'destroy'])->name('magasin.pieces.destroy');
});

// Demandes pieces routes for magasin - scoped by centre
Route::prefix('magasin/demandes-pieces')->group(function () {
Route::get('/', [DMPieceController::class, 'index'])->name('magasin.demandes-pieces.index');
Route::get('/create', [DMPieceController::class, 'create'])->name('magasin.demandes-pieces.create');
Route::post('/', [DMPieceController::class, 'store'])->name('magasin.demandes-pieces.store');
Route::get('/{demande_piece}/edit', [DMPieceController::class, 'edit'])->name('magasin.demandes-pieces.edit');
Route::put('/{demande_piece}', [DMPieceController::class, 'update'])->name('magasin.demandes-pieces.update');
Route::delete('/{demande_piece}', [DMPieceController::class, 'destroy'])->name('magasin.demandes-pieces.destroy');
});


// New MagasinDemandePieceController routes (specific to magasin's own demandes)
Route::prefix('magasin/mes-demandes')->group(function () {
// PDF exports - define specific routes first
Route::get('/export/pdf', [MagasinDemandePieceController::class, 'exportListPdf'])->name('magasin.mes-demandes.export-list');

Route::get('/{demande_piece}/pdf', [MagasinDemandePieceController::class, 'exportPdf'])->name('magasin.mes-demandes.pdf');

// General routes - define less specific routes last
Route::get('/', [MagasinDemandePieceController::class, 'index'])->name('magasin.mes-demandes.index');
Route::get('/{demande_piece}', [MagasinDemandePieceController::class, 'show'])->name('magasin.mes-demandes.show');
Route::put('/{demande_piece}', [MagasinDemandePieceController::class, 'update'])->name('magasin.mes-demandes.update');
});


});

