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
use Inertia\Inertia;

class DraController extends Controller
{
    public function index()
    {
        $userCentreId = Auth::user()->id_centre;

        $dras = Dra::with('centre') // eager load to prevent N+1
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


    public function show($n_dra)
    {
        $userCentreId = Auth::user()->id_centre;

        $dra = Dra::with('centre')->where('n_dra', $n_dra)->firstOrFail();

        if ($dra->id_centre !== $userCentreId) {
            abort(403, 'Unauthorized action.');
        }

        // Load factures with pieces, prestations, and charges
        $factures = $dra->factures()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces:id_piece,nom_piece,prix_piece,tva',
                'prestations:id_prest,nom_prest,desc_prest,prix_prest,tva',
                'charges:id_charge,nom_charge,desc_change,prix_charge,tva'
            ])
            ->get()
            ->map(function ($facture) {
                $facture->montant = $this->calculateMontant($facture);
                return $facture;
            });

        // Load bonAchats with pieces
        $bonAchats = $dra->bonAchats()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces:id_piece,nom_piece,prix_piece,tva',
                'prestations:id_prest,nom_prest,desc_prest,prix_prest,tva',
                'charges:id_charge,nom_charge,desc_change,prix_charge,tva'
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



    public function autocreate(Request $request)
    {
        $user = Auth::user();
        $centreId = $user->id_centre;

        // Count existing DRA entries for this centre
        $count = Dra::where('id_centre', $centreId)->count() + 1;

        // Create the n_dra format like "CENTREID-0001"
        $n_dra = $centreId . str_pad($count, 6, '0', STR_PAD_LEFT);

        // Create the DRA
        Dra::create([
            'n_dra' => $n_dra,
            'id_centre' => $centreId,
            'date_creation' => now(),
            'etat' => 'actif',
            'total_dra' => 0,
        ]);

        return redirect()->route('scentre.dras.index');
    }


    public function store(Request $request)
    {
        $centreId = Auth::user()->id_centre;

        $centre = Centre::findOrFail($centreId);

        $count = Dra::where('id_centre', $centreId)->count();
        $n_dra = $centreId . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // Default value; could be changed to allow setting total_dra via request
        $totalDra = 0;

        // Optional: If you want to prevent creating DRA with insufficient funds
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

        // Update montant_disponible if needed
        if ($totalDra > 0) {
            $centre->decrement('montant_disponible', $totalDra);
        }

        return redirect()->route('scentre.dras.index')->with('success', 'DRA créé avec succès.');
    }


    public function edit(Dra $dra)
{

return Inertia::render('Dra/Edit', [
'dra' => $dra,
]);
}

    public function update(Request $request, Dra $dra)
    {


        $validated = $request->validate([
            'etat' => 'required|string|in:cloture,refuse,accepte',
        ]);

        $dra->update($validated);

        return redirect()->back()->with('success', 'État du DRA mis à jour avec succès.');
    }


