<?php


use App\Http\Controllers\Atelier\PieceController;
use App\Http\Controllers\Magasin\MagasinController;
use App\Http\Controllers\Magasin\MagasinDemandePieceController;
use App\Http\Controllers\Magasin\QuantiteStockeController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;





// Magasin routes
Route::middleware(['auth', 'verified', 'role:service magasin'])->group(function () {



    Route::prefix('magasin/quantites')->name('magasin.quantites.')->middleware(['auth', 'verified', 'role:service magasin|admin'])->group(function () {
        Route::get('/', [QuantiteStockeController::class, 'index'])->name('index');
        Route::get('/create', [QuantiteStockeController::class, 'create'])->name('create');
        Route::post('/', [QuantiteStockeController::class, 'store'])->name('store');

        Route::get('/{id_magasin}/{id_piece}/edit', [QuantiteStockeController::class, 'edit'])->name('edit');
        Route::put('/{id_magasin}/{id_piece}', [QuantiteStockeController::class, 'update'])->name('update');

        Route::delete('/{id_magasin}/{id_piece}', [QuantiteStockeController::class, 'destroy'])->name('destroy');
    });


// Main magasin dashboard
Route::get('/magasin', [MagasinController::class, 'index'])->name('magasin.index');


// Pieces management routes (under /magasin/pieces)
Route::prefix('magasin/pieces')->group(function () {
Route::get('/', [PieceController::class, 'index'])->name('magasin.pieces.index');
Route::get('/create', [PieceController::class, 'create'])->name('magasin.pieces.create');
Route::post('/', [PieceController::class, 'store'])->name('magasin.pieces.store');
Route::get('/{piece}/edit', [PieceController::class, 'edit'])->name('magasin.pieces.edit');
Route::put('/{piece}', [PieceController::class, 'update'])->name('magasin.pieces.update');
Route::delete('/{piece}', [PieceController::class, 'destroy'])->name('magasin.pieces.destroy');
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



    Route::post('/{demande_piece}/livrer', [MagasinDemandePieceController::class, 'livrerPiece'])->name('magasin.mes-demandes.livrer');

});


});

