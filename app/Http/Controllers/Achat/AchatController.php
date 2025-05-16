<?php

namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class AchatController extends Controller
{
    public function index()
    {
        return Inertia::render('Achat/Dashboard');
    }
}
