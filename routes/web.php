<?php


use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\Achat\AchatController;
use App\Http\Controllers\Achat\AchatDemandePieceController;
use App\Http\Controllers\Achat\BonAchatController;
use App\Http\Controllers\Achat\DraController;
use App\Http\Controllers\Achat\FactureController;
use App\Http\Controllers\Atelier\AtelierController;
use App\Http\Controllers\Atelier\DPieceController;
use App\Http\Controllers\Atelier\GestionAtelierController;
use App\Http\Controllers\Atelier\PieceController;
use App\Http\Controllers\CentreController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\ListDraAccepteController;
use App\Http\Controllers\Magasin\DMPieceController;
use App\Http\Controllers\Magasin\GestionMagasinController;
use App\Http\Controllers\Magasin\MagasinController;
use App\Http\Controllers\Magasin\MagasinDemandePieceController;
use App\Http\Controllers\Magasin\QuantiteStockeController;
use App\Http\Controllers\PaimentController;
use App\Http\Controllers\RemboursementController;
use App\Http\Controllers\RoleUserController;
use App\Http\Controllers\Scf\ConsulterDraController;
use App\Http\Controllers\Scf\ScfController;
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


//SCF routes
Route::middleware(['auth', 'role:service cf|admin'])
    ->prefix('scf')
    ->name('scf.')
    ->group(function () {
        // Dashboard
        Route::get('/', [ScfController::class, 'index'])->name('index');
        Route::get('/dras/{dra}/factures', [FactureController::class, 'show'])->name('dras.factures.show');
        Route::get('/dras/{dra}/bon-achats', [BonAchatController::class, 'show'])->name('dras.bon-achats.show');

        // DRA Routes
        Route::prefix('dras')->name('dras.')->group(function () {
            Route::get('/', [ConsulterDraController::class, 'index'])->name('index');
            Route::get('/{dra}', [ConsulterDraController::class, 'show'])->name('show');
            Route::put('/{dra}', [DraController::class, 'update'])->name('update');
        });
    });

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



Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/about', [AboutPageController::class, 'show'])->name('about');

// Editable routes - now available to any authenticated user
Route::middleware(['auth','verified','role:admin'])->group(function () {
    Route::get('/about/edit', [AboutPageController::class, 'edit'])->name('about.edit');
    Route::put('/about', [AboutPageController::class, 'update'])->name('about.update');
    Route::get('/about/create', [AboutPageController::class, 'create'])->name('about.create');
    Route::post('/about', [AboutPageController::class, 'store'])->name('about.store');
    Route::delete('/about', [AboutPageController::class, 'destroy'])->name('about.destroy');
});


// Magasin routes
Route::middleware(['auth', 'verified', 'role:service magasin|admin'])->group(function () {
    // Main magasin dashboard
    Route::get('/magasin', [MagasinController::class, 'index'])->name('magasin.index');
    Route::get('/magasin/quantites', [QuantiteStockeController::class, 'index'])->name('magasin.quantites.index');
    // Pieces management routes (under /magasin/pieces)
    Route::prefix('magasin/pieces')->group(function () {
        Route::get('/', [PieceController::class, 'index'])->name('magasin.pieces.index');
        Route::get('/create', [PieceController::class, 'create'])->name('magasin.pieces.create');
        Route::post('/', [PieceController::class, 'store'])->name('magasin.pieces.store');
        Route::get('/{piece}/edit', [PieceController::class, 'edit'])->name('magasin.pieces.edit');
        Route::put('/{piece}', [PieceController::class, 'update'])->name('magasin.pieces.update');
        Route::delete('/{piece}', [PieceController::class, 'destroy'])->name('magasin.pieces.destroy');
    });

    // Demandes pieces routes for magasin - scoped by centre
    Route::prefix('magasin/demandes-pieces')->group(function () {
        Route::get('/', [DMPieceController::class, 'index'])->name('magasin.demandes-pieces.index');
        Route::get('/create', [DMPieceController::class, 'create'])->name('magasin.demandes-pieces.create');
        Route::post('/', [DMPieceController::class, 'store'])->name('magasin.demandes-pieces.store');
        Route::get('/{demande_piece}/edit', [DMPieceController::class, 'edit'])->name('magasin.demandes-pieces.edit');
        Route::put('/{demande_piece}', [DMPieceController::class, 'update'])->name('magasin.demandes-pieces.update');
        Route::delete('/{demande_piece}', [DMPieceController::class, 'destroy'])->name('magasin.demandes-pieces.destroy');
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
    });


});


//atelier routes
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

    // Demandes pieces routes for atelier - scoped by centre
    Route::get('/atelier/demandes-pieces', [DPieceController::class, 'index'])->name('atelier.demandes-pieces.index');
    Route::get('/atelier/demandes-pieces/create', [DPieceController::class, 'create'])->name('atelier.demandes-pieces.create');
    Route::post('/atelier/demandes-pieces', [DPieceController::class, 'store'])->name('atelier.demandes-pieces.store');
    Route::get('/atelier/demandes-pieces/{demande_piece}/edit', [DPieceController::class, 'edit'])->name('atelier.demandes-pieces.edit');
    Route::put('/atelier/demandes-pieces/{demande_piece}', [DPieceController::class, 'update'])->name('atelier.demandes-pieces.update');
    Route::delete('/atelier/demandes-pieces/{demande_piece}', [DPieceController::class, 'destroy'])->name('atelier.demandes-pieces.destroy');
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

//GestionAtelier and GestionMagasin Routes
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::prefix('gestionatelier')->group(function () {
        Route::get('/', [GestionAtelierController::class, 'index'])->name('gestionatelier.index');
        Route::get('/create', [GestionAtelierController::class, 'create'])->name('gestionatelier.create');
        Route::post('/', [GestionAtelierController::class, 'store'])->name('gestionatelier.store');
        Route::get('/{gestionatelier}/edit', [GestionAtelierController::class, 'edit'])->name('gestionatelier.edit');
        Route::put('/{gestionatelier}', [GestionAtelierController::class, 'update'])->name('gestionatelier.update');
        Route::delete('/{gestionatelier}', [GestionAtelierController::class, 'destroy'])->name('gestionatelier.destroy');
    });

    Route::prefix('gestionmagasin')->controller(GestionMagasinController::class)->name('gestionmagasin.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/create', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{gestionmagasin}/edit', 'edit')->name('edit');
        Route::put('/{gestionmagasin}', 'update')->name('update');
        Route::delete('/{gestionmagasin}', 'destroy')->name('destroy');
    });
});


// Single demande PDF export
Route::get('/achat/demandes-pieces/{demande_piece}/export-single-pdf', [AchatDemandePieceController::class, 'exportPdf'])
    ->name('achat.demandes-pieces.export-single-pdf');

// Full list PDF export
Route::get('/achat/demandes-pieces/export-full-list-pdf',
    [AchatDemandePieceController::class, 'exportListPdf'])
    ->name('achat.demandes-pieces.export-full-list-pdf');










require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
