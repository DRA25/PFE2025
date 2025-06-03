<?php


use App\Http\Controllers\Achat\AchatController;
use App\Http\Controllers\Scentre\DraController;
use App\Http\Controllers\Achat\ConsulterDraController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth', 'role:service achat|admin'])->group(function () {
// Group all achat-related routes under /achat prefix
Route::prefix('achat')->name('achat.')->group(function () {


    Route::get('/', [AchatController::class, 'index'])->name('index');

    Route::prefix('dras')->name('dras.')->group(function () {
        Route::get('/', [ConsulterDraController::class, 'index'])->name('index');
        Route::get('/{dra}', [ConsulterDraController::class, 'show'])->name('show');
        Route::put('/{dra}', [DraController::class, 'update'])->name('update');
    });


});
});
