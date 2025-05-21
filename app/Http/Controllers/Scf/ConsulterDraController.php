<?php

namespace App\Http\Controllers\Scf;

use App\Http\Controllers\Controller;
use App\Models\Dra;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConsulterDraController extends Controller
{
    public function index()
    {
        $dras = Dra::with('centre')
            ->where('id_centre', Auth::user()->id_centre)
            ->whereIn('etat', ['cloture', 'refuse', 'accepte']) // Filter by multiple states
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

        return Inertia::render('Scf/ConsulterDra/Index', compact('dras'));
    }

    public function show(Dra $dra)
    {
        // Authorization
        if ($dra->id_centre !== Auth::user()->id_centre) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('Scf/ConsulterDra/Show', [
            'dra' => [
                'n_dra' => $dra->n_dra,
                'date_creation' => $dra->date_creation->format('Y-m-d'),
                'etat' => $dra->etat,
                'total_dra' => $dra->total_dra,
                'available' => $dra->centre->seuil_centre - $dra->total_dra,
                'created_at' => $dra->created_at->toISOString(),
            ]
        ]);
    }
}
