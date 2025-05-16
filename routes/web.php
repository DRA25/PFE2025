<?php


use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\Achat\AchatController;
use App\Http\Controllers\Achat\AchatDemandePieceController;
use App\Http\Controllers\Achat\BonAchatController;
use App\Http\Controllers\Achat\DraController;
use App\Http\Controllers\Achat\FactureController;
use App\Http\Controllers\Atelier\AtelierController;
use App\Http\Controllers\Atelier\DPieceController;
use App\Http\Controllers\Atelier\PieceController;
use App\Http\Controllers\CentreController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\MagasinController;
use App\Http\Controllers\paimentController;
use App\Http\Controllers\RoleUserController;
use App\Http\Controllers\ScfController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

Route::middleware(['auth', 'role:admin'])->group(function () {


        // User CRUD Routes
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Role Management Routes
    Route::get('/roles', [RoleUserController::class, 'index'])->name('roles.index');
    Route::post('/roles/{user}/assign', [RoleUserController::class, 'assignRoles'])->name('roles.assign');
    Route::post('/roles/{user}/remove/{role}', [RoleUserController::class, 'removeRole'])->name('roles.remove');
});



//Magasin routes
Route::middleware(['auth', 'role:service magasin|admin'])->group(function () {
    Route::get('/magasin', [MagasinController::class, 'index'])->name('magasin.index');

});






Route::middleware(['auth', 'role:service cf|service achat|admin'])->group(function () {
    // Group all achat-related routes under /achat prefix
    Route::prefix('achat')->name('achat.')->group(function () {
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
            'create' => 'dras.create',
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




Route::get('/about', [AboutPageController::class, 'show'])->name('about');

// Editable routes - now available to any authenticated user
Route::middleware(['auth'])->group(function () {
    Route::get('/about/edit', [AboutPageController::class, 'edit'])->name('about.edit');
    Route::put('/about', [AboutPageController::class, 'update'])->name('about.update');
    Route::get('/about/create', [AboutPageController::class, 'create'])->name('about.create');
    Route::post('/about', [AboutPageController::class, 'store'])->name('about.store');
    Route::delete('/about', [AboutPageController::class, 'destroy'])->name('about.destroy');
});




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

    Route::prefix('atelier')->group(function () {
        Route::resource('demandes-pieces', DPieceController::class)
            ->parameters(['demandes-pieces' => 'demande_piece'])
            ->names([
                'index' => 'atelier.demandes-pieces.index',
                'create' => 'atelier.demandes-pieces.create',
                'store' => 'atelier.demandes-pieces.store',
                'edit' => 'atelier.demandes-pieces.edit',
                'update' => 'atelier.demandes-pieces.update',
                'destroy' => 'atelier.demandes-pieces.destroy',
            ]);
    });
});

//centre routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/centres', [CentreController::class, 'index'])->name('centres.index');
    Route::get('/centres/create', [CentreController::class, 'create'])->name('centres.create');
    Route::post('/centres', [CentreController::class, 'store'])->name('centres.store');
    Route::get('/centres/{centre}/edit', [CentreController::class, 'edit'])->name('centres.edit');
    Route::put('/centres/{centre}', [CentreController::class, 'update'])->name('centres.update');
    Route::delete('/centres/{centre}', [CentreController::class, 'destroy'])->name('centres.destroy');
});



//fournisseur routes
Route::middleware(['auth', 'role:service achat|admin'])->group(function () {
    Route::get('/fournisseurs', [FournisseurController::class, 'index'])->name('fournisseurs.index');
    Route::get('/fournisseurs/create', [FournisseurController::class, 'create'])->name('fournisseurs.create');
    Route::post('/fournisseurs', [FournisseurController::class, 'store'])->name('fournisseurs.store');
    Route::get('/fournisseurs/{fournisseur}/edit', [FournisseurController::class, 'edit'])->name('fournisseurs.edit');
    Route::put('/fournisseurs/{fournisseur}', [FournisseurController::class, 'update'])->name('fournisseurs.update');
    Route::delete('/fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy'])->name('fournisseurs.destroy');
});






require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
