<?php


use App\Http\Controllers\FournisseurController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



//fournisseur routes
Route::middleware(['auth', 'role:service achat|service centre'])->group(function () {
Route::get('/fournisseurs', [FournisseurController::class, 'index'])->name('fournisseurs.index');
Route::get('/fournisseurs/create', [FournisseurController::class, 'create'])->name('fournisseurs.create');
Route::post('/fournisseurs', [FournisseurController::class, 'store'])->name('fournisseurs.store');
Route::get('/fournisseurs/{fournisseur}/edit', [FournisseurController::class, 'edit'])->name('fournisseurs.edit');
Route::put('/fournisseurs/{fournisseur}', [FournisseurController::class, 'update'])->name('fournisseurs.update');
Route::delete('/fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy'])->name('fournisseurs.destroy');
});
