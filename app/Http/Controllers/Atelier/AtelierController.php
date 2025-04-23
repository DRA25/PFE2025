<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AtelierController extends Controller
{
    public function index()
    {


        return Inertia::render('Atelier/Index', [
            'userHasRole' => auth()->user()?->getRoleNames()->first(), // e.g. 'atelier'
        ]);
    }
}


