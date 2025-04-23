<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AtelierController extends Controller
{
    public function index()
    {
        return Inertia::render('Atelier/Index');
    }
}
