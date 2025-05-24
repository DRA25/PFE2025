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
            // Create the facture
            $facture = new Facture([
                'n_facture' => $request->n_facture,
                'date_facture' => $request->date_facture,
                'id_fourn' => $request->id_fourn,
                'n_dra' => $dra->n_dra,
            ]);
            $facture->save();

            // Attach pieces with quantities
            $pieceAttachments = [];
            foreach ($request->pieces as $piece) {
                $pieceAttachments[$piece['id_piece']] = ['qte_f' => $piece['qte_f']];
            }
            $facture->pieces()->attach($pieceAttachments);

            // Calculate total amount
            $totalFacture = $this->calculateMontant($facture);

            // Check DRA total
            $totalDra = $dra->bonAchats()->sum('montant_ba') + $dra->factures()
                    ->with('pieces')
                    ->get()
                    ->sum(function($f) {
                        return $this->calculateMontant($f);
                    });

            if ($totalDra > $dra->centre->montant_disponible) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le montant disponible est insuffisant, il faut un remboursement.']);
            }

            // Update DRA and centre
            $dra->update([
                'total_dra' => $totalDra,
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

        // Load facture with pieces
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

            // Calculate old amount before changes
            $oldMontant = $this->calculateMontant($facture);

            // Update facture details
            $facture->update([
                'n_facture' => $request->n_facture,
                'date_facture' => $request->date_facture,
                'id_fourn' => $request->id_fourn,
            ]);

            // Sync pieces with quantities
            $pieceAttachments = [];
            foreach ($request->pieces as $piece) {
                $pieceAttachments[$piece['id_piece']] = ['qte_f' => $piece['qte_f']];
            }
            $facture->pieces()->sync($pieceAttachments);

            // Calculate new amount
            $newMontant = $this->calculateMontant($facture);

            // Restore old montant to centre first
            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible + $oldMontant
            ]);

            // Calculate new DRA total
            $totalDra = $dra->bonAchats()->sum('montant_ba') +
                $dra->factures()
                    ->with('pieces')
                    ->get()
                    ->sum(function($f) {
                        return $this->calculateMontant($f);
                    });

            if ($totalDra > $dra->centre->montant_disponible) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le total du DRA dépasse le seuil autorisé du centre.']);
            }

            $dra->update([
                'total_dra' => $totalDra
            ]);

            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible - $newMontant
            ]);

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
            // Calculate amount to restore
            $montantToRestore = $this->calculateMontant($facture);

            $facture->delete();

            // Restore montant to centre
            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible + $montantToRestore
            ]);

            // Update total_dra
            $dra->update([
                'total_dra' => $dra->bonAchats()->sum('montant_ba') +
                    $dra->factures()
                        ->with('pieces')
                        ->get()
                        ->sum(function($f) {
                            return $this->calculateMontant($f);
                        })
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
        return $facture->pieces->sum(function($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_f;
            return $subtotal * (1 + ($piece->tva / 100));
        });
    }
}
