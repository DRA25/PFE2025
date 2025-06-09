<?php

use App\Http\Controllers\Scentre\BonAchatController;
use App\Http\Controllers\Scentre\BonCommandeController;
use App\Http\Controllers\Scentre\DraController;
use App\Http\Controllers\Scentre\EncaissementController;
use App\Http\Controllers\Scentre\FactureController;
use App\Http\Controllers\Scentre\ScentreController;
use App\Http\Controllers\Scentre\ScentreDemandePieceController;

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:service centre|admin'])->group(function () {
    // Group all scentre-related routes under /scentre prefix
    Route::prefix('scentre')->name('scentre.')->group(function () {

        Route::prefix('boncommandes')->name('boncommandes.')->group(function () {
            Route::get('/', [BonCommandeController::class, 'index'])->name('index');
            Route::get('/create', [BonCommandeController::class, 'create'])->name('create');
            Route::post('/', [BonCommandeController::class, 'store'])->name('store');
            Route::get('/{n_bc}', [BonCommandeController::class, 'show'])->name('show');
            Route::get('/{n_bc}/edit', [BonCommandeController::class, 'edit'])->name('edit');
            Route::put('/{n_bc}', [BonCommandeController::class, 'update'])->name('update');
            Route::delete('/{n_bc}', [BonCommandeController::class, 'destroy'])->name('destroy');
            Route::get('/{n_bc}/export-pdf', [BonCommandeController::class, 'exportPdf'])->name('export-pdf');
        });

        Route::get('/demandes-export-pdf', [ScentreDemandePieceController::class, 'exportListPdf'])
            ->name('demandes-pieces.export-pdf');

        // Main dashboard route - name it simply 'index' (will become 'scentre.index')
        Route::get('/', [ScentreController::class, 'index'])->name('index');

        // Demandes pieces routes - will automatically get 'scentre.' prefix
        Route::resource('demandes-pieces', ScentreDemandePieceController::class)
            ->parameters(['demandes-pieces' => 'demande_piece'])
            ->names([
                'index' => 'demandes-pieces.index',
                'show' => 'demandes-pieces.show',
                'update' => 'demandes-pieces.update'
            ]);

        Route::get('/dras/{dra}', [DraController::class, 'show'])->name('dras.show');

        // DRAs routes - views located in pages/dra/
        Route::resource('dras', DraController::class)->names([
            'index' => 'dras.index',
            'autocreate' => 'dras.auto-create',
            'store' => 'dras.store',
            'show' => 'dras.show',
            'edit' => 'dras.edit',
            'update' => 'dras.update',
            'destroy' => 'dras.destroy'
        ]);

        // Additional DRA routes
        Route::put('dras/{dra}/close', [DraController::class, 'close'])
            ->name('dras.close');

        // Factures routes - views located in pages/facture/
        Route::prefix('dras/{dra}/factures')->name('dras.factures.')->group(function () {
            Route::get('/', [FactureController::class, 'index'])->name('index');
            Route::get('/create', [FactureController::class, 'create'])->name('create');
            Route::post('/', [FactureController::class, 'store'])->name('store');
            Route::get('/{facture}/edit', [FactureController::class, 'edit'])->name('edit');
            Route::put('/{facture}', [FactureController::class, 'update'])->name('update');
            Route::delete('/{facture}', [FactureController::class, 'destroy'])->name('destroy');
        });

        // BonScentres routes - views located in pages/bon-scentre/
        Route::prefix('dras/{dra}/bon-achats')->name('dras.bon-achats.')->group(function () {
            Route::get('/', [BonAchatController::class, 'index'])->name('index');
            Route::get('/create', [BonAchatController::class, 'create'])->name('create');
            Route::post('/', [BonAchatController::class, 'store'])->name('store');
            Route::get('/{bonAchat}/edit', [BonAchatController::class, 'edit'])->name('edit');
            Route::put('/{bonAchat}', [BonAchatController::class, 'update'])->name('update');
            Route::delete('/{bonAchat}', [BonAchatController::class, 'destroy'])->name('destroy');

        });
    });
});

// Encaissements routes
Route::resource('encaissements', EncaissementController::class);

// Single demande PDF export
Route::get('/scentre/demandes-pieces/{demande_piece}/export-single-pdf', [ScentreDemandePieceController::class, 'exportPdf'])
    ->name('scentre.demandes-pieces.export-single-pdf');

// Full list PDF export
Route::get('/scentre/demandes-pieces/export-full-list-pdf', [ScentreDemandePieceController::class, 'exportListPdf'])
    ->name('scentre.demandes-pieces.export-full-list-pdf');
