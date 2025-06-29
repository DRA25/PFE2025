<?php
namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\Dra;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\Piece;
use App\Models\Prestation;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FactureController extends Controller
{
    /**
     * Display a listing of the factures for a specific DRA.
     *
     * @param  \App\Models\Dra  $dra
     * @return \Inertia\Response
     */
    public function index(Dra $dra)
    {
        $factures = $dra->factures()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces' => function ($query) {
                    $query->withPivot('qte_f', 'prix_piece'); // Load pivot data for pieces
                },
                'prestations' => function ($query) {
                    $query->withPivot('qte_fpr', 'prix_prest'); // Load pivot data for prestations
                },
                'charges' => function ($query) {
                    $query->withPivot('qte_fc', 'prix_charge'); // Load pivot data for charges
                }
            ])
            ->get()
            ->map(function ($facture) {
                $facture->montant = $this->calculateMontant($facture);
                return $facture;
            });

        return Inertia::render('Facture/Index', [
            'dra' => $dra,
            'factures' => $factures,
        ]);
    }


    public function show(Dra $dra)
    {
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

        return Inertia::render('Facture/Show', [ // Assuming a Facture/Show view that lists factures for a DRA
            'dra' => $dra,
            'factures' => $factures,
        ]);
    }


    public function create(Dra $dra)
    {
        $fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);
        $pieces = Piece::all(['id_piece', 'nom_piece', 'tva']);
        $prestations = Prestation::all(['id_prest', 'nom_prest', 'tva']);
        // When fetching charges for creation, prix_charge is not needed from the Charge model directly.
        // It will be provided by the user in the pivot table for the invoice.
        $charges = Charge::all(['id_charge', 'nom_charge', 'tva']);

        return inertia('Facture/Create', [
            'dra' => $dra,
            'fournisseurs' => $fournisseurs,
            'pieces' => $pieces,
            'prestations' => $prestations,
            'charges' => $charges,
        ]);
    }


    public function store(Request $request, Dra $dra)
    {
        $request->validate([
            'n_facture' => 'required|unique:factures,n_facture',
            'date_facture' => 'required|date',
            'id_fourn' => 'required|exists:fournisseurs,id_fourn',
            'droit_timbre' => 'nullable|numeric',
            'pieces' => 'nullable|array',
            'pieces.*.id_piece' => 'required_with:pieces|exists:pieces,id_piece',
            'pieces.*.qte_f' => 'required_with:pieces|integer|min:1',
            'pieces.*.prix_piece' => 'required_with:pieces|numeric|min:0',
            'prestations' => 'nullable|array',
            'prestations.*.id_prest' => 'required_with:prestations|exists:prestations,id_prest',
            'prestations.*.qte_fpr' => 'required_with:prestations|integer|min:1',
            'prestations.*.prix_prest' => 'required_with:prestations|numeric|min:0',
            'charges' => 'nullable|array',
            'charges.*.id_charge' => 'required_with:charges|exists:charges,id_charge',
            'charges.*.qte_fc' => 'required_with:charges|integer|min:1',
            'charges.*.prix_charge' => 'required_with:charges|numeric|min:0', // Added validation for prix_charge
        ]);

        if (empty($request->pieces) && empty($request->prestations) && empty($request->charges)) {
            return back()->withErrors(['items' => 'Vous devez sélectionner au moins un article (pièce, prestation ou charge).']);
        }

        DB::beginTransaction();

        try {
            $facture = new Facture([
                'n_facture' => $request->n_facture,
                'date_facture' => $request->date_facture,
                'id_fourn' => $request->id_fourn,
                'n_dra' => $dra->n_dra,
                'droit_timbre' => $request->droit_timbre ?? 0,
            ]);
            $facture->save();

            // Attach pieces with price
            if (!empty($request->pieces)) {
                $pieceAttachments = [];
                foreach ($request->pieces as $piece) {
                    $pieceAttachments[$piece['id_piece']] = [
                        'qte_f' => $piece['qte_f'],
                        'prix_piece' => $piece['prix_piece']
                    ];
                }
                $facture->pieces()->attach($pieceAttachments);
            }

            // Attach prestations with price
            if (!empty($request->prestations)) {
                $prestationAttachments = [];
                foreach ($request->prestations as $prestation) {
                    $prestationAttachments[$prestation['id_prest']] = [
                        'qte_fpr' => $prestation['qte_fpr'],
                        'prix_prest' => $prestation['prix_prest']
                    ];
                }
                $facture->prestations()->attach($prestationAttachments);
            }

            // Attach charges with price
            if (!empty($request->charges)) {
                $chargeAttachments = [];
                foreach ($request->charges as $charge) {
                    $chargeAttachments[$charge['id_charge']] = [
                        'qte_fc' => $charge['qte_fc'],
                        'prix_charge' => $charge['prix_charge'] // Store price in pivot
                    ];
                }
                $facture->charges()->attach($chargeAttachments);
            }

            // Refresh facture to get pivot data for total calculation
            $facture->load('pieces', 'prestations', 'charges');
            $totalFacture = $this->calculateMontant($facture);

            if ($totalFacture > 20000) {
                DB::rollBack();
                return back()->withErrors(['facture_total' => 'Le montant total de la facture ne peut pas dépasser 20 000 DA.']);
            }

            // Load relations needed for total DRA calculation
            $dra->load('bonAchats.pieces', 'factures.pieces', 'factures.prestations', 'factures.charges');
            $totalDra = '0'; // Use string for bcadd for precision

            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateBonAchatMontant($ba), 2);
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateMontant($f), 2);
            }

            if ($dra->centre->montant_disponible < $totalFacture) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le montant disponible est insuffisant, il faut un remboursement.']);
            }

            $dra->update([
                'total_dra' => round((float)$totalDra, 2), // Cast back to float for DB storage if needed
            ]);

            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible - $totalFacture
            ]);

            DB::commit();

            return redirect()->route('scentre.dras.factures.index', $dra->n_dra)
                ->with('success', 'Facture créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()]);
        }
    }


    public function edit(Dra $dra, Facture $facture)
    {
        $fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);
        $pieces = Piece::all(['id_piece', 'nom_piece', 'tva']);
        $prestations = Prestation::all(['id_prest', 'nom_prest', 'tva']);
        $charges = Charge::all(['id_charge', 'nom_charge', 'tva']); // Fetch basic charge info

        // Eager load pivot data for all related items
        $facture->load([
            'pieces' => function ($query) {
                $query->withPivot('qte_f', 'prix_piece');
            },
            'prestations' => function ($query) {
                $query->withPivot('qte_fpr', 'prix_prest');
            },
            'charges' => function ($query) {
                $query->withPivot('qte_fc', 'prix_charge'); // Load prix_charge from pivot
            }
        ]);

        return Inertia::render('Facture/Edit', [
            'dra' => $dra,
            'facture' => $facture,
            'fournisseurs' => $fournisseurs,
            'allPieces' => $pieces,
            'allPrestations' => $prestations,
            'allCharges' => $charges,
        ]);
    }


    public function update(Request $request, $n_dra, $n_facture)
    {
        // Find facture by n_facture first for old montant calculation
        $factureToUpdate = Facture::where('n_facture', $n_facture)->firstOrFail();

        $request->validate([
            // Allow n_facture to be unique, but ignore the current facture's n_facture during update
            'n_facture' => 'required|unique:factures,n_facture,' . $factureToUpdate->n_facture . ',n_facture',
            'date_facture' => 'required|date',
            'id_fourn' => 'required|exists:fournisseurs,id_fourn',
            'droit_timbre' => 'nullable|numeric',
            'pieces' => 'nullable|array',
            'pieces.*.id_piece' => 'required_with:pieces|exists:pieces,id_piece',
            'pieces.*.qte_f' => 'required_with:pieces|integer|min:1',
            'pieces.*.prix_piece' => 'required_with:pieces|numeric|min:0',
            'prestations' => 'nullable|array',
            'prestations.*.id_prest' => 'required_with:prestations|exists:prestations,id_prest',
            'prestations.*.qte_fpr' => 'required_with:prestations|integer|min:1',
            'prestations.*.prix_prest' => 'required_with:prestations|numeric|min:0',
            'charges' => 'nullable|array',
            'charges.*.id_charge' => 'required_with:charges|exists:charges,id_charge',
            'charges.*.qte_fc' => 'required_with:charges|integer|min:1',
            'charges.*.prix_charge' => 'required_with:charges|numeric|min:0', // Added validation for prix_charge
        ]);

        if (empty($request->pieces) && empty($request->prestations) && empty($request->charges)) {
            return back()->withErrors(['items' => 'Vous devez sélectionner au moins un article (pièce, prestation ou charge).']);
        }

        DB::beginTransaction();

        try {
            $dra = Dra::where('n_dra', $n_dra)->firstOrFail();
            $facture = $factureToUpdate; // Use the facture retrieved earlier
            $centre = $dra->centre;

            // Load old pivot data for correct oldMontant calculation before detaching/syncing
            $facture->load('pieces', 'prestations', 'charges');
            $oldMontant = $this->calculateMontant($facture);

            // Restore old amount to available montant_disponible
            $centre->montant_disponible += $oldMontant;
            $centre->save();

            $facture->update([
                'n_facture' => $request->n_facture, // This might be problematic if n_facture is the primary key
                'date_facture' => $request->date_facture,
                'id_fourn' => $request->id_fourn,
                'droit_timbre' => $request->droit_timbre ?? 0,
            ]);

            // Sync pieces with price
            if (!empty($request->pieces)) {
                $pieceAttachments = [];
                foreach ($request->pieces as $piece) {
                    $pieceAttachments[$piece['id_piece']] = [
                        'qte_f' => $piece['qte_f'],
                        'prix_piece' => $piece['prix_piece']
                    ];
                }
                $facture->pieces()->sync($pieceAttachments);
            } else {
                $facture->pieces()->detach();
            }

            // Sync prestations with price
            if (!empty($request->prestations)) {
                $prestationAttachments = [];
                foreach ($request->prestations as $prestation) {
                    $prestationAttachments[$prestation['id_prest']] = [
                        'qte_fpr' => $prestation['qte_fpr'],
                        'prix_prest' => $prestation['prix_prest']
                    ];
                }
                $facture->prestations()->sync($prestationAttachments);
            } else {
                $facture->prestations()->detach();
            }

            // Sync charges with price
            if (!empty($request->charges)) {
                $chargeAttachments = [];
                foreach ($request->charges as $charge) {
                    $chargeAttachments[$charge['id_charge']] = [
                        'qte_fc' => $charge['qte_fc'],
                        'prix_charge' => $charge['prix_charge'] // Update price in pivot
                    ];
                }
                $facture->charges()->sync($chargeAttachments);
            } else {
                $facture->charges()->detach();
            }

            // Refresh facture to get the latest pivot data for newMontant calculation
            $facture->refresh();
            $newMontant = $this->calculateMontant($facture);

            if ($newMontant > 20000) {
                DB::rollBack();
                return back()->withErrors(['facture_total' => 'Le montant total de la facture ne peut pas dépasser 20 000 DA.']);
            }

            // Recalculate total DRA after updates
            $dra->load(['bonAchats.pieces', 'factures.pieces', 'factures.prestations', 'factures.charges']);
            $totalDra = '0'; // Use string for bcadd for precision

            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateBonAchatMontant($ba), 2);
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateMontant($f), 2);
            }

            // Check if center has enough available amount for the new total DRA
            // This condition was incorrectly checking against newMontant (current facture) instead of totalDra (entire DRA)
            // It should also consider the threshold of the center.
            if ($dra->centre->montant_disponible - $newMontant < 0) { // Check if new facture exceeds available montant for the centre
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le montant disponible du centre est insuffisant pour cette mise à jour de facture.']);
            }

            $dra->total_dra = (float)$totalDra; // Cast back to float for DB storage if needed
            $dra->save();

            $centre->montant_disponible -= $newMontant; // Subtract only the new facture amount, not total DRA
            $centre->save();

            DB::commit();

            return redirect()->route('scentre.dras.factures.index', $dra->n_dra)
                ->with('success', 'Facture mise à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
        }
    }


    public function destroy(Dra $dra, Facture $facture)
    {
        DB::beginTransaction();

        try {
            // Load pivot data to correctly calculate montantToRestore
            $facture->load('pieces', 'prestations', 'charges');
            $montantToRestore = $this->calculateMontant($facture);

            $facture->pieces()->detach();
            $facture->prestations()->detach();
            $facture->charges()->detach();
            $facture->delete();

            // Restore montant to centre's available amount
            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible + $montantToRestore
            ]);

            // Recalculate total DRA
            $dra->load('bonAchats.pieces', 'factures.pieces', 'factures.prestations', 'factures.charges');

            $totalDra = '0'; // Use string for bcadd for precision
            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateBonAchatMontant($ba), 2);
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateMontant($f), 2);
            }

            $dra->update([
                'total_dra' => (float)$totalDra, // Cast back to float for DB storage if needed
            ]);

            DB::commit();

            return redirect()->route('scentre.dras.factures.index', $dra->n_dra)
                ->with('success', 'Facture supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }


    protected function calculateMontant(Facture $facture): float
    {
        $piecesTotal = $facture->pieces->sum(function ($piece) {
            $price = $piece->pivot->prix_piece;
            $subtotal = $price * $piece->pivot->qte_f;
            return $subtotal * (1 + ($piece->tva / 100));
        });

        $prestationsTotal = $facture->prestations->sum(function ($prestation) {
            $price = $prestation->pivot->prix_prest; // Get price from pivot
            $subtotal = $price * $prestation->pivot->qte_fpr;
            return $subtotal * (1 + ($prestation->tva / 100));
        });

        $chargesTotal = $facture->charges->sum(function ($charge) {
            $price = $charge->pivot->prix_charge; // Get price from pivot
            $subtotal = $price * $charge->pivot->qte_fc;
            return $subtotal * (1 + ($charge->tva / 100));
        });

        return (float)bcadd(
            bcadd(bcadd((string)$piecesTotal, (string)$prestationsTotal, 2), (string)$chargesTotal, 2),
            (string)($facture->droit_timbre ?? 0),
            2
        );
    }


    protected function calculateBonAchatMontant($bonAchat): float
    {
        return $bonAchat->pieces->sum(function ($piece) {
            $price = $piece->pivot->prix_piece;
            $subtotal = $price * $piece->pivot->qte_ba;
            return $subtotal * (1 + ($piece->tva / 100));
        });
    }
}
