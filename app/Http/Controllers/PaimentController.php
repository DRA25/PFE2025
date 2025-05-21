<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class PaimentController extends Controller
{
    public function index()
    {
        return Inertia::render('Paiment/Dashboard');
    }
}
