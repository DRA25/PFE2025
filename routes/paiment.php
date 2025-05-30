<?php


use App\Http\Controllers\Paiment\ListDraAccepteController;
use App\Http\Controllers\Paiment\PaimentController;
use App\Http\Controllers\Paiment\RemboursementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;




//Paiment routes
Route::middleware(['auth', 'role:service paiment|admin'])->group(function () {
Route::get('/paiment', [PaimentController::class, 'index'])->name('paiment.index');

Route::prefix('/paiment/dras')->name('dras.')->group(function () {
Route::get('/', [ListDraAccepteController::class, 'index'])->name('index');
});

Route::prefix('/paiment/remboursements')->name('paiment.remboursements.')->group(function () {
Route::get('/', [RemboursementController::class, 'index'])->name('index');
Route::get('/create', [RemboursementController::class, 'create'])->name('create');
Route::post('/', [RemboursementController::class, 'store'])->name('store');
Route::get('/{remboursement}/edit', [RemboursementController::class, 'edit'])->name('edit');
Route::put('/{remboursement}', [RemboursementController::class, 'update'])->name('update');
Route::delete('/{remboursement}', [RemboursementController::class, 'destroy'])->name('destroy');
});
});
