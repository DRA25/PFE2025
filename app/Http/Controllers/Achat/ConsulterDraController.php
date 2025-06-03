<?php

namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Dra;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConsulterDraController extends Controller
{
    public function index()
    {
        $dras = Dra::with('centre')
            ->whereIn('etat', ['cloture', 'refuse', 'accepte','rembourse']) // Filter by multiple states
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

        return Inertia::render('Achat/ConsulterDra/Index', compact('dras'));
    }

    public function show($n_dra)
    {


        $dra = Dra::with('centre')->where('n_dra', $n_dra)->firstOrFail();

//
        // Load factures with related data
        $factures = $dra->factures()
            ->with(['fournisseur:id_fourn,nom_fourn', 'pieces:id_piece,nom_piece,prix_piece,tva'])
            ->get()
            ->map(function ($facture) {
                $facture->montant = $this->calculateMontant($facture);
                return $facture;
            });

        // Load bonAchats with related data
        $bonAchats = $dra->bonAchats()
            ->with(['fournisseur:id_fourn,nom_fourn', 'pieces:id_piece,nom_piece,prix_piece,tva'])
            ->get()
            ->map(function ($bonAchat) {
                $bonAchat->montant = $this->calculateMontant($bonAchat);
                return $bonAchat;
            });

        return Inertia::render('Achat/ConsulterDra/Show', [
            'dra' => [
                'n_dra' => $dra->n_dra,
                'id_centre' => $dra->id_centre,
                'date_creation' => $dra->date_creation->format('Y-m-d'),
                'etat' => $dra->etat,
                'total_dra' => $dra->total_dra,
                'created_at' => $dra->created_at?->toISOString(),
                'centre' => [
                    'seuil_centre' => $dra->centre->seuil_centre,
                    'montant_disponible' => $dra->centre->montant_disponible,
                ]
            ],
            'factures' => $factures,
            'bonAchats' => $bonAchats,
        ]);
    }

    protected function calculateMontant($model)
    {
        // Sum of pieces (HT + TVA)
        $total = $model->pieces->sum(function ($piece) {
            $ht = $piece->prix_piece;
            $tva = $piece->tva ?? 0;
            return $ht * (1 + $tva / 100);
        });

        // If it's a Facture, add droit_timbre
        if ($model instanceof \App\Models\Facture) {
            $total += $model->droit_timbre ?? 0;
        }

        return $total;
    }

}
