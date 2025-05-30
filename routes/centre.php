<?php


use App\Http\Controllers\CentreController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;




//centre routes
Route::middleware(['auth', 'role:admin'])->group(function () {
Route::get('/centres', [CentreController::class, 'index'])->name('centres.index');
Route::get('/centres/create', [CentreController::class, 'create'])->name('centres.create');
Route::post('/centres', [CentreController::class, 'store'])->name('centres.store');
Route::get('/centres/{centre}/edit', [CentreController::class, 'edit'])->name('centres.edit');
Route::put('/centres/{centre}', [CentreController::class, 'update'])->name('centres.update');
Route::delete('/centres/{centre}', [CentreController::class, 'destroy'])->name('centres.destroy');
});
