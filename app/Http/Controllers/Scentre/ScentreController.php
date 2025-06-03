<?php

namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class ScentreController extends Controller
{
    public function index()
    {
        return Inertia::render('Scentre/Dashboard');
    }
}