    public function destroy($n_dra)
    {
        DB::beginTransaction();

        try {
            $userCentreId = Auth::user()->id_centre;
            $dra = Dra::with(['bonAchats.pieces', 'factures.pieces', 'centre'])->where('n_dra', $n_dra)->firstOrFail();

            // Authorization: Ensure the DRA belongs to the user's center
            if ($dra->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }

            if ($dra->etat !== 'actif') {
                return back()->withErrors(['error' => 'Seuls les DRAs actifs peuvent être supprimés']);
            }

            // Calculate the total montant to restore
            $montantToRestore = '0';

            foreach ($dra->bonAchats as $bonAchat) {
                $montantToRestore = bcadd($montantToRestore, (string)$this->calculateMontant($bonAchat), 2);
            }

            foreach ($dra->factures as $facture) {
                $montantToRestore = bcadd($montantToRestore, (string)$this->calculateMontant($facture), 2);
            }

            // Delete related factures and bonAchats
            $dra->factures()->delete();
            $dra->bonAchats()->delete();

            // Delete the DRA itself
            $dra->delete();

            // Update the centre's montant_disponible
            $dra->centre->increment('montant_disponible', $montantToRestore);

            DB::commit();

            return redirect()->route('scentre.dras.index')
                ->with('success', 'DRA supprimé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }


    public function close(Dra $dra)
    {
        $userCentreId = Auth::user()->id_centre;

        // Authorization: Ensure the DRA belongs to the user's center
        if ($dra->id_centre !== $userCentreId) {
            abort(403, 'Unauthorized action.');
        }

        // Convert to lowercase for consistent comparison
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

    protected function calculateMontant($model)
    {
        $total = 0;

        // Calculate pieces total (HT + TVA)
        if ($model->relationLoaded('pieces')) {
            $total += $model->pieces->sum(function ($piece) {
                $ht = $piece->prix_piece;
                $tva = $piece->tva ?? 0;
                return $ht * (1 + $tva / 100);
            });
        }

        // Add prestations (HT + TVA)
        if ($model->relationLoaded('prestations')) {
            $total += $model->prestations->sum(function ($prestation) {
                $ht = $prestation->prix_prest;
                $tva = $prestation->tva ?? 0;
                return $ht * (1 + $tva / 100);
            });
        }

        // Add charges (HT + TVA)
        if ($model->relationLoaded('charges')) {
            $total += $model->charges->sum(function ($charge) {
                $ht = $charge->prix_charge;
                $tva = $charge->tva ?? 0;
                return $ht * (1 + $tva / 100);
            });
        }

        // If it's a Facture, add droit_timbre
        if ($model instanceof \App\Models\Facture) {
            $total += $model->droit_timbre ?? 0;
        }

        return $total;
    }



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
            'factures.pieces',
            'factures.prestations',
            'factures.charges',
            'factures.fournisseur',
            'bonAchats.pieces',
            'bonAchats.prestations', // Add prestations to bonAchats eager load
            'bonAchats.charges',    // Add charges to bonAchats eager load
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

            // Process factures (decaissement)
            foreach ($dra->factures as $facture) {
                // Calculate pieces total (HT + TVA)
                $piecesTotal = $facture->pieces->sum(function ($piece) {
                    $quantity = $piece->pivot->qte_f ?? 1; // Direct pivot access
                    return ($piece->prix_piece * $quantity) * (1 + ($piece->tva ?? 0) / 100);
                });

                // Calculate prestations total (HT + TVA)
                $prestationsTotal = $facture->prestations->sum(function ($prestation) {
                    $quantity = $prestation->pivot->qte_fpr ?? 1; // Direct pivot access
                    return ($prestation->prix_prest * $quantity) * (1 + ($prestation->tva ?? 0) / 100);
                });

                // Calculate charges total (HT + TVA)
                $chargesTotal = $facture->charges->sum(function ($charge) {
                    $quantity = $charge->pivot->qte_fc ?? 1; // Direct pivot access
                    return ($charge->prix_charge * $quantity) * (1 + ($charge->tva ?? 0) / 100);
                });

                $droitTimbre = $facture->droit_timbre ?? 0;
                $totalAmount = $piecesTotal + $prestationsTotal + $chargesTotal + $droitTimbre;

                // Combine all item names with quantities for the libelle
                $pieceNames = $facture->pieces->map(function ($piece) {
                    $quantity = $piece->pivot->qte_f ?? 1;
                    return $piece->nom_piece . ' (x' . $quantity . ')';
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
                    'n_bon' => 'Facture ' . $facture->n_facture, // Indicate it's a facture
                    'date_bon' => Carbon::parse($facture->date_facture)->format('d/m/Y'), // Use facture date
                    'libelle' => $libelle,
                    'fournisseur' => $fournisseurName,
                    'encaissement' => '',
                    'decaissement' => number_format($totalAmount, 2, ',', ' '),
                    'obs' => '',
                    'is_total' => false
                ]);

                $draTotalDecaissement += $totalAmount;
            }

            // Process bonAchats (decaissement) - UPDATED SECTION
            foreach ($dra->bonAchats as $bonAchat) {
                // Calculate pieces total (HT + TVA)
                $baPiecesTotal = $bonAchat->pieces->sum(function ($piece) {
                    $quantity = $piece->pivot->qte_ba ?? 1;
                    return ($piece->prix_piece * $quantity) * (1 + ($piece->tva ?? 0) / 100);
                });

                // Calculate prestations total (HT + TVA) for Bon d'Achat
                $baPrestationsTotal = $bonAchat->prestations->sum(function ($prestation) {
                    // Assuming similar pivot structure for bonAchats_prestations
                    $quantity = $prestation->pivot->qte_bapr ?? 1; // Adjust pivot column name if different
                    return ($prestation->prix_prest * $quantity) * (1 + ($prestation->tva ?? 0) / 100);
                });

                // Calculate charges total (HT + TVA) for Bon d'Achat
                $baChargesTotal = $bonAchat->charges->sum(function ($charge) {
                    // Assuming similar pivot structure for bonAchats_charges
                    $quantity = $charge->pivot->qte_bac ?? 1; // Adjust pivot column name if different
                    return ($charge->prix_charge * $quantity) * (1 + ($charge->tva ?? 0) / 100);
                });

                $totalAmount = $baPiecesTotal + $baPrestationsTotal + $baChargesTotal;

                // Combine all item names with quantities for the libelle for Bon d'Achat
                $baPieceNames = $bonAchat->pieces->map(function ($piece) {
                    $quantity = $piece->pivot->qte_ba ?? 1;
                    return $piece->nom_piece . ' (x' . $quantity . ')';
                })->implode(', ');

                $baPrestationNames = $bonAchat->prestations->map(function ($prestation) {
                    $quantity = $prestation->pivot->qte_bapr ?? 1; // Adjust pivot column name if different
                    return $prestation->nom_prest . ' (x' . $quantity . ')';
                })->implode(', ');

                $baChargeNames = $bonAchat->charges->map(function ($charge) {
                    $quantity = $charge->pivot->qte_bac ?? 1; // Adjust pivot column name if different
                    return $charge->nom_charge . ' (x' . $quantity . ')';
                })->implode(', ');

                $libelle = implode(', ', array_filter([$baPieceNames, $baPrestationNames, $baChargeNames]));

                $fournisseurName = $bonAchat->fournisseur ? $bonAchat->fournisseur->nom_fourn : 'Non spécifié';

                $draItems->push([
                    'n_dra' => $dra->n_dra,
                    'n_bon' => 'Bon Achat ' . $bonAchat->n_ba, // Indicate it's a bon d'achat
                    'date_bon' => Carbon::parse($bonAchat->date_ba)->format('d/m/Y'), // Use bonAchat date
                    'libelle' => $libelle,
                    'fournisseur' => $fournisseurName,
                    'encaissement' => '',
                    'decaissement' => number_format($totalAmount, 2, ',', ' '),
                    'obs' => '',
                    'is_total' => false
                ]);

                $draTotalDecaissement += $totalAmount;
            }

            // Calculate total encaissement for this DRA
            foreach ($dra->remboursements as $remboursement) {
                $draTotalEncaissement += $remboursement->encaissements->sum('montant_enc');
            }

            // Add encaissement row if there are any
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

            // Add DRA total row if there are items
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

        // Calculate grand totals
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


    public function exportEtatTrimestriel()
    {
        $user = Auth::user();

        if (!$user->id_centre) {
            abort(403, 'User is not associated with any centre');
        }

        $centreCode = $user->id_centre;
        $formattedCentreCode = '1' . $centreCode;

        // 🟢 Fetch centre type
        $centre = Centre::find($centreCode);
        $centreType = $centre ? $centre->type_centre : 'Inconnu';

        $startDate = Carbon::now()->startOfQuarter();
        $endDate = Carbon::now()->endOfQuarter();
        $items = collect();

        $processDocument = function ($document, $isFacture = true) use (&$items, $formattedCentreCode) {
            $date = $isFacture
                ? Carbon::parse($document->date_facture)->format('d/m/Y')
                : Carbon::parse($document->date_ba)->format('d/m/Y');

            $addedTimbre = false;

            foreach ($document->pieces as $piece) {
                $quantity = $isFacture ? ($piece->pivot->qte_f ?? 1) : ($piece->pivot->qte_ba ?? 1);
                $montant = ($piece->prix_piece * $quantity) * (1 + ($piece->tva ?? 0) / 100);
                if ($isFacture && !$addedTimbre) {
                    $montant += $document->droit_timbre ?? 0;
                    $addedTimbre = true;
                }

                $items->push([
                    'item' => $items->count() + 1,
                    'libelle' => $piece->nom_piece,
                    'compte_charge' => $piece->compteGeneral->code ?? 'N/A',
                    'date' => $date,
                    'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                    'cds' => $formattedCentreCode,
                    'fourniture_consommable' => number_format($montant, 2, ',', ' '),
                    'travaux_prestations' => '',
                    'autres' => ''
                ]);
            }

            foreach ($document->prestations as $prestation) {
                $quantity = $isFacture ? ($prestation->pivot->qte_fpr ?? 1) : ($prestation->pivot->qte_bapr ?? 1);
                $montant = ($prestation->prix_prest * $quantity) * (1 + ($prestation->tva ?? 0) / 100);
                if ($isFacture && !$addedTimbre) {
                    $montant += $document->droit_timbre ?? 0;
                    $addedTimbre = true;
                }

                $items->push([
                    'item' => $items->count() + 1,
                    'libelle' => $prestation->nom_prest,
                    'compte_charge' => $prestation->compteGeneral->code ?? 'N/A',
                    'date' => $date,
                    'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                    'cds' => $formattedCentreCode,
                    'fourniture_consommable' => '',
                    'travaux_prestations' => number_format($montant, 2, ',', ' '),
                    'autres' => ''
                ]);
            }

            foreach ($document->charges as $charge) {
                $quantity = $isFacture ? ($charge->pivot->qte_fc ?? 1) : ($charge->pivot->qte_bac ?? 1);
                $montant = ($charge->prix_charge * $quantity) * (1 + ($charge->tva ?? 0) / 100);
                if ($isFacture && !$addedTimbre) {
                    $montant += $document->droit_timbre ?? 0;
                    $addedTimbre = true;
                }

                $items->push([
                    'item' => $items->count() + 1,
                    'libelle' => $charge->nom_charge,
                    'compte_charge' => $charge->compteGeneral->code ?? 'N/A',
                    'date' => $date,
                    'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                    'cds' => $formattedCentreCode,
                    'fourniture_consommable' => '',
                    'travaux_prestations' => '',
                    'autres' => number_format($montant, 2, ',', ' ')
                ]);
            }
        };

        $factures = Facture::with([
            'pieces.compteGeneral',
            'prestations.compteGeneral',
            'charges.compteGeneral',
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
            'pieces.compteGeneral',
            'prestations.compteGeneral',
            'charges.compteGeneral',
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

        // 🟢 Pass centreType to the view
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

    public function exportEtatTrimestrielAllCentres()
    {
        // Get all centers
        $centres = Centre::all();

        // Get current quarter dates
        $startDate = Carbon::now()->startOfQuarter();
        $endDate = Carbon::now()->endOfQuarter();

        // Prepare data for all centers
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

            // Process documents for this center
            $processDocuments = function ($documents, $isFacture = true) use (&$items, $formattedCentreCode) {
                foreach ($documents as $document) {
                    $date = $isFacture
                        ? Carbon::parse($document->date_facture)->format('d/m/Y')
                        : Carbon::parse($document->date_ba)->format('d/m/Y');
                    $addedTimbre = false;

                    // Process pieces
                    foreach ($document->pieces as $piece) {
                        $quantity = $isFacture
                            ? ($piece->pivot->qte_f ?? 1)
                            : ($piece->pivot->qte_ba ?? 1);
                        $montant = ($piece->prix_piece * $quantity) * (1 + ($piece->tva ?? 0) / 100);

                        if ($isFacture && !$addedTimbre) {
                            $montant += $document->droit_timbre ?? 0;
                            $addedTimbre = true;
                        }

                        $items->push([
                            'item' => $items->count() + 1,
                            'libelle' => $piece->nom_piece,
                            'compte_charge' => $piece->compteGeneral->code ?? 'N/A',
                            'date' => $date,
                            'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                            'cds' => $formattedCentreCode,
                            'fourniture_consommable' => number_format($montant, 2, ',', ' '),
                            'travaux_prestations' => '',
                            'autres' => ''
                        ]);
                    }

                    // Process prestations
                    foreach ($document->prestations as $prestation) {
                        $quantity = $isFacture
                            ? ($prestation->pivot->qte_fpr ?? 1)
                            : ($prestation->pivot->qte_bapr ?? 1);
                        $montant = ($prestation->prix_prest * $quantity) * (1 + ($prestation->tva ?? 0) / 100);

                        if ($isFacture && !$addedTimbre) {
                            $montant += $document->droit_timbre ?? 0;
                            $addedTimbre = true;
                        }

                        $items->push([
                            'item' => $items->count() + 1,
                            'libelle' => $prestation->nom_prest,
                            'compte_charge' => $prestation->compteGeneral->code ?? 'N/A',
                            'date' => $date,
                            'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                            'cds' => $formattedCentreCode,
                            'fourniture_consommable' => '',
                            'travaux_prestations' => number_format($montant, 2, ',', ' '),
                            'autres' => ''
                        ]);
                    }

                    // Process charges
                    foreach ($document->charges as $charge) {
                        $quantity = $isFacture
                            ? ($charge->pivot->qte_fc ?? 1)
                            : ($charge->pivot->qte_bac ?? 1);
                        $montant = ($charge->prix_charge * $quantity) * (1 + ($charge->tva ?? 0) / 100);

                        if ($isFacture && !$addedTimbre) {
                            $montant += $document->droit_timbre ?? 0;
                            $addedTimbre = true;
                        }

                        $items->push([
                            'item' => $items->count() + 1,
                            'libelle' => $charge->nom_charge,
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
            };

            // Get factures for this center
            $factures = Facture::with([
                'pieces.compteGeneral',
                'prestations.compteGeneral',
                'charges.compteGeneral',
                'fournisseur',
                'dra'
            ])
                ->whereHas('dra', fn($q) => $q->where('id_centre', $centreCode))
                ->whereBetween('date_facture', [$startDate, $endDate])
                ->get();

            // Get bonAchats for this center
            $bonAchats = BonAchat::with([
                'pieces.compteGeneral',
                'prestations.compteGeneral',
                'charges.compteGeneral',
                'fournisseur',
                'dra'
            ])
                ->whereHas('dra', fn($q) => $q->where('id_centre', $centreCode))
                ->whereBetween('date_ba', [$startDate, $endDate])
                ->get();

            $processDocuments($factures, true);
            $processDocuments($bonAchats, false);

            // Calculate totals for this center
            $calculateTotal = fn($field) => $items->sum(
                fn($item) => (float) str_replace([' ', ','], ['', '.'], $item[$field] ?? '0')
            );

            $centreTotals = [
                'totalFourniture' => $calculateTotal('fourniture_consommable'),
                'totalTravaux' => $calculateTotal('travaux_prestations'),
                'totalAutres' => $calculateTotal('autres'),
                'grandTotal' => 0
            ];

            $centreTotals['grandTotal'] =
                $centreTotals['totalFourniture'] +
                $centreTotals['totalTravaux'] +
                $centreTotals['totalAutres'];

            // Add to global totals
            $globalTotals['totalFourniture'] += $centreTotals['totalFourniture'];
            $globalTotals['totalTravaux'] += $centreTotals['totalTravaux'];
            $globalTotals['totalAutres'] += $centreTotals['totalAutres'];
            $globalTotals['grandTotal'] += $centreTotals['grandTotal'];

            // Format numbers for display
            $centreTotals = array_map(function($value) {
                return number_format($value, 2, ',', ' ');
            }, $centreTotals);

            // Store center data
            $allCentreData[] = [
                'centreCode' => $formattedCentreCode,
                'centreType' => $centre->type_centre,
                'items' => $items,
                'totals' => $centreTotals
            ];
        }

        // Format global totals
        $globalTotals = array_map(function($value) {
            return number_format($value, 2, ',', ' ');
        }, $globalTotals);

        // Generate PDF
        $pdf = PDF::loadView('exports.etat_trimestriel_all_centres', [
            'allCentreData' => $allCentreData,
            'globalTotals' => $globalTotals,
            'trimestre' => 'Du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y'),
            'currentDate' => Carbon::now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('etat_trimestriel_all_centres_' . $startDate->format('Y-m') . '.pdf');
    }

}
