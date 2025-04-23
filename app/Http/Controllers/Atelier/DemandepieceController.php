<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DemandepieceController extends Controller
{
    public function index()
    {


        return Inertia::render('Atelier/demandepiece', [
            'userHasRole' => auth()->user()?->getRoleNames()->first(), // e.g. 'atelier'
        ]);
    }
}
