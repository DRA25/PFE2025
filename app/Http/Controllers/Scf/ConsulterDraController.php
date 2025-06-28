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
            ->whereIn('etat', ['cloture', 'refuse', 'accepte'])
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
                ];
            });

        return Inertia::render('Scf/ConsulterDra/Index', compact('dras'));
    }

    public function show($n_dra)
    {
        $dra = Dra::with('centre')->where('n_dra', $n_dra)->firstOrFail();

        // Load factures with related data and pivot prices.
        // Factures still have pieces, prestations, and charges.
        $factures = $dra->factures()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces:id_piece,nom_piece,tva',
                'prestations:id_prest,nom_prest,tva', // prix_prest is on pivot
                'charges:id_charge,nom_charge,tva'    // prix_charge is on pivot
            ])
            ->get()
            ->map(function ($facture) {
                $facture->montant = $this->calculateMontant($facture);
                return $facture;
            });

        // Load bonAchats with related data and pivot prices.
        // BonAchats now only have pieces.
        $bonAchats = $dra->bonAchats()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces:id_piece,nom_piece,tva'
            ])
            ->get()
            ->map(function ($bonAchat) {
                $bonAchat->montant = $this->calculateMontant($bonAchat);
                return $bonAchat;
            });

        return Inertia::render('Scf/ConsulterDra/Show', [
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

    /**
     * Calculates the total amount for a given model (Facture or BonAchat).
     * This now correctly handles BonAchat having only pieces.
     *
     * @param \App\Models\Facture|\App\Models\BonAchat $model
     * @return float
     */
    protected function calculateMontant($model)
    {
        $total = 0;

        // Calculate pieces total (HT + TVA) using pivot prices
        if ($model->relationLoaded('pieces')) {
            $total += $model->pieces->sum(function ($piece) {
                // Determine the correct quantity field from pivot
                $quantity = $piece->pivot->qte_f ?? $piece->pivot->qte_ba ?? 1;
                $ht = $piece->pivot->prix_piece ?? 0;
                $tva = $piece->tva ?? 0;
                return $ht * (1 + $tva / 100) * $quantity;
            });
        }

        // Add prestations (HT + TVA) using pivot prices - Only for Facture
        // The condition $model instanceof \App\Models\Facture ensures this only runs for Factures.
        if ($model instanceof \App\Models\Facture && $model->relationLoaded('prestations')) {
            $total += $model->prestations->sum(function ($prestation) {
                $ht = $prestation->pivot->prix_prest ?? 0;
                $tva = $prestation->tva ?? 0;
                return $ht * (1 + $tva / 100) * ($prestation->pivot->qte_fpr ?? 1);
            });
        }

        // Add charges (HT + TVA) using pivot prices - Only for Facture
        // The condition $model instanceof \App\Models\Facture ensures this only runs for Factures.
        if ($model instanceof \App\Models\Facture && $model->relationLoaded('charges')) {
            $total += $model->charges->sum(function ($charge) {
                $ht = $charge->pivot->prix_charge ?? 0;
                $tva = $charge->tva ?? 0;
                return $ht * (1 + $tva / 100) * ($charge->pivot->qte_fc ?? 1);
            });
        }

        // If it's a Facture, add droit_timbre
        if ($model instanceof \App\Models\Facture) {
            $total += $model->droit_timbre ?? 0;
        }

        return $total;
    }
}
