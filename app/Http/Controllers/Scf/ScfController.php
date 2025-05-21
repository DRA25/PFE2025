<?php

namespace App\Http\Controllers\Scf;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ScfController extends Controller
{
    public function index()
    {
        return Inertia::render('Scf/Dashboard');
    }
}
