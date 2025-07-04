<?php

namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ScentreController extends Controller
{
    public function index()
    {

        $user = Auth::user();
        $centre = null;

        if ($user && $user->id_centre) {
            $centre = Centre::find($user->id_centre);
        }



        return Inertia::render('Scentre/Dashboard', [
            'centre' => $centre ? [
                'id_centre' => $centre->id_centre,

            ] : null,
        ]);
    }
}
