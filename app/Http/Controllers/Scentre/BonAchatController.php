<?php

namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\BonAchat;
use App\Models\Dra;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\Piece;
use App\Models\Prestation;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BonAchatController extends Controller
{
    public function index(Dra $dra)
    {
        $bonAchats = $dra->bonAchats()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces:id_piece,nom_piece,prix_piece,tva',
                'prestations:id_prest,nom_prest,prix_prest,tva',
                'charges:id_charge,nom_charge,prix_charge,tva'
            ])
            ->get()
            ->map(function ($bonAchat) {
                $bonAchat->montant = $this->calculateMontant($bonAchat);
                return $bonAchat;
            });

        return Inertia::render('BonAchat/Index', [
            'dra' => $dra,
            'bonAchats' => $bonAchats,
        ]);
    }

    public function show(Dra $dra)
    {
        $bonAchats = $dra->bonAchats()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces:id_piece,nom_piece,prix_piece,tva',
                'prestations:id_prest,nom_prest,prix_prest,tva',
                'charges:id_charge,nom_charge,prix_charge,tva'
            ])
            ->get()
            ->map(function ($bonAchat) {
                $bonAchat->montant = $this->calculateMontant($bonAchat);
                return $bonAchat;
            });

        return Inertia::render('BonAchat/Show', [
            'dra' => $dra,
            'bonAchats' => $bonAchats,
        ]);
    }

    public function create(Dra $dra)
    {
        $fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);
        $pieces = Piece::all(['id_piece', 'nom_piece', 'prix_piece', 'tva']);
        $prestations = Prestation::all(['id_prest', 'nom_prest', 'prix_prest', 'tva']);
        $charges = Charge::all(['id_charge', 'nom_charge', 'prix_charge', 'tva']);

        return Inertia::render('BonAchat/Create', [
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
            'n_ba' => 'required|unique:bon_achats,n_ba',
            'date_ba' => 'required|date',
            'id_fourn' => 'required|exists:fournisseurs,id_fourn',
            'pieces' => 'nullable|array',
            'pieces.*.id_piece' => 'required_with:pieces|exists:pieces,id_piece',
            'pieces.*.qte_ba' => 'required_with:pieces|integer|min:1',
            'prestations' => 'nullable|array',
            'prestations.*.id_prest' => 'required_with:prestations|exists:prestations,id_prest',
            'prestations.*.qte_bapr' => 'required_with:prestations|integer|min:1',
            'charges' => 'nullable|array',
            'charges.*.id_charge' => 'required_with:charges|exists:charges,id_charge',
            'charges.*.qte_bac' => 'required_with:charges|integer|min:1',
        ]);

        // At least one of pieces, prestations, or charges must be present
        if (empty($request->pieces) && empty($request->prestations) && empty($request->charges)) {
            return back()->withErrors(['items' => 'Vous devez sélectionner au moins un article (pièce, prestation ou charge).']);
        }

        DB::beginTransaction();

        try {
            $bonAchat = new BonAchat([
                'n_ba' => $request->n_ba,
                'date_ba' => $request->date_ba,
                'id_fourn' => $request->id_fourn,
                'n_dra' => $dra->n_dra,
            ]);
            $bonAchat->save();

            // Attach pieces if present
            if (!empty($request->pieces)) {
                $pieceAttachments = [];
                foreach ($request->pieces as $piece) {
                    $pieceAttachments[$piece['id_piece']] = ['qte_ba' => $piece['qte_ba']];
                }
                $bonAchat->pieces()->attach($pieceAttachments);
            }

            // Attach prestations if present
            if (!empty($request->prestations)) {
                $prestationAttachments = [];
                foreach ($request->prestations as $prestation) {
                    $prestationAttachments[$prestation['id_prest']] = ['qte_bapr' => $prestation['qte_bapr']];
                }
                $bonAchat->prestations()->attach($prestationAttachments);
            }

            // Attach charges if present
            if (!empty($request->charges)) {
                $chargeAttachments = [];
                foreach ($request->charges as $charge) {
                    $chargeAttachments[$charge['id_charge']] = ['qte_bac' => $charge['qte_bac']];
                }
                $bonAchat->charges()->attach($chargeAttachments);
            }

            $totalBonAchat = $this->calculateMontant($bonAchat);

            if ($totalBonAchat > 10000) {
                DB::rollBack();
                return back()->withErrors(['bonAchat_total' => 'Le montant total du Bon Achat ne peut pas dépasser 10 000 DA.']);
            }

            $dra->load('bonAchats.pieces', 'bonAchats.prestations', 'bonAchats.charges', 'factures.pieces', 'factures.prestations', 'factures.charges');
            $totalDra = '0';

            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateMontant($ba), 2);
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateFactureMontant($f), 2);
            }

            if ($dra->centre->montant_disponible < $totalBonAchat) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le montant disponible est insuffisant, il faut un remboursement.']);
            }

            $dra->update([
                'total_dra' => round($totalDra, 2),
            ]);

            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible - $totalBonAchat
            ]);

            DB::commit();

            return redirect()->route('scentre.dras.bon-achats.index', $dra->n_dra)
                ->with('success', 'Bon d\'achat créé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()]);
        }
    }

    public function edit(Dra $dra, BonAchat $bonAchat)
    {
        $fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);
        $pieces = Piece::all(['id_piece', 'nom_piece', 'prix_piece', 'tva']);
        $prestations = Prestation::all(['id_prest', 'nom_prest', 'prix_prest', 'tva']);
        $charges = Charge::all(['id_charge', 'nom_charge', 'prix_charge', 'tva']);
        $bonAchat->load('pieces', 'prestations', 'charges');

        return Inertia::render('BonAchat/Edit', [
            'dra' => $dra,
            'bonAchat' => $bonAchat,
            'fournisseurs' => $fournisseurs,
            'allPieces' => $pieces,
            'allPrestations' => $prestations,
            'allCharges' => $charges,
        ]);
    }

    public function update(Request $request, $n_dra, $n_ba)
    {
        $request->validate([
            'n_ba' => 'required|unique:bon_achats,n_ba,' . $n_ba . ',n_ba',
            'date_ba' => 'required|date',
            'id_fourn' => 'required|exists:fournisseurs,id_fourn',
            'pieces' => 'nullable|array',
            'pieces.*.id_piece' => 'required_with:pieces|exists:pieces,id_piece',
            'pieces.*.qte_ba' => 'required_with:pieces|integer|min:1',
            'prestations' => 'nullable|array',
            'prestations.*.id_prest' => 'required_with:prestations|exists:prestations,id_prest',
            'prestations.*.qte_bapr' => 'required_with:prestations|integer|min:1',
            'charges' => 'nullable|array',
            'charges.*.id_charge' => 'required_with:charges|exists:charges,id_charge',
            'charges.*.qte_bac' => 'required_with:charges|integer|min:1',
        ]);

        // At least one of pieces, prestations, or charges must be present
        if (empty($request->pieces) && empty($request->prestations) && empty($request->charges)) {
            return back()->withErrors(['items' => 'Vous devez sélectionner au moins un article (pièce, prestation ou charge).']);
        }

        DB::beginTransaction();

        try {
            $dra = Dra::where('n_dra', $n_dra)->firstOrFail();
            $bonAchat = BonAchat::where('n_ba', $n_ba)->firstOrFail();
            $centre = $dra->centre;

            // 1. Calculate old amount and restore it to montant_disponible
            $oldMontant = $this->calculateMontant($bonAchat);
            $centre->montant_disponible += $oldMontant;
            $centre->save();

            // 2. Update the bonAchat with new data
            $bonAchat->update([
                'n_ba' => $request->n_ba,
                'date_ba' => $request->date_ba,
                'id_fourn' => $request->id_fourn,
            ]);

            // 3. Sync pieces if present
            if (!empty($request->pieces)) {
                $pieceAttachments = [];
                foreach ($request->pieces as $piece) {
                    $pieceAttachments[$piece['id_piece']] = ['qte_ba' => $piece['qte_ba']];
                }
                $bonAchat->pieces()->sync($pieceAttachments);
            } else {
                $bonAchat->pieces()->detach();
            }

            // 4. Sync prestations if present
            if (!empty($request->prestations)) {
                $prestationAttachments = [];
                foreach ($request->prestations as $prestation) {
                    $prestationAttachments[$prestation['id_prest']] = ['qte_bapr' => $prestation['qte_bapr']];
                }
                $bonAchat->prestations()->sync($prestationAttachments);
            } else {
                $bonAchat->prestations()->detach();
            }

            // 5. Sync charges if present
            if (!empty($request->charges)) {
                $chargeAttachments = [];
                foreach ($request->charges as $charge) {
                    $chargeAttachments[$charge['id_charge']] = ['qte_bac' => $charge['qte_bac']];
                }
                $bonAchat->charges()->sync($chargeAttachments);
            } else {
                $bonAchat->charges()->detach();
            }

            // 6. Refresh relationships and calculate new amount
            $bonAchat->refresh();
            $newMontant = $this->calculateMontant($bonAchat);

            if ($newMontant > 10000) {
                DB::rollBack();
                return back()->withErrors(['bonAchat_total' => 'Le montant total du Bon Achat ne peut pas dépasser 10 000 DA.']);
            }

            // 7. Recalculate total_dra from scratch
            $totalDra = 0;
            $dra->load(['bonAchats.pieces', 'bonAchats.prestations', 'bonAchats.charges', 'factures.pieces', 'factures.prestations', 'factures.charges']);

            foreach ($dra->bonAchats as $ba) {
                $totalDra += $this->calculateMontant($ba);
            }
            foreach ($dra->factures as $f) {
                $totalDra += $this->calculateFactureMontant($f);
            }

            // 8. Check available balance
            if ($dra->centre->montant_disponible < $newMontant) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le total du DRA dépasse le seuil autorisé du centre.']);
            }

            // 9. Update DRA total
            $dra->total_dra = $totalDra;
            $dra->save();

            // 10. Update centre's available amount
            $centre->montant_disponible -= $newMontant;
            $centre->save();

            DB::commit();

            return redirect()->route('scentre.dras.bon-achats.index', $dra->n_dra)
                ->with('success', 'Bon d\'achat mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
        }
    }

    public function destroy(Dra $dra, BonAchat $bonAchat)
    {
        DB::beginTransaction();

        try {
            $montantToRestore = $this->calculateMontant($bonAchat);

            $bonAchat->pieces()->detach();
            $bonAchat->prestations()->detach();
            $bonAchat->charges()->detach();
            $bonAchat->delete();

            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible + $montantToRestore
            ]);

            $dra->load('bonAchats.pieces', 'bonAchats.prestations', 'bonAchats.charges', 'factures.pieces', 'factures.prestations', 'factures.charges');

            $totalDra = '0';
            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateMontant($ba), 2);
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateFactureMontant($f), 2);
            }

            $dra->update([
                'total_dra' => (float)$totalDra,
            ]);

            DB::commit();

            return redirect()->route('scentre.dras.bon-achats.index', $dra->n_dra)
                ->with('success', 'Bon d\'achat supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    protected function calculateMontant(BonAchat $bonAchat): float
    {
        $piecesTotal = $bonAchat->pieces->sum(function ($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_ba;
            return $subtotal * (1 + ($piece->tva / 100));
        });

        $prestationsTotal = $bonAchat->prestations->sum(function ($prestation) {
            $subtotal = $prestation->prix_prest * $prestation->pivot->qte_bapr;
            return $subtotal * (1 + ($prestation->tva / 100));
        });

        $chargesTotal = $bonAchat->charges->sum(function ($charge) {
            $subtotal = $charge->prix_charge * $charge->pivot->qte_bac;
            return $subtotal * (1 + ($charge->tva / 100));
        });

        return $piecesTotal + $prestationsTotal + $chargesTotal;
    }

    protected function calculateFactureMontant(Facture $facture): float
    {
        $piecesTotal = $facture->pieces->sum(function ($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_f;
            return $subtotal * (1 + ($piece->tva / 100));
        });

        $prestationsTotal = $facture->prestations->sum(function ($prestation) {
            $subtotal = $prestation->prix_prest * $prestation->pivot->qte_fpr;
            return $subtotal * (1 + ($prestation->tva / 100));
        });

        $chargesTotal = $facture->charges->sum(function ($charge) {
            $subtotal = $charge->prix_charge * $charge->pivot->qte_fc;
            return $subtotal * (1 + ($charge->tva / 100));
        });

        return $piecesTotal + $prestationsTotal + $chargesTotal + ($facture->droit_timbre ?? 0);
    }
}
