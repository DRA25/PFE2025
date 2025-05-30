<?php


use App\Http\Controllers\Achat\AchatController;
use App\Http\Controllers\Achat\AchatDemandePieceController;
use App\Http\Controllers\Achat\BonAchatController;
use App\Http\Controllers\Achat\DraController;
use App\Http\Controllers\Achat\EncaissementController;
use App\Http\Controllers\Achat\FactureController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;




Route::middleware(['auth', 'role:service achat|admin'])->group(function () {
// Group all achat-related routes under /achat prefix
Route::prefix('achat')->name('achat.')->group(function () {

Route::get('/demandes-export-pdf', [AchatDemandePieceController::class, 'exportListPdf'])
->name('demandes-pieces.export-pdf');

// Main dashboard route - name it simply 'index' (will become 'achat.index')
Route::get('/', [AchatController::class, 'index'])->name('index');

// Demandes pieces routes - will automatically get 'achat.' prefix
Route::resource('demandes-pieces', AchatDemandePieceController::class)
->parameters(['demandes-pieces' => 'demande_piece'])
->names([
'index' => 'demandes-pieces.index', // Full name: achat.demandes-pieces.index
'show' => 'demandes-pieces.show',
'update' => 'demandes-pieces.update'
]);


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

// BonAchats routes - views located in pages/bon-achat/
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





//encaissements routes
Route::resource('encaissements', EncaissementController::class);




// Single demande PDF export
Route::get('/achat/demandes-pieces/{demande_piece}/export-single-pdf', [AchatDemandePieceController::class, 'exportPdf'])
->name('achat.demandes-pieces.export-single-pdf');

// Full list PDF export
Route::get('/achat/demandes-pieces/export-full-list-pdf',
[AchatDemandePieceController::class, 'exportListPdf'])
->name('achat.demandes-pieces.export-full-list-pdf');

