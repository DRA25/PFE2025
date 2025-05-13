<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AtelierController extends Controller
{
public function index()
{
return Inertia::render('Atelier/Dashboard');
}
}

