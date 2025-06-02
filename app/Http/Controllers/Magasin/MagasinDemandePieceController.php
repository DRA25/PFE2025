<?php
namespace App\Http\Controllers\Magasin;

use App\Http\Controllers\Controller;
use App\Models\DemandePiece;
use App\Models\QuantiteStocke;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class MagasinDemandePieceController extends Controller
{
    // Show only demandes related to the current magasin
    public function index()
    {
        $demandes = DemandePiece::with([
            'magasin.centre',
            'atelier.centre',
            'piece'
        ])
            ->where(function ($query) {
                $query->whereNotNull('id_atelier')
                    ->whereNull('id_magasin');
            })
            ->orWhere(function ($query) {
                $query->whereNull('id_magasin')
                    ->whereNull('id_atelier');
            })
            ->orderBy('date_dp', 'desc')
            ->get();

        return Inertia::render('Magasin/DemandeAtelier/Index', [
            'demandes' => $demandes
        ]);
    }

    // Show details of a specific demande
//    public function show(DemandePiece $demande_piece)
//    {
//
//
//        return Inertia::render('Magasin/DemandeAtelier/Show', [
//            'demande' => $demande_piece->load([ 'atelier.centre', 'piece'])
//        ]);
//    }


    public function show(DemandePiece $demande_piece)
    {
        // Get the authenticated magasin user's magasin ID
        //$id_magasin = auth()->user()->magasin->id_magasin;

        // Initialize stock quantity
        $qte_stocke = 0;

        // Check if the demande has a piece associated
        if ($demande_piece->id_piece) {
            // Get the stock quantity for this piece in the current magasin
            $stock = QuantiteStocke::where('id_piece', $demande_piece->id_piece)

                ->first();

            $qte_stocke = $stock ? $stock->qte_stocke : 0;
        }

        // Load relationships and add the stock quantity
        $demande_piece->load(['piece', 'atelier.centre']);
        $demande_piece->qte_stocke = $qte_stocke;

        return Inertia::render('Magasin/DemandeAtelier/Show', [
            'demande' => $demande_piece
        ]);
    }

    // Update only the status (more restricted than DMPieceController)
    public function update(Request $request, DemandePiece $demande_piece)
    {


        $validated = $request->validate([
            'etat_dp' => 'required|string|in:En attente,Validée,Refusée,Livrée',
        ]);

        $demande_piece->update($validated);

        return redirect()->route('magasin.mes-demandes.index')
            ->with('success', 'État mis à jour avec succès');
    }

    // PDF export for a single demande
    public function exportPdf(DemandePiece $demande_piece)
    {


        $demande_piece->load(['magasin.centre', 'atelier.centre', 'piece']);
        $pdf = Pdf::loadView('magasin.demandespieces.pdf', ['demande' => $demande_piece]);
        return $pdf->download('demande_piece_' . $demande_piece->id_dp . '.pdf');
    }

    // PDF export for all demandes of the current magasin
    public function exportListPdf(Request $request)
    {
        // Start building the query
        $demandes = DemandePiece::with([
            'magasin.centre',
            'atelier.centre',
            'piece'
        ]);

        $etat = $request->input('etat'); // Get the etat from the request

        // Apply the OR WHERE conditions with etat filtering
        $demandes->where(function ($query) use ($etat) {
            $query->whereNotNull('id_atelier')
                ->whereNull('id_magasin')
                ->when($etat, function ($q) use ($etat) {
                    $q->where('etat_dp', $etat);
                });
        })
            ->orWhere(function ($query) use ($etat) {
                $query->whereNull('id_magasin')
                    ->whereNull('id_atelier')
                    ->when($etat, function ($q) use ($etat) {
                        $q->where('etat_dp', $etat);
                    });
            });

        // Apply the ordering
        $demandes->orderBy('date_dp', 'desc');

        // Get the filtered results
        $filteredDemandes = $demandes->get();

        // Pass etat to the view
        $pdf = Pdf::loadView('magasin.demandespieces.pdf-export', [
            'demandes' => $filteredDemandes,
            'etat'     => $etat,
        ]);

        return $pdf->download('mes-demandes-list-'.now()->format('Y-m-d').'.pdf');
    }

    public function livrerPiece(Request $request, $demande_piece_id)
    {
        DB::beginTransaction();
        try {


            $demande = DemandePiece::with(['piece', 'atelier.centre'])
                ->findOrFail($demande_piece_id);

            // 1. Check if already delivered
            if ($demande->etat_dp === 'Livrée') {
                return back()->with('error', 'Cette demande a déjà été livrée');
            }

            // 2. Find the stock - ensure we're checking the right magasin
            $stock = QuantiteStocke::where('id_piece', $demande->id_piece )
                ->first();

            if (!$stock) {
                return back()->with('error', 'Pièce non trouvée dans votre stock');
            }

            // 3. Check quantity
            if ($stock->qte_stocke < $demande->qte_demandep) {
                return back()->with('error', 'Stock insuffisant');
            }

            // 4. Update stock using direct SQL to avoid model issues
            QuantiteStocke::where('id_piece', $demande->id_piece)
                ->decrement('qte_stocke', $demande->qte_demandep);

            // 5. Update demande status
            $demande->etat_dp = 'Livrée';
            $demande->save();

            DB::commit();

            return redirect()
                ->route('magasin.mes-demandes.index')
                ->with('success', 'Pièce livrée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la livraison: ' . $e->getMessage());
        }
    }




}
