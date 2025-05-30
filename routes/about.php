<?php


use App\Http\Controllers\AboutPageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;



Route::get('/about', [AboutPageController::class, 'show'])->name('about');

// Editable routes - now available to any authenticated user
Route::middleware(['auth','verified','role:admin'])->group(function () {
Route::get('/about/edit', [AboutPageController::class, 'edit'])->name('about.edit');
Route::put('/about', [AboutPageController::class, 'update'])->name('about.update');
Route::get('/about/create', [AboutPageController::class, 'create'])->name('about.create');
Route::post('/about', [AboutPageController::class, 'store'])->name('about.store');
Route::delete('/about', [AboutPageController::class, 'destroy'])->name('about.destroy');
});
