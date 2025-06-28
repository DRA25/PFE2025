<?php

namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\BonAchat;
use App\Models\Centre;
use App\Models\Dra;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;


class DraController extends Controller
{
    /**
     * Display a listing of DRAs for the authenticated user's center.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $userCentreId = Auth::user()->id_centre;

        $dras = Dra::with('centre')
            ->where('id_centre', $userCentreId)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dra/Index', [
            'dras' => $dras->map(function ($dra) {
                return [
                    'n_dra' => $dra->n_dra,
                    'id_centre' => $dra->id_centre,
                    'date_creation' => $dra->date_creation->format('Y-m-d'),
                    'etat' => $dra->etat,
                    'total_dra' => $dra->total_dra,
                    'created_at' => $dra->created_at ? $dra->created_at->toISOString() : now()->toISOString(),
                    'centre' => [
                        'seuil_centre' => $dra->centre->seuil_centre,
                        'montant_disponible' => $dra->centre->montant_disponible,
                    ]
                ];
            }),
            'id_centre' => $userCentreId
        ]);
    }

    /**
     * Display the specified DRA and its associated factures and bonAchats.
     *
     * @param string $n_dra The unique identifier of the DRA.
     * @return \Inertia\Response
     */
    public function show($n_dra)
    {
        $userCentreId = Auth::user()->id_centre;
        $dra = Dra::with('centre')->where('n_dra', $n_dra)->firstOrFail();

        // Ensure the user is authorized to view this DRA
        if ($dra->id_centre !== $userCentreId) {
            abort(403, 'Unauthorized action.');
        }

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
                    $query->withPivot('qte_fc', 'prix_charge');
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
                },
            ])
            ->get()
            ->map(function ($bonAchat) {
                $bonAchat->montant = $this->calculateMontant($bonAchat);
                return $bonAchat;
            });

        return Inertia::render('Dra/Show', [
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
     * Automatically creates a new DRA for the authenticated user's center.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function autocreate(Request $request)
    {
        $user = Auth::user();
        $centreId = $user->id_centre;

        $count = Dra::where('id_centre', $centreId)->count() + 1;
        $n_dra = $centreId . str_pad($count, 6, '0', STR_PAD_LEFT);

        Dra::create([
            'n_dra' => $n_dra,
            'id_centre' => $centreId,
            'date_creation' => now(),
            'etat' => 'actif',
            'total_dra' => 0,
        ]);

        return redirect()->route('scentre.dras.index');
    }

    /**
     * Stores a new DRA (though autocreate is used, this method might be for manual creation).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $centreId = Auth::user()->id_centre;
        $centre = Centre::findOrFail($centreId);

        $count = Dra::where('id_centre', $centreId)->count();
        $n_dra = $centreId . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        $totalDra = 0; // Initialize totalDra for new DRA

        // This condition is likely intended to check if the *initial* DRA creation exceeds threshold
        // but since totalDra is 0, it won't prevent creation. Logic might need adjustment if totalDra
        // is supposed to be calculated from initial items in a single form.
        if ($centre->montant_disponible < $totalDra) {
            return back()->withErrors(['montant_disponible' => 'Fonds insuffisants pour créer une nouvelle DRA.']);
        }

        $dra = new Dra();
        $dra->n_dra = $n_dra;
        $dra->id_centre = $centreId;
        $dra->date_creation = now()->toDateString();
        $dra->etat = 'actif';
        $dra->total_dra = $totalDra;
        $dra->save();

        if ($totalDra > 0) { // This will not run as totalDra is 0
            $centre->decrement('montant_disponible', $totalDra);
        }

        return redirect()->route('scentre.dras.index')->with('success', 'DRA créé avec succès.');
    }

    /**
     * Show the form for editing the specified DRA.
     *
     * @param \App\Models\Dra $dra
     * @return \Inertia\Response
     */
    public function edit(Dra $dra)
    {
        return Inertia::render('Dra/Edit', [
            'dra' => $dra,
        ]);
    }

    /**
     * Update the specified DRA's state.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Dra $dra
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Dra $dra)
    {
        $validated = $request->validate([
            'etat' => 'required|string|in:cloture,refuse,accepte',
        ]);

        $dra->update($validated);

        return redirect()->back()->with('success', 'État du DRA mis à jour avec succès.');
    }

    /**
     * Remove the specified DRA from storage.
     *
     * @param string $n_dra The unique identifier of the DRA to destroy.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($n_dra)
    {
        DB::beginTransaction();

        try {
            $userCentreId = Auth::user()->id_centre;
            $dra = Dra::with([
                'bonAchats.pieces' => fn($query) => $query->withPivot('qte_ba', 'prix_piece'), // Load pivot for BA pieces
                'factures.pieces' => fn($query) => $query->withPivot('qte_f', 'prix_piece'),   // Load pivot for Facture pieces
                'factures.prestations' => fn($query) => $query->withPivot('qte_fpr', 'prix_prest'), // Load pivot for Facture prestations
                'factures.charges' => fn($query) => $query->withPivot('qte_fc', 'prix_charge'),   // Load pivot for Facture charges
                'centre'
            ])->where('n_dra', $n_dra)->firstOrFail();

            if ($dra->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }

            if ($dra->etat !== 'actif') {
                return back()->withErrors(['error' => 'Seuls les DRAs actifs peuvent être supprimés']);
            }

            $montantToRestore = '0';

            // Calculate total montant to restore from associated bonAchats
            foreach ($dra->bonAchats as $bonAchat) {
                $montantToRestore = bcadd($montantToRestore, (string)$this->calculateMontant($bonAchat), 2);
            }

            // Calculate total montant to restore from associated factures
            foreach ($dra->factures as $facture) {
                $montantToRestore = bcadd($montantToRestore, (string)$this->calculateMontant($facture), 2);
            }

            // Detach related items before deleting
            foreach ($dra->bonAchats as $bonAchat) {
                $bonAchat->pieces()->detach();
            }
            foreach ($dra->factures as $facture) {
                $facture->pieces()->detach();
                $facture->prestations()->detach();
                $facture->charges()->detach();
            }

            $dra->factures()->delete();
            $dra->bonAchats()->delete();
            $dra->delete();

            // Restore the total amount to the centre's available montant_disponible
            $dra->centre->increment('montant_disponible', (float)$montantToRestore);

            DB::commit();

            return redirect()->route('scentre.dras.index')
                ->with('success', 'DRA supprimé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting DRA: ' . $e->getMessage(), ['n_dra' => $n_dra, 'error' => $e]);
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    /**
     * Close the specified DRA. Only active or refused DRAs can be closed.
     *
     * @param \App\Models\Dra $dra
     * @return \Illuminate\Http\RedirectResponse
     */
    public function close(Dra $dra)
    {
        $userCentreId = Auth::user()->id_centre;

        if ($dra->id_centre !== $userCentreId) {
            abort(403, 'Unauthorized action.');
        }

        $normalizedEtat = strtolower($dra->etat);

        if ($normalizedEtat !== 'refuse' && $normalizedEtat !== 'actif') {
            return back()->withErrors([
                'etat' => 'Seuls les DRAs actifs ou refusés peuvent être clôturés'
            ]);
        }

        $dra->update(['etat' => 'cloture']);

        return redirect()->route('scentre.dras.index')
            ->with('success', 'DRA clôturé avec succès');
    }

    /**
     * Calculate the total amount for a given model (Facture or BonAchat).
     * This function dynamically determines the correct pivot fields based on the model type.
     *
     * @param \App\Models\Facture|\App\Models\BonAchat $model The model instance (Facture or BonAchat).
     * @return float The calculated total amount including VAT and droit_timbre (for Factures).
     */
    protected function calculateMontant($model): float
    {
        $total = 0;

        // Calculate pieces total (HT + TVA) using pivot prices
        if ($model->relationLoaded('pieces')) {
            $total += $model->pieces->sum(function ($piece) use ($model) {
                $quantity = 0;
                $price = 0;

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
                $price = $charge->pivot->prix_charge ?? 0; // Corrected to get price from pivot
                $tva = $charge->tva ?? 0;
                return ($price * $quantity) * (1 + $tva / 100);
            });
        }

        // If it's a Facture, add droit_timbre
        if ($model instanceof \App\Models\Facture) {
            $total += $model->droit_timbre ?? 0;
        }

        return (float) $total;
    }

    /**
     * Export all DRAs for the user's centre to a PDF Brouillard de Caisse Régie.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportAllDras()
    {
        $userCentreId = Auth::user()->id_centre;
        $allItems = collect();
        $centre = Centre::find($userCentreId);
        $centreType = $centre ? $centre->type_centre : 'Marine';
        $centreCode = $centre ? $centre->id_centre : 'N/A';
        $centreSeuil = $centre ? (float) $centre->seuil_centre : 0.00;

        $dras = Dra::with([
            'centre',
            'factures.pieces' => fn($query) => $query->withPivot('qte_f', 'prix_piece'),
            'factures.prestations' => fn($query) => $query->withPivot('qte_fpr', 'prix_prest'),
            'factures.charges' => fn($query) => $query->withPivot('qte_fc', 'prix_charge'),
            'factures.fournisseur',
            'bonAchats.pieces' => fn($query) => $query->withPivot('qte_ba', 'prix_piece'),
            'bonAchats.fournisseur',
            'remboursements.encaissements'
        ])
            ->where('id_centre', $userCentreId)
            ->orderBy('n_dra', 'asc')
            ->get();

        $firstDate = $dras->first() ? Carbon::parse($dras->first()->date_creation) : now();
        $lastDate = $dras->last() ? Carbon::parse($dras->last()->date_creation) : now();

        foreach ($dras as $dra) {
            $draItems = collect();
            $draTotalDecaissement = 0;
            $draTotalEncaissement = 0;

            foreach ($dra->factures as $facture) {
                $piecesTotal = $facture->pieces->sum(function ($piece) {
                    $quantity = $piece->pivot->qte_f ?? 1;
                    $price = $piece->pivot->prix_piece ?? 0;
                    return ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);
                });

                $prestationsTotal = $facture->prestations->sum(function ($prestation) {
                    $quantity = $prestation->pivot->qte_fpr ?? 1;
                    $price = $prestation->pivot->prix_prest ?? 0;
                    return ($price * $quantity) * (1 + ($prestation->tva ?? 0) / 100);
                });

                $chargesTotal = $facture->charges->sum(function ($charge) {
                    $quantity = $charge->pivot->qte_fc ?? 1;
                    $price = $charge->pivot->prix_charge ?? 0; // Corrected to get price from pivot
                    return ($price * $quantity) * (1 + ($charge->tva ?? 0) / 100);
                });

                $droitTimbre = $facture->droit_timbre ?? 0;
                $totalAmount = $piecesTotal + $prestationsTotal + $chargesTotal + $droitTimbre;

                $pieceNames = $facture->pieces->map(function ($piece) {
                    $quantity = $piece->pivot->qte_f ?? 1;
                    return $piece->nom_piece . ' (x' . $quantity.')';
                })->implode(', ');

                $prestationNames = $facture->prestations->map(function ($prestation) {
                    $quantity = $prestation->pivot->qte_fpr ?? 1;
                    return $prestation->nom_prest . ' (x' . $quantity . ')';
                })->implode(', ');

                $chargeNames = $facture->charges->map(function ($charge) {
                    $quantity = $charge->pivot->qte_fc ?? 1;
                    return $charge->nom_charge . ' (x' . $quantity . ')';
                })->implode(', ');

                $libelle = implode(', ', array_filter([$pieceNames, $prestationNames, $chargeNames]));
                $fournisseurName = $facture->fournisseur ? $facture->fournisseur->nom_fourn : 'Non spécifié';

                $draItems->push([
                    'n_dra' => $dra->n_dra,
                    'n_bon' => 'Facture ' . $facture->n_facture,
                    'date_bon' => Carbon::parse($facture->date_facture)->format('d/m/Y'),
                    'libelle' => $libelle,
                    'fournisseur' => $fournisseurName,
                    'encaissement' => '',
                    'decaissement' => number_format($totalAmount, 2, ',', ' '),
                    'obs' => '',
                    'is_total' => false
                ]);

                $draTotalDecaissement += $totalAmount;
            }

            foreach ($dra->bonAchats as $bonAchat) {
                $baPiecesTotal = $bonAchat->pieces->sum(function ($piece) {
                    $quantity = $piece->pivot->qte_ba ?? 1;
                    $price = $piece->pivot->prix_piece ?? 0;
                    return ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);
                });

                $totalAmount = $baPiecesTotal;
                $baPieceNames = $bonAchat->pieces->map(function ($piece) {
                    $quantity = $piece->pivot->qte_ba ?? 1;
                    return $piece->nom_piece . ' (x' . $quantity . ')';
                })->implode(', ');

                $libelle = implode(', ', array_filter([$baPieceNames]));
                $fournisseurName = $bonAchat->fournisseur ? $bonAchat->fournisseur->nom_fourn : 'Non spécifié';

                $draItems->push([
                    'n_dra' => $dra->n_dra,
                    'n_bon' => 'Bon Achat ' . $bonAchat->n_ba,
                    'date_bon' => Carbon::parse($bonAchat->date_ba)->format('d/m/Y'),
                    'libelle' => $libelle,
                    'fournisseur' => $fournisseurName,
                    'encaissement' => '',
                    'decaissement' => number_format($totalAmount, 2, ',', ' '),
                    'obs' => '',
                    'is_total' => false
                ]);

                $draTotalDecaissement += $totalAmount;
            }

            foreach ($dra->remboursements as $remboursement) {
                $draTotalEncaissement += $remboursement->encaissements->sum('montant_enc');
            }

            if ($draTotalEncaissement > 0) {
                $draItems->push([
                    'n_dra' => $dra->n_dra,
                    'n_bon' => '',
                    'date_bon' => $dra->date_creation->format('d/m/Y'),
                    'libelle' => 'Encaissement remboursement',
                    'fournisseur' => '',
                    'encaissement' => number_format($draTotalEncaissement, 2, ',', ' '),
                    'decaissement' => '',
                    'obs' => '',
                    'is_total' => false
                ]);
            }

            if ($draItems->isNotEmpty()) {
                $draItems->push([
                    'n_dra' => '',
                    'n_bon' => '',
                    'date_bon' => '',
                    'libelle' => 'Total DRA',
                    'fournisseur' => '',
                    'encaissement' => number_format($draTotalEncaissement, 2, ',', ' '),
                    'decaissement' => number_format($draTotalDecaissement, 2, ',', ' '),
                    'obs' => '',
                    'is_total' => true
                ]);
            }

            $allItems = $allItems->merge($draItems);
        }

        $rawTotalEncaissement = $allItems->where('is_total', false)->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['encaissement']);
        });

        $rawTotalDecaissement = $allItems->where('is_total', false)->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['decaissement']);
        });

        $rawBalancePeriod = $rawTotalEncaissement - $rawTotalDecaissement;
        $calculatedValue = $centreSeuil + $rawBalancePeriod;

        $pdf = PDF::loadView('scentre.dra.export_brouillard', [
            'items' => $allItems,
            'totalEncaissement' => number_format($rawTotalEncaissement, 2, ',', ' '),
            'totalDecaissement' => number_format($rawTotalDecaissement, 2, ',', ' '),
            'balancePeriod' => number_format($rawBalancePeriod, 2, ',', ' '),
            'centreseuil' => number_format($centreSeuil, 2, ',', ' '),
            'calculatedResult' => number_format($calculatedValue, 2, ',', ' '),
            'id_centre' => $userCentreId,
            'centre_type' => $centreType,
            'centre_code' => $centreCode,
            'periode_debut' => $firstDate->format('d/m/Y'),
            'periode_fin' => $lastDate->format('d/m/Y'),
            'exercice' => $firstDate->format('Y'),
        ]);

        return $pdf->download('brouillard_caisse_regie.pdf');
    }

    /**
     * Export the quarterly statement for the user's centre.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportEtatTrimestriel()
    {
        $user = Auth::user();
        if (!$user->id_centre) {
            abort(403, 'User is not associated with any centre');
        }

        $centreCode = $user->id_centre;
        $formattedCentreCode = '1' . $centreCode;
        $centre = Centre::find($centreCode);
        $centreType = $centre ? $centre->type_centre : 'Inconnu';
        $startDate = Carbon::now()->startOfQuarter();
        $endDate = Carbon::now()->endOfQuarter();
        $items = collect();

        $processDocument = function ($document, $isFacture = true) use (&$items, $formattedCentreCode) {
            $date = $isFacture
                ? Carbon::parse($document->date_facture)->format('d/m/Y')
                : Carbon::parse($document->date_ba)->format('d/m/Y');

            $addedTimbre = false; // Flag to ensure droit_timbre is added only once per facture

            // Process pieces
            if ($document->relationLoaded('pieces')) {
                foreach ($document->pieces as $piece) {
                    if (!$piece) {
                        continue;
                    }

                    $quantity = $isFacture ? ($piece->pivot->qte_f ?? 1) : ($piece->pivot->qte_ba ?? 1);
                    $price = $piece->pivot->prix_piece ?? 0;
                    $montant = ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);

                    // Add droit_timbre to the first item processed for a Facture
                    if ($isFacture && !$addedTimbre) {
                        $montant += $document->droit_timbre ?? 0;
                        $addedTimbre = true;
                    }

                    $items->push([
                        'item' => $items->count() + 1,
                        'libelle' => $piece->nom_piece ?? 'Pièce sans nom',
                        'compte_charge' => $piece->compteGeneral->code ?? 'N/A',
                        'date' => $date,
                        'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                        'cds' => $formattedCentreCode,
                        'fourniture_consommable' => number_format($montant, 2, ',', ' '),
                        'travaux_prestations' => '',
                        'autres' => ''
                    ]);
                }
            }

            // Process prestations
            if ($isFacture && $document->relationLoaded('prestations')) {
                foreach ($document->prestations as $prestation) {
                    if (!$prestation) {
                        continue;
                    }

                    $quantity = $prestation->pivot->qte_fpr ?? 1;
                    $price = $prestation->pivot->prix_prest ?? 0;
                    $montant = ($price * $quantity) * (1 + ($prestation->tva ?? 0) / 100);

                    // Add droit_timbre if it hasn't been added yet (i.e., no pieces were processed)
                    if ($isFacture && !$addedTimbre) {
                        $montant += $document->droit_timbre ?? 0;
                        $addedTimbre = true;
                    }

                    $items->push([
                        'item' => $items->count() + 1,
                        'libelle' => $prestation->nom_prest ?? 'Prestation sans nom',
                        'compte_charge' => $prestation->compteGeneral->code ?? 'N/A',
                        'date' => $date,
                        'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                        'cds' => $formattedCentreCode,
                        'fourniture_consommable' => '',
                        'travaux_prestations' => number_format($montant, 2, ',', ' '),
                        'autres' => ''
                    ]);
                }
            }

            // Process charges
            if ($isFacture && $document->relationLoaded('charges')) {
                foreach ($document->charges as $charge) {
                    if (!$charge) {
                        continue;
                    }

                    $quantity = $charge->pivot->qte_fc ?? 1;
                    $price = $charge->pivot->prix_charge ?? 0; // Corrected to use pivot price
                    $montant = ($price * $quantity) * (1 + ($charge->tva ?? 0) / 100);

                    // Add droit_timbre if it hasn't been added yet (i.e., no pieces or prestations were processed)
                    if ($isFacture && !$addedTimbre) {
                        $montant += $document->droit_timbre ?? 0;
                        $addedTimbre = true;
                    }

                    $items->push([
                        'item' => $items->count() + 1,
                        'libelle' => $charge->nom_charge ?? 'Charge sans nom',
                        'compte_charge' => $charge->compteGeneral->code ?? 'N/A',
                        'date' => $date,
                        'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                        'cds' => $formattedCentreCode,
                        'fourniture_consommable' => '',
                        'travaux_prestations' => '',
                        'autres' => number_format($montant, 2, ',', ' ')
                    ]);
                }
            }
            // The logic to add Droit de Timbre as a separate line item was removed in a previous iteration
            // and is not being re-added here as per your request.
        };

        $factures = Facture::with([
            'pieces' => function ($query) { $query->withPivot('qte_f', 'prix_piece')->with('compteGeneral:code,libelle'); },
            'prestations' => function ($query) { $query->withPivot('qte_fpr', 'prix_prest')->with('compteGeneral:code,libelle'); },
            'charges' => function ($query) { $query->withPivot('qte_fc', 'prix_charge')->with('compteGeneral:code,libelle'); },
            'fournisseur',
            'dra'
        ])
            ->whereHas('dra', fn($q) => $q->where('id_centre', $centreCode))
            ->whereBetween('date_facture', [$startDate, $endDate])
            ->get();

        foreach ($factures as $facture) {
            $processDocument($facture, true);
        }

        $bonAchats = BonAchat::with([
            'pieces' => function ($query) { $query->withPivot('qte_ba', 'prix_piece')->with('compteGeneral:code,libelle'); },
            'fournisseur',
            'dra'
        ])
            ->whereHas('dra', fn($q) => $q->where('id_centre', $centreCode))
            ->whereBetween('date_ba', [$startDate, $endDate])
            ->get();

        foreach ($bonAchats as $bonAchat) {
            $processDocument($bonAchat, false);
        }

        $calculateTotal = fn($field) =>
        $items->sum(fn($item) =>
        (float) str_replace([' ', ','], ['', '.'], $item[$field] ?? '0'));

        $totalFourniture = $calculateTotal('fourniture_consommable');
        $totalTravaux = $calculateTotal('travaux_prestations');
        $totalAutres = $calculateTotal('autres');
        $grandTotal = $totalFourniture + $totalTravaux + $totalAutres;

        $pdf = PDF::loadView('exports.etat_trimestriel', [
            'items' => $items,
            'centreCode' => $formattedCentreCode,
            'centreType' => $centreType,
            'trimestre' => 'Du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y'),
            'totalFourniture' => number_format($totalFourniture, 2, ',', ' '),
            'totalTravaux' => number_format($totalTravaux, 2, ',', ' '),
            'totalAutres' => number_format($totalAutres, 2, ',', ' '),
            'grandTotal' => number_format($grandTotal, 2, ',', ' '),
            'currentDate' => Carbon::now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('etat_trimestriel_' . $formattedCentreCode . '_' . $startDate->format('Y-m') . '.pdf');
    }

    /**
     * Export the quarterly statement for all centres to a single PDF.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportEtatTrimestrielAllCentres()
    {
        $centres = Centre::all();
        $startDate = Carbon::now()->startOfQuarter();
        $endDate = Carbon::now()->endOfQuarter();
        $allCentreData = [];
        $globalTotals = [
            'totalFourniture' => 0,
            'totalTravaux' => 0,
            'totalAutres' => 0,
            'grandTotal' => 0
        ];

        foreach ($centres as $centre) {
            $centreCode = $centre->id_centre;
            $formattedCentreCode = '1' . $centreCode;
            $items = collect();

            $processDocuments = function ($documents, $isFacture = true) use (&$items, $formattedCentreCode) {
                foreach ($documents as $document) {
                    if (!$document) {
                        Log::warning('Invalid document encountered in exportEtatTrimestrielAllCentres');
                        continue;
                    }

                    try {
                        $date = $isFacture
                            ? ($document->date_facture ? Carbon::parse($document->date_facture)->format('d/m/Y') : 'Date invalide')
                            : ($document->date_ba ? Carbon::parse($document->date_ba)->format('d/m/Y') : 'Date invalide');

                        $addedTimbre = false; // Flag for each document to ensure droit_timbre is added only once

                        // Process pieces
                        if ($document->relationLoaded('pieces')) {
                            foreach ($document->pieces as $piece) {
                                if (!$piece) {
                                    continue;
                                }

                                $quantity = $isFacture
                                    ? ($piece->pivot->qte_f ?? 1)
                                    : ($piece->pivot->qte_ba ?? 1);
                                $price = $piece->pivot->prix_piece ?? 0;
                                $montant = ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);

                                // Add droit_timbre to the first item processed for a Facture
                                if ($isFacture && !$addedTimbre) {
                                    $montant += $document->droit_timbre ?? 0;
                                    $addedTimbre = true;
                                }

                                $items->push([
                                    'item' => $items->count() + 1,
                                    'libelle' => $piece->nom_piece ?? 'Pièce sans nom',
                                    'compte_charge' => $piece->compteGeneral->code ?? 'N/A',
                                    'date' => $date,
                                    'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                                    'cds' => $formattedCentreCode,
                                    'fourniture_consommable' => number_format($montant, 2, ',', ' '),
                                    'travaux_prestations' => '',
                                    'autres' => ''
                                ]);
                            }
                        }

                        // Process prestations
                        if ($isFacture && $document->relationLoaded('prestations')) {
                            foreach ($document->prestations as $prestation) {
                                if (!$prestation) {
                                    continue;
                                }

                                $quantity = $prestation->pivot->qte_fpr ?? 1;
                                $price = $prestation->pivot->prix_prest ?? 0;
                                $montant = ($price * $quantity) * (1 + ($prestation->tva ?? 0) / 100);

                                // Add droit_timbre if it hasn't been added yet
                                if ($isFacture && !$addedTimbre) {
                                    $montant += $document->droit_timbre ?? 0;
                                    $addedTimbre = true;
                                }

                                $items->push([
                                    'item' => $items->count() + 1,
                                    'libelle' => $prestation->nom_prest ?? 'Prestation sans nom',
                                    'compte_charge' => $prestation->compteGeneral->code ?? 'N/A',
                                    'date' => $date,
                                    'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                                    'cds' => $formattedCentreCode,
                                    'fourniture_consommable' => '',
                                    'travaux_prestations' => number_format($montant, 2, ',', ' '),
                                    'autres' => ''
                                ]);
                            }
                        }

                        // Process charges
                        if ($isFacture && $document->relationLoaded('charges')) {
                            foreach ($document->charges as $charge) {
                                if (!$charge) {
                                    continue;
                                }

                                $quantity = $charge->pivot->qte_fc ?? 1;
                                $price = $charge->pivot->prix_charge ?? 0; // Corrected to use pivot price
                                $montant = ($price * $quantity) * (1 + ($charge->tva ?? 0) / 100);

                                // Add droit_timbre if it hasn't been added yet
                                if ($isFacture && !$addedTimbre) {
                                    $montant += $document->droit_timbre ?? 0;
                                    $addedTimbre = true;
                                }

                                $items->push([
                                    'item' => $items->count() + 1,
                                    'libelle' => $charge->nom_charge ?? 'Charge sans nom',
                                    'compte_charge' => $charge->compteGeneral->code ?? 'N/A',
                                    'date' => $date,
                                    'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                                    'cds' => $formattedCentreCode,
                                    'fourniture_consommable' => '',
                                    'travaux_prestations' => '',
                                    'autres' => number_format($montant, 2, ',', ' ')
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing document in exportEtatTrimestrielAllCentres', [
                            'document_id' => $document->id ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
            };

            try {
                $factures = Facture::with([
                    'pieces' => function ($query) { $query->withPivot('qte_f', 'prix_piece')->with('compteGeneral:code,libelle'); },
                    'prestations' => function ($query) { $query->withPivot('qte_fpr', 'prix_prest')->with('compteGeneral:code,libelle'); },
                    'charges' => function ($query) { $query->withPivot('qte_fc', 'prix_charge')->with('compteGeneral:code,libelle'); },
                    'fournisseur',
                    'dra'
                ])
                    ->whereHas('dra', fn($q) => $q->where('id_centre', $centreCode))
                    ->whereBetween('date_facture', [$startDate, $endDate])
                    ->get();

                $bonAchats = BonAchat::with([
                    'pieces' => function ($query) { $query->withPivot('qte_ba', 'prix_piece')->with('compteGeneral:code,libelle'); },
                    'fournisseur',
                    'dra'
                ])
                    ->whereHas('dra', fn($q) => $q->where('id_centre', $centreCode))
                    ->whereBetween('date_ba', [$startDate, $endDate])
                    ->get();

                // Process documents for the current centre
                $processDocuments($factures, true);
                $processDocuments($bonAchats, false);

                $calculateTotalCentre = fn($field) =>
                $items->sum(fn($item) =>
                (float) str_replace([' ', ','], ['', '.'], $item[$field] ?? '0'));

                $totalFournitureCentre = $calculateTotalCentre('fourniture_consommable');
                $totalTravauxCentre = $calculateTotalCentre('travaux_prestations');
                $totalAutresCentre = $calculateTotalCentre('autres');
                $grandTotalCentre = $totalFournitureCentre + $totalTravauxCentre + $totalAutresCentre;

                $allCentreData[] = [
                    'centre' => $centre,
                    'centreType' => $centre->type_centre ?? 'N/A',
                    'centreCode' => $centre->code_centre ?? $centre->id_centre,
                    'items' => $items,
                    'totals' => [
                        'totalFourniture' => number_format($totalFournitureCentre, 2, ',', ' '),
                        'totalTravaux' => number_format($totalTravauxCentre, 2, ',', ' '),
                        'totalAutres' => number_format($totalAutresCentre, 2, ',', ' '),
                        'grandTotal' => number_format($grandTotalCentre, 2, ',', ' '),
                    ],
                ];

                $globalTotals['totalFourniture'] += $totalFournitureCentre;
                $globalTotals['totalTravaux'] += $totalTravauxCentre;
                $globalTotals['totalAutres'] += $totalAutresCentre;
                $globalTotals['grandTotal'] += $grandTotalCentre;

            } catch (\Exception $e) {
                Log::error('Error processing centre in exportEtatTrimestrielAllCentres', [
                    'centre_id' => $centreCode,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        $pdf = PDF::loadView('exports.etat_trimestriel_all_centres', [
            'allCentreData' => $allCentreData,
            'globalTotals' => array_map(fn($total) => number_format($total, 2, ',', ' '), $globalTotals),
            'trimestre' => 'Du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y'),
            'currentDate' => Carbon::now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('etat_trimestriel_all_centres_' . $startDate->format('Y-m') . '.pdf');
    }
}
