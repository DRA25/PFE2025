<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ScfController extends Controller
{
    public function index()
    {
        return Inertia::render('Scf/Index');
    }
}
