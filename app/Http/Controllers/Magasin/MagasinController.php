<?php

namespace App\Http\Controllers\Magasin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class MagasinController extends Controller
{
    public function index()
    {
        return Inertia::render('Magasin/Dashboard');
    }
}
