<?php

namespace App\Http\Controllers\Magasin;

use App\Http\Controllers\Controller;
use App\Models\QuantiteStocke;
use Inertia\Inertia;

class QuantiteStockeController extends Controller
{
    public function index()
    {
        $quantites = QuantiteStocke::with(['magasin', 'piece'])->get();

        return Inertia::render('Magasin/QuantiteStocke/Index', [
            'quantites' => $quantites,
        ]);
    }
}

