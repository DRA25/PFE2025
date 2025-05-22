<?php

namespace App\Http\Controllers;

use App\Models\Dra;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ListDraAccepteController extends Controller
{
    public function index()
    {
        $dras = Dra::with('centre')
            ->whereIn('etat', ['accepte']) // Filter by multiple states
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($dra) {
                return [
                    'n_dra' => $dra->n_dra,
                    'id_centre' => $dra->id_centre,
                    'date_creation' => $dra->date_creation->format('Y-m-d'),
                    'etat' => $dra->etat,
                    'total_dra' => $dra->total_dra,
                    'created_at' => $dra->created_at->toISOString(),
                    // Removed available and seuil_centre as they're no longer needed in the frontend
                ];
            });

        return Inertia::render('Paiment/ListDraAccepte/Index', compact('dras'));
    }
}
