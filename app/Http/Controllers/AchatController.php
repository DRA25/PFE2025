<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class AchatController extends Controller
{
    public function index()
    {
        return Inertia::render('Achat/Dashboard');
    }
}
