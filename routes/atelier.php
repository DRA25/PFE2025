<?php


use App\Http\Controllers\Atelier\AtelierController;
use App\Http\Controllers\Atelier\DPieceController;
use App\Http\Controllers\Atelier\PieceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;




//atelier routes
Route::middleware(['auth', 'verified', 'role:service atelier|admin'])->group(function () {
// Main atelier dashboard
Route::get('/atelier', [AtelierController::class, 'index'])->name('atelier.index');

// Pieces management routes (now under /atelier/pieces)
Route::prefix('atelier/pieces')->group(function () {
Route::get('/', [PieceController::class, 'index'])->name('atelier.pieces.index');
Route::get('/create', [PieceController::class, 'create'])->name('atelier.pieces.create');
Route::post('/', [PieceController::class, 'store'])->name('atelier.pieces.store');
Route::get('/{piece}/edit', [PieceController::class, 'edit'])->name('atelier.pieces.edit');
Route::put('/{piece}', [PieceController::class, 'update'])->name('atelier.pieces.update');
Route::delete('/{piece}', [PieceController::class, 'destroy'])->name('atelier.pieces.destroy');
});

// Demandes pieces routes for atelier - scoped by centre
Route::get('/atelier/demandes-pieces', [DPieceController::class, 'index'])->name('atelier.demandes-pieces.index');
Route::get('/atelier/demandes-pieces/create', [DPieceController::class, 'create'])->name('atelier.demandes-pieces.create');
Route::post('/atelier/demandes-pieces', [DPieceController::class, 'store'])->name('atelier.demandes-pieces.store');
Route::get('/atelier/demandes-pieces/{demande_piece}/edit', [DPieceController::class, 'edit'])->name('atelier.demandes-pieces.edit');
Route::put('/atelier/demandes-pieces/{demande_piece}', [DPieceController::class, 'update'])->name('atelier.demandes-pieces.update');
Route::delete('/atelier/demandes-pieces/{demande_piece}', [DPieceController::class, 'destroy'])->name('atelier.demandes-pieces.destroy');
});
