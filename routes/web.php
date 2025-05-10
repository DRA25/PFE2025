<?php

use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\AchatController;
use App\Http\Controllers\Atelier\AtelierController;
use App\Http\Controllers\Atelier\DemandepieceController;
use App\Http\Controllers\BonAchatController;
use App\Http\Controllers\DraController;
use App\Http\Controllers\FactureController;
use App\Http\Controllers\MagasinController;
use App\Http\Controllers\paimentController;
use App\Http\Controllers\ScfController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/roles', [UserController::class, 'assignRoles'])->name('users.assignRoles');
    Route::post('/users/{user}/remove-role/{role}', [UserController::class, 'removeRole'])->name('users.removeRole');
});

//Atelier routes

//Route::middleware(['auth', 'role:service atelier|admin'])->group(function () {
  //  Route::get('/atelier', [AtelierController::class, 'index'])->name('atelier.index');
    //Route::get('/demandepiece', [DemandepieceController::class, 'index'])->name('demandepiece.index');


//});



//Magasin routes
Route::middleware(['auth', 'role:service magasin|admin'])->group(function () {
    Route::get('/magasin', [MagasinController::class, 'index'])->name('magasin.index');

});

//Achat routes
//Route::middleware(['auth', 'role:service cf|service achat|admin'])->group(function () {
//    Route::get('/achat', [AchatController::class, 'index'])->name('achat.index');
//    Route::resource('dras', DraController::class);
//
//    Route::prefix('dras/{dra}/factures')->name('dras.factures.')->group(function () {
//        Route::get('/', [FactureController::class, 'index'])->name('index');
//        Route::get('/create', [FactureController::class, 'create'])->name('create');
//        Route::post('/', [FactureController::class, 'store'])->name('store');
//        Route::get('/{facture}/edit', [FactureController::class, 'edit'])->name('edit');
//        Route::put('/{facture}', [FactureController::class, 'update'])->name('update');
//        Route::delete('/{facture}', [FactureController::class, 'destroy'])->name('destroy');
//    });
//
//
//});

Route::middleware(['auth', 'role:service cf|service achat|admin'])->group(function () {
    // DRAs routes
    Route::resource('dras', DraController::class);

    // Close route
    Route::put('dras/{dra}/close', [DraController::class, 'close'])
        ->name('dras.close');
    Route::delete('dras/{dra}', [DraController::class, 'destroy'])
        ->name('dras.destroy');

    // Factures routes grouped under DRA
    Route::prefix('dras/{dra}/factures')->name('dras.factures.')->group(function () {
        Route::get('/', [FactureController::class, 'index'])->name('index');
        Route::get('/create', [FactureController::class, 'create'])->name('create');
        Route::post('/', [FactureController::class, 'store'])->name('store');
        Route::get('/{facture}/edit', [FactureController::class, 'edit'])->name('edit');
        Route::put('/{facture}', [FactureController::class, 'update'])->name('update'); // Fixed this line
        Route::delete('/{facture}', [FactureController::class, 'destroy'])->name('destroy');
    });

    // BonAchats routes grouped under DRA
    Route::prefix('dras/{dra}/bon-achats')->name('dras.bon-achats.')->group(function () {
        Route::get('/', [BonAchatController::class, 'index'])->name('index');
        Route::get('/create', [BonAchatController::class, 'create'])->name('create');
        Route::post('/', [BonAchatController::class, 'store'])->name('store');
        Route::get('/{bonAchat}/edit', [BonAchatController::class, 'edit'])->name('edit');
        Route::put('/{bonAchat}', [BonAchatController::class, 'update'])->name('update');
        Route::delete('/{bonAchat}', [BonAchatController::class, 'destroy'])->name('destroy');
    });
});


//SCF routes
Route::middleware(['auth', 'role:service cf|admin'])->group(function () {
    Route::get('/scf', [ScfController::class, 'index'])->name('scf.index');

});

//Paiment routes
Route::middleware(['auth', 'role:service paiment|admin'])->group(function () {
    Route::get('/paiment', [PaimentController::class, 'index'])->name('paiment.index');

});





Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


//Route::get('about', function () {
//    return Inertia::render('About');
//})->middleware(['auth', 'verified'])->name('about');


Route::get('/about', [AboutPageController::class, 'show'])->name('about');

// Editable routes - now available to any authenticated user
Route::middleware(['auth'])->group(function () {
    Route::get('/about/edit', [AboutPageController::class, 'edit'])->name('about.edit');
    Route::put('/about', [AboutPageController::class, 'update'])->name('about.update');
    Route::get('/about/create', [AboutPageController::class, 'create'])->name('about.create');
    Route::post('/about', [AboutPageController::class, 'store'])->name('about.store');
    Route::delete('/about', [AboutPageController::class, 'destroy'])->name('about.destroy');
});



/*Route::middleware(['auth', 'verified', 'role:service atelier|admin'])->group(function () {
    Route::resource('atelier', DemandepieceController::class)->names([
        'index' => 'atelier.index',
        'create' => 'atelier.create',
        'store' => 'atelier.store',
        'edit' => 'atelier.edit',
        'update' => 'atelier.update',
        'destroy' => 'atelier.destroy',
    ]);
}); */
Route::middleware(['auth', 'verified', 'role:service atelier|admin'])->group(function () {
    Route::resource('atelier', DemandepieceController::class)
        ->names([
            'index' => 'atelier.index',
            'create' => 'atelier.create',
            'store' => 'atelier.store',
            'show' => 'atelier.show',
            'edit' => 'atelier.edit',
            'update' => 'atelier.update',
            'destroy' => 'atelier.destroy',
        ]);
});


require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
