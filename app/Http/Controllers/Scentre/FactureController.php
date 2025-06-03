<?php

namespace App\Http\Controllers\Scentre;

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
            'droit_timbre' => 'nullable|numeric',
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
                'droit_timbre' => $request->droit_timbre ?? 0,
            ]);
            $facture->save();

            $pieceAttachments = [];
            foreach ($request->pieces as $piece) {
                $pieceAttachments[$piece['id_piece']] = ['qte_f' => $piece['qte_f']];
            }
            $facture->pieces()->attach($pieceAttachments);

            $totalFacture = $this->calculateMontant($facture);

            // Check maximum facture limit
            if ($totalFacture > 20000) {
                DB::rollBack();
                return back()->withErrors(['facture_total' => 'Le montant total de la facture ne peut pas dépasser 20 000 DA.']);
            }

            // Calculate total_dra including both bonAchats and factures
            $dra->load('bonAchats.pieces', 'factures.pieces');
            $totalDra = '0';

            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateBonAchatMontant($ba),2);
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateMontant($f),2);
            }

            if ($dra->centre->montant_disponible < $totalFacture) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le montant disponible est insuffisant, il faut un remboursement.']);
            }

            $dra->update([
                'total_dra' => round($totalDra, 2),
            ]);


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
            'droit_timbre' => 'nullable|numeric',
            'pieces' => 'required|array|min:1',
            'pieces.*.id_piece' => 'required|exists:pieces,id_piece',
            'pieces.*.qte_f' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();

        try {
            $dra = Dra::where('n_dra', $n_dra)->firstOrFail();
            $facture = Facture::where('n_facture', $n_facture)->firstOrFail();
            $centre = $dra->centre;

            $oldMontant = $this->calculateMontant($facture);
            $centre->montant_disponible += $oldMontant;
            $centre->save();

            $facture->update([
                'n_facture' => $request->n_facture,
                'date_facture' => $request->date_facture,
                'id_fourn' => $request->id_fourn,
                'droit_timbre' => $request->droit_timbre ?? 0,
            ]);

            $pieceAttachments = [];
            foreach ($request->pieces as $piece) {
                $pieceAttachments[$piece['id_piece']] = ['qte_f' => $piece['qte_f']];
            }
            $facture->pieces()->sync($pieceAttachments);

            $facture->refresh();
            $newMontant = $this->calculateMontant($facture);

            // Check maximum facture limit
            if ($newMontant > 20000) {
                DB::rollBack();
                return back()->withErrors(['facture_total' => 'Le montant total de la facture ne peut pas dépasser 20 000 DA.']);
            }

            $totalDra = 0;
            $dra->load(['bonAchats.pieces', 'factures.pieces']);

            foreach ($dra->bonAchats as $ba) {
                $totalDra += $this->calculateBonAchatMontant($ba);
            }
            foreach ($dra->factures as $f) {
                $totalDra += $this->calculateMontant($f);
            }

            if ($dra->centre->montant_disponible < $newMontant) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le total du DRA dépasse le seuil autorisé du centre.']);
            }

            $dra->total_dra = $totalDra;
            $dra->save();

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

            $facture->pieces()->detach();
            $facture->delete();

            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible + $montantToRestore
            ]);

            $dra->load('bonAchats.pieces', 'factures.pieces');

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
        $piecesTotal = $facture->pieces->sum(function ($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_f;
            return $subtotal * (1 + ($piece->tva / 100));
        });

        return $piecesTotal + ($facture->droit_timbre ?? 0);
    }

    protected function calculateBonAchatMontant($bonAchat): float
    {
        return $bonAchat->pieces->sum(function ($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_ba;
            return $subtotal * (1 + ($piece->tva / 100));
        });
    }
}
