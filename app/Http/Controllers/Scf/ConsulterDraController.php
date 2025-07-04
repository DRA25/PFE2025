<?php

namespace App\Http\Controllers\Scf;

use App\Http\Controllers\Controller;
use App\Models\Dra;
use Illuminate\Http\Request; // Import Request class
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // Import Rule for validation
use Inertia\Inertia;
use Illuminate\Validation\ValidationException; // Import ValidationException
use Exception; // Import Exception
use Illuminate\Support\Facades\Log; // Import Log facade

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
        $factures = $dra->factures()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces' => function ($query) {
                    $query->withPivot('qte_f', 'prix_piece');
                },
                'prestations' => function ($query) {
                    $query->withPivot('qte_fpr', 'prix_prest');
                },
                'charges' => function ($query) {
                    $query->withPivot('qte_fc', 'prix_charge'); // Ensure prix_charge is loaded from pivot
                }
            ])
            ->get()
            ->map(function ($facture) {
                $facture->montant = $this->calculateMontant($facture);
                return $facture;
            });

        // Load bonAchats with related data and pivot prices.
        $bonAchats = $dra->bonAchats()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces' => function ($query) {
                    $query->withPivot('qte_ba', 'prix_piece');
                }
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
                'motif' => $dra->motif, // Pass the motif to the frontend
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
     * Update the specified DRA's state and motif.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $n_dra
     * @return \Illuminate\Http\RedirectResponse
     */



    protected function calculateMontant($model)
    {
        $total = 0;

        // Calculate pieces total (HT + TVA) using pivot prices
        if ($model->relationLoaded('pieces')) {
            $total += $model->pieces->sum(function ($piece) use ($model) {
                $quantity = 0;
                $price = 0;

                // Determine the correct quantity field and price from pivot based on model type
                if ($model instanceof \App\Models\BonAchat) {
                    $quantity = $piece->pivot->qte_ba ?? 0;
                    $price = $piece->pivot->prix_piece ?? 0;
                } elseif ($model instanceof \App\Models\Facture) {
                    $quantity = $piece->pivot->qte_f ?? 0;
                    $price = $piece->pivot->prix_piece ?? 0;
                }

                $tva = $piece->tva ?? 0;
                return ($price * $quantity) * (1 + $tva / 100);
            });
        }

        // Add prestations (HT + TVA) using pivot prices - Only for Facture
        if ($model instanceof \App\Models\Facture && $model->relationLoaded('prestations')) {
            $total += $model->prestations->sum(function ($prestation) {
                $quantity = $prestation->pivot->qte_fpr ?? 0;
                $price = $prestation->pivot->prix_prest ?? 0;
                $tva = $prestation->tva ?? 0;
                return ($price * $quantity) * (1 + $tva / 100);
            });
        }

        // Add charges (HT + TVA) using pivot prices - Only for Facture
        if ($model instanceof \App\Models\Facture && $model->relationLoaded('charges')) {
            $total += $model->charges->sum(function ($charge) {
                $quantity = $charge->pivot->qte_fc ?? 0;
                $price = $charge->pivot->prix_charge ?? 0; // Corrected: Get prix_charge from pivot
                $tva = $charge->tva ?? 0;
                return ($price * $quantity) * (1 + $tva / 100);
            });
        }

        // If it's a Facture, add droit_timbre
        if ($model instanceof \App\Models\Facture) {
            $total += $model->droit_timbre ?? 0;
        }

        return (float) $total; // Ensure return type is float
    }
}
