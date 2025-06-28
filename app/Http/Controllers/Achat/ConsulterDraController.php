<?php

namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Dra;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ConsulterDraController extends Controller
{
    /**
     * Display a listing of DRAs for consultation.
     * Only DRAs with specific states are shown.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $dras = Dra::with('centre')
            ->whereIn('etat', ['cloture', 'refuse', 'accepte', 'rembourse'])
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

        return Inertia::render('Achat/ConsulterDra/Index', compact('dras'));
    }

    /**
     * Display the specified DRA and its associated factures and bonAchats.
     *
     * @param string $n_dra The unique identifier of the DRA.
     * @return \Inertia\Response
     */
    public function show($n_dra)
    {
        $dra = Dra::with('centre')->where('n_dra', $n_dra)->firstOrFail();

        // Load factures with related data and pivot prices.
        $factures = $dra->factures()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces' => function ($query) {
                    $query->withPivot('qte_f', 'prix_piece'); // Load prix_piece from pivot
                },
                'prestations' => function ($query) {
                    $query->withPivot('qte_fpr', 'prix_prest'); // Load prix_prest from pivot
                },
                'charges' => function ($query) {
                    $query->withPivot('qte_fc', 'prix_charge');    // Load prix_charge from pivot
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
                    $query->withPivot('qte_ba', 'prix_piece'); // Load prix_piece from pivot
                }
            ])
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

    /**
     * Calculates the total amount for a given model (Facture or BonAchat).
     * This now correctly handles BonAchat having only pieces and Facture having all.
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
                $ht = $piece->pivot->prix_piece ?? 0;
                $tva = $piece->tva ?? 0;
                // Use qte_f for Facture pieces, qte_ba for BonAchat pieces
                $quantity = $piece->pivot->qte_f ?? $piece->pivot->qte_ba ?? 1;
                return $ht * $quantity * (1 + $tva / 100);
            });
        }

        // Add prestations (HT + TVA) using pivot prices - Only for Facture
        if ($model instanceof \App\Models\Facture && $model->relationLoaded('prestations')) {
            $total += $model->prestations->sum(function ($prestation) {
                $ht = $prestation->pivot->prix_prest ?? 0;
                $tva = $prestation->tva ?? 0;
                // For Facture prestations, use qte_fpr
                $quantity = $prestation->pivot->qte_fpr ?? 1;
                return $ht * $quantity * (1 + $tva / 100);
            });
        }

        // Add charges (HT + TVA) using pivot prices - Only for Facture
        if ($model instanceof \App\Models\Facture && $model->relationLoaded('charges')) {
            $total += $model->charges->sum(function ($charge) {
                $ht = $charge->pivot->prix_charge ?? 0; // Correctly get prix_charge from pivot
                $tva = $charge->tva ?? 0;
                // For Facture charges, use qte_fc
                $quantity = $charge->pivot->qte_fc ?? 1;
                return $ht * $quantity * (1 + $tva / 100);
            });
        }

        // If it's a Facture, add droit_timbre
        if ($model instanceof \App\Models\Facture) {
            $total += $model->droit_timbre ?? 0;
        }

        return (float) $total; // Ensure return type is float
    }
}
