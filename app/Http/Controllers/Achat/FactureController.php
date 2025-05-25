<?php

namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Dra;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\Piece;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FactureController extends Controller
{
    public function index(Dra $dra)
    {
        $factures = $dra->factures()
            ->with(['fournisseur:id_fourn,nom_fourn', 'pieces:id_piece,nom_piece,prix_piece,tva'])
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
            ->with(['fournisseur:id_fourn,nom_fourn', 'pieces:id_piece,nom_piece,prix_piece,tva'])
            ->get()
            ->map(function ($facture) {
                $facture->montant = $this->calculateMontant($facture);
                return $facture;
            });

        return Inertia::render('Facture/Show', [
            'dra' => $dra,
            'factures' => $factures,
        ]);
    }

    public function create(Dra $dra)
    {
        $fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);
        $pieces = Piece::all(['id_piece', 'nom_piece', 'prix_piece', 'tva']);

        return inertia('Facture/Create', [
            'dra' => $dra,
            'fournisseurs' => $fournisseurs,
            'pieces' => $pieces,
        ]);
    }

    public function store(Request $request, Dra $dra)
    {
        $request->validate([
            'n_facture' => 'required|unique:factures,n_facture',
            'date_facture' => 'required|date',
            'id_fourn' => 'required|exists:fournisseurs,id_fourn',
            'pieces' => 'required|array|min:1',
            'pieces.*.id_piece' => 'required|exists:pieces,id_piece',
            'pieces.*.qte_f' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $facture = new Facture([
                'n_facture' => $request->n_facture,
                'date_facture' => $request->date_facture,
                'id_fourn' => $request->id_fourn,
                'n_dra' => $dra->n_dra,
            ]);
            $facture->save();

            $pieceAttachments = [];
            foreach ($request->pieces as $piece) {
                $pieceAttachments[$piece['id_piece']] = ['qte_f' => $piece['qte_f']];
            }
            $facture->pieces()->attach($pieceAttachments);

            $totalFacture = $this->calculateMontant($facture);

            // Calculate total_dra including both bonAchats and factures
            $dra->load('bonAchats.pieces', 'factures.pieces');
            $totalDra = '0';

            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateBonAchatMontant($ba), 2);
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateMontant($f), 2);
            }

            if ($totalDra > $dra->centre->montant_disponible) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le montant disponible est insuffisant, il faut un remboursement.']);
            }

            // Update total_dra for the Dra
            $dra->update([
                'total_dra' => (float)$totalDra,
            ]);

            // Update the Centre's montant_disponible
            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible - $totalFacture
            ]);

            DB::commit();

            return redirect()->route('achat.dras.factures.index', $dra->n_dra)
                ->with('success', 'Facture créée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()]);
        }
    }


    public function edit(Dra $dra, Facture $facture)
    {
        $fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);
        $pieces = Piece::all(['id_piece', 'nom_piece', 'prix_piece', 'tva']);
        $facture->load('pieces');

        return Inertia::render('Facture/Edit', [
            'dra' => $dra,
            'facture' => $facture,
            'fournisseurs' => $fournisseurs,
            'allPieces' => $pieces,
        ]);
    }

    public function update(Request $request, $n_dra, $n_facture)
    {
        $request->validate([
            'n_facture' => 'required|unique:factures,n_facture,' . $n_facture . ',n_facture',
            'date_facture' => 'required|date',
            'id_fourn' => 'required|exists:fournisseurs,id_fourn',
            'pieces' => 'required|array|min:1',
            'pieces.*.id_piece' => 'required|exists:pieces,id_piece',
            'pieces.*.qte_f' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $dra = Dra::where('n_dra', $n_dra)->firstOrFail();
            $facture = Facture::where('n_facture', $n_facture)->firstOrFail();
            $centre = $dra->centre;

            // 1. Calculate old amount and restore it to montant_disponible
            $oldMontant = $this->calculateMontant($facture);
            $centre->montant_disponible += $oldMontant;
            $centre->save();

            // 2. Update the facture with new data
            $facture->update([
                'n_facture' => $request->n_facture,
                'date_facture' => $request->date_facture,
                'id_fourn' => $request->id_fourn,
            ]);

            // 3. Sync pieces
            $pieceAttachments = [];
            foreach ($request->pieces as $piece) {
                $pieceAttachments[$piece['id_piece']] = ['qte_f' => $piece['qte_f']];
            }
            $facture->pieces()->sync($pieceAttachments);

            // 4. Refresh relationships and calculate new amount
            $facture->refresh(); // Important to get updated relationships
            $newMontant = $this->calculateMontant($facture);

            // 5. Recalculate total_dra from scratch
            $totalDra = 0;
            $dra->load(['bonAchats.pieces', 'factures.pieces']);

            foreach ($dra->bonAchats as $ba) {
                $totalDra += $this->calculateBonAchatMontant($ba);
            }
            foreach ($dra->factures as $f) {
                $totalDra += $this->calculateMontant($f);
            }

            // 6. Check available balance
            if ($totalDra > $centre->montant_disponible) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le total du DRA dépasse le seuil autorisé du centre.']);
            }

            // 7. Update DRA total
            $dra->total_dra = $totalDra;
            $dra->save();

            // 8. Update centre's available amount
            $centre->montant_disponible -= $newMontant;
            $centre->save();

            DB::commit();

            return redirect()->route('achat.dras.factures.index', $dra->n_dra)
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
            $montantToRestore = $this->calculateMontant($facture);

            // Delete facture and detach related pieces
            $facture->pieces()->detach();
            $facture->delete();

            // Restore montant_disponible in centre
            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible + $montantToRestore
            ]);

            // Reload relationships
            $dra->load('bonAchats.pieces', 'factures.pieces');

            // Recalculate total_dra using bcadd for precision
            $totalDra = '0';
            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateBonAchatMontant($ba), 2);
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateMontant($f), 2);
            }

            $dra->update([
                'total_dra' => (float)$totalDra,
            ]);

            DB::commit();

            return redirect()->route('achat.dras.factures.index', $dra->n_dra)
                ->with('success', 'Facture supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }


    protected function calculateMontant(Facture $facture): float
    {
        return $facture->pieces->sum(function ($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_f;
            return $subtotal * (1 + ($piece->tva / 100));
        });
    }

    protected function calculateBonAchatMontant($bonAchat): float
    {
        return $bonAchat->pieces->sum(function ($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_ba;
            return $subtotal * (1 + ($piece->tva / 100));
        });
    }
}
