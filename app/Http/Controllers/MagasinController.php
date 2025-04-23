<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class MagasinController extends Controller
{
    public function index()
    {
        return Inertia::render('Magasin/Index');
    }
}
