<?php

namespace App\Http\Controllers\Paiment;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class PaimentController extends Controller
{
    public function index()
    {
        return Inertia::render('Paiment/Dashboard');
    }
}
