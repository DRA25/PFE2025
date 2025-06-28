<?php
namespace App\Http\Controllers\Scentre;

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
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces:id_piece,nom_piece,tva',
            ])
            ->get()
            ->map(function ($bonAchat) {
                $bonAchat->montant = $this->calculateBonAchatMontant($bonAchat); // Renamed for clarity
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
                'pieces:id_piece,nom_piece,tva',
            ])
            ->get()
            ->map(function ($bonAchat) {
                $bonAchat->montant = $this->calculateBonAchatMontant($bonAchat); // Renamed for clarity
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
        $pieces = Piece::all(['id_piece', 'nom_piece', 'tva']);

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
            'pieces.*.prix_piece' => 'required|numeric|min:0.01', // Price validation
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
                $pieceAttachments[$piece['id_piece']] = [
                    'qte_ba' => $piece['qte_ba'],
                    'prix_piece' => $piece['prix_piece'] // Store price in pivot
                ];
            }
            $bonAchat->pieces()->attach($pieceAttachments);

            $totalBonAchat = $this->calculateBonAchatMontant($bonAchat); // Renamed for clarity

            if ($totalBonAchat > 10000) {
                DB::rollBack();
                return back()->withErrors(['bonAchat_total' => 'Le montant total du Bon Achat ne peut pas dépasser 10 000 DA.']);
            }

            $dra->load(['bonAchats.pieces', 'factures.pieces', 'factures.prestations', 'factures.charges']); // Added prestations and charges

            $totalDra = '0';

            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateBonAchatMontant($ba), 2); // Renamed for clarity
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateFactureTotal($f), 2); // Using the new comprehensive function
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
        $pieces = Piece::all(['id_piece', 'nom_piece', 'tva']);
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
            'pieces.*.prix_piece' => 'required|numeric|min:0', // Added validation for prix_piece
        ]);

        DB::beginTransaction();

        try {
            $dra = Dra::where('n_dra', $n_dra)->firstOrFail();
            $bonAchat = BonAchat::where('n_ba', $n_ba)->firstOrFail();
            $centre = $dra->centre;

            $oldMontant = $this->calculateBonAchatMontant($bonAchat); // Renamed for clarity
            $centre->montant_disponible += $oldMontant;
            $centre->save();

            $bonAchat->update([
                'n_ba' => $request->n_ba,
                'date_ba' => $request->date_ba,
                'id_fourn' => $request->id_fourn,
            ]);

            $pieceAttachments = [];
            foreach ($request->pieces as $piece) {
                $pieceAttachments[$piece['id_piece']] = [
                    'qte_ba' => $piece['qte_ba'],
                    'prix_piece' => $piece['prix_piece'] // Get prix_piece directly from request
                ];
            }
            $bonAchat->pieces()->sync($pieceAttachments);

            $bonAchat->refresh();
            $newMontant = $this->calculateBonAchatMontant($bonAchat); // Renamed for clarity

            if ($newMontant > 10000) {
                DB::rollBack();
                return back()->withErrors(['bonAchat_total' => 'Le montant total du Bon Achat ne peut pas dépasser 10 000 DA.']);
            }

            $totalDra = 0;
            $dra->load(['bonAchats.pieces', 'factures.pieces', 'factures.prestations', 'factures.charges']); // Added prestations and charges

            foreach ($dra->bonAchats as $ba) {
                $totalDra += $this->calculateBonAchatMontant($ba); // Renamed for clarity
            }
            foreach ($dra->factures as $f) {
                $totalDra += $this->calculateFactureTotal($f); // Using the new comprehensive function
            }

            if ($centre->montant_disponible < $newMontant) {
                DB::rollBack();
                return back()->withErrors(['total_dra' => 'Le total du DRA dépasse le seuil autorisé du centre.']);
            }

            $dra->total_dra = $totalDra;
            $dra->save();

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
            $montantToRestore = $this->calculateBonAchatMontant($bonAchat); // Renamed for clarity

            $bonAchat->pieces()->detach();
            $bonAchat->delete();

            $dra->centre->update([
                'montant_disponible' => $dra->centre->montant_disponible + $montantToRestore
            ]);

            $dra->load(['bonAchats.pieces', 'factures.pieces', 'factures.prestations', 'factures.charges']); // Added prestations and charges

            $totalDra = '0';
            foreach ($dra->bonAchats as $ba) {
                $totalDra = bcadd($totalDra, (string)$this->calculateBonAchatMontant($ba), 2); // Renamed for clarity
            }
            foreach ($dra->factures as $f) {
                $totalDra = bcadd($totalDra, (string)$this->calculateFactureTotal($f), 2); // Using the new comprehensive function
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

    // Renamed the function to avoid naming conflict and clarify its purpose
    protected function calculateBonAchatMontant(BonAchat $bonAchat): float
    {
        $piecesTotal = $bonAchat->pieces->sum(function ($piece) {
            $subtotal = $piece->pivot->prix_piece * $piece->pivot->qte_ba;
            return $subtotal * (1 + ($piece->tva / 100));
        });

        return $piecesTotal;
    }

    // New/Updated function to calculate the total for a Facture
    protected function calculateFactureTotal(Facture $facture): float
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
            // Assuming prix_charge is on the pivot table, or direct on charge model
            // If prix_charge is on the pivot, it should be $charge->pivot->prix_charge
            // Based on your current schema (from DraController), it seems to be $charge->prix_charge
            $subtotal = ($charge->pivot->prix_charge ?? $charge->prix_charge) * $charge->pivot->qte_fc; // Adjusted to check pivot first
            return $subtotal * (1 + ($charge->tva / 100));
        });

        return $piecesTotal + $prestationsTotal + $chargesTotal + ($facture->droit_timbre ?? 0);
    }
}
