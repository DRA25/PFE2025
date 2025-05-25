<?php

namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\BonAchat;
use App\Models\Dra;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\Piece;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BonAchatController extends Controller
{
    public function index(Dra $dra)
    {
        $bonAchats = $dra->bonAchats()
            ->with(['fournisseur:id_fourn,nom_fourn', 'pieces:id_piece,nom_piece,prix_piece,tva'])
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
            ->with(['fournisseur:id_fourn,nom_fourn', 'pieces:id_piece,nom_piece,prix_piece,tva'])
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

        return Inertia::render('BonAchat/Create', [
            'dra' => $dra,
            'fournisseurs' => $fournisseurs,
            'pieces' => $pieces,
        ]);
    }

    public function store(Request $request, Dra $dra)
    {
        $request->validate([
            'n_ba' => 'required|unique:bon_achats,n_ba',
            'date_ba' => 'required|date',
            'id_fourn' => 'required|exists:fournisseurs,id_fourn',
            'pieces' => 'required|array|min:1',
            'pieces.*.id_piece' => 'required|exists:pieces,id_piece',
            'pieces.*.qte_ba' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $bonAchat = new BonAchat([
                'n_ba' => $request->n_ba,
                'date_ba' => $request->date_ba,
                'id_fourn' => $request->id_fourn,
                'n_dra' => $dra->n_dra,
            ]);
            $bonAchat->save();

            $pieceAttachments = [];
            foreach ($request->pieces as $piece) {
                $pieceAttachments[$piece['id_piece']] = ['qte_ba' => $piece['qte_ba']];
            }
            $bonAchat->pieces()->attach($pieceAttachments);

            $totalBonAchat = $this->calculateMontant($bonAchat);

            $dra->load('bonAchats.pieces', 'factures.pieces');
            $totalDra = '0';

            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateMontant($ba), 2);
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateFactureMontant($f), 2);
            }

            if ($totalDra > $dra->centre->montant_disponible) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le montant disponible est insuffisant, il faut un remboursement.']);
            }

            $dra->update([
                'total_dra' => (float)$totalDra,
            ]);

            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible - $totalBonAchat
            ]);

            DB::commit();

            return redirect()->route('achat.dras.bon-achats.index', $dra->n_dra)
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
        $bonAchat->load('pieces');

        return Inertia::render('BonAchat/Edit', [
            'dra' => $dra,
            'bonAchat' => $bonAchat,
            'fournisseurs' => $fournisseurs,
            'allPieces' => $pieces,
        ]);
    }

    public function update(Request $request, $n_dra, $n_ba)
    {
        $request->validate([
            'n_ba' => 'required|unique:bon_achats,n_ba,' . $n_ba . ',n_ba',
            'date_ba' => 'required|date',
            'id_fourn' => 'required|exists:fournisseurs,id_fourn',
            'pieces' => 'required|array|min:1',
            'pieces.*.id_piece' => 'required|exists:pieces,id_piece',
            'pieces.*.qte_ba' => 'required|integer|min:1',
        ]);

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

            // 3. Sync pieces
            $pieceAttachments = [];
            foreach ($request->pieces as $piece) {
                $pieceAttachments[$piece['id_piece']] = ['qte_ba' => $piece['qte_ba']];
            }
            $bonAchat->pieces()->sync($pieceAttachments);

            // 4. Refresh relationships and calculate new amount
            $bonAchat->refresh(); // Important to get updated relationships
            $newMontant = $this->calculateMontant($bonAchat);

            // 5. Recalculate total_dra from scratch
            $totalDra = 0;
            $dra->load(['bonAchats.pieces', 'factures.pieces']);

            foreach ($dra->bonAchats as $ba) {
                $totalDra += $this->calculateMontant($ba);
            }
            foreach ($dra->factures as $f) {
                $totalDra += $this->calculateFactureMontant($f);
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

            return redirect()->route('achat.dras.bon-achats.index', $dra->n_dra)
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
            $bonAchat->delete();

            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible + $montantToRestore
            ]);

            $dra->load('bonAchats.pieces', 'factures.pieces');

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

            return redirect()->route('achat.dras.bon-achats.index', $dra->n_dra)
                ->with('success', 'Bon d\'achat supprimé avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    protected function calculateMontant(BonAchat $bonAchat): float
    {
        return $bonAchat->pieces->sum(function ($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_ba;
            return $subtotal * (1 + ($piece->tva / 100));
        });
    }

    protected function calculateFactureMontant(Facture $facture): float
    {
        return $facture->pieces->sum(function ($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_f;
            return $subtotal * (1 + ($piece->tva / 100));
        });
    }
}

