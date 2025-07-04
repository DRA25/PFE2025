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

    public function index()
    {
        $demandes = DemandePiece::with([
            'magasin.centre',
            'atelier.centre',
            'piece'
        ])
            ->where(function ($query) {
                $query->whereNotNull('id_atelier');
            })
            ->where('etat_dp', 'en attente')
            ->orderBy('date_dp', 'desc')
            ->get();


        $etatOptions = ['en attente', 'non disponible', 'livre', 'refuse'];

        return Inertia::render('Magasin/DemandeAtelier/Index', [
            'demandes' => $demandes,
            'etatOptions' => $etatOptions,
        ]);
    }


    public function show(DemandePiece $demande_piece)
    {

        $qte_stocke = 0;


        if ($demande_piece->id_piece) {

            $stock = QuantiteStocke::where('id_piece', $demande_piece->id_piece)

                ->first();

            $qte_stocke = $stock ? $stock->qte_stocke : 0;
        }


        // Ensure 'motif' is loaded with the demande_piece if it exists in the database
        $demande_piece->load(['piece', 'atelier.centre']);
        $demande_piece->qte_stocke = $qte_stocke;


        $etatOptions = ['en attente', 'non disponible', 'livre', 'refuse'];

        return Inertia::render('Magasin/DemandeAtelier/Show', [
            'demande' => $demande_piece,
            'etatOptions' => $etatOptions,
        ]);
    }


    public function update(Request $request, DemandePiece $demande_piece)
    {
        $validated = $request->validate([
            'etat_dp' => 'required|string|in:en attente,non disponible,livre,refuse',
            // Add motif validation: required if etat_dp is 'refuse', otherwise nullable
            'motif' => 'nullable|string|max:500|required_if:etat_dp,refuse',
        ]);

        // Update the demande_piece with the validated data, including motif
        $demande_piece->update($validated);

        return redirect()->route('magasin.mes-demandes.index')
            ->with('success', 'État mis à jour avec succès');
    }


    public function exportPdf(DemandePiece $demande_piece)
    {


        $demande_piece->load(['atelier.centre', 'piece']);
        $pdf = Pdf::loadView('magasin.demandespieces.pdf', ['demande' => $demande_piece]);
        return $pdf->download('demande_piece_' . $demande_piece->id_dp . '.pdf');
    }


    public function exportListPdf(Request $request)
    {
        // Start building the query
        $demandes = DemandePiece::with([
            'atelier.centre',
            'piece'
        ]);

        // Filter to only include demands where etat_dp is 'en attente'
        $demandes->where('etat_dp', 'en attente');

        $demandes->where(function ($query) {
            $query->whereNotNull('id_atelier');
        });

        $demandes->orderBy('date_dp', 'desc');

        $filteredDemandes = $demandes->get();


        $pdf = Pdf::loadView('magasin.demandespieces.pdf-export', [
            'demandes' => $filteredDemandes,
            'etat'     => 'en attente',
        ]);

        return $pdf->download('mes-demandes-list-en-attente-'.now()->format('Y-m-d').'.pdf');
    }

    public function livrerPiece(Request $request, $demande_piece_id)
    {
        DB::beginTransaction();
        try {


            $demande = DemandePiece::with(['piece', 'atelier.centre'])
                ->findOrFail($demande_piece_id);


            if ($demande->etat_dp === 'livre') {
                return back()->with('error', 'Cette demande a déjà été livrée');
            }


            $stock = QuantiteStocke::where('id_piece', $demande->id_piece )
                ->first();

            if (!$stock) {
                return back()->with('error', 'Pièce non trouvée dans votre stock');
            }


            if ($stock->qte_stocke < $demande->qte_demandep) {
                return back()->with('error', 'Stock insuffisant');
            }


            QuantiteStocke::where('id_piece', $demande->id_piece)
                ->decrement('qte_stocke', $demande->qte_demandep);


            $demande->etat_dp = 'livre';
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
