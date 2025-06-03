<?php

namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\DemandePiece;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScentreDemandePieceController extends Controller
{
    public function index()
    {
        $demandes = DemandePiece::with([
            'magasin.centre',
            'atelier.centre',
            'piece'
        ])
            ->where(function ($query) {
                $query->whereNotNull('id_magasin')
                    ->whereNull('id_atelier');
            })
            ->orWhere(function ($query) {
                $query->whereNull('id_magasin')
                    ->whereNull('id_atelier');
            })
            ->orderBy('date_dp', 'desc')
            ->get();

        return Inertia::render('Scentre/DemandesPieces/Index', [
            'demandes' => $demandes
        ]);
    }

    public function show(DemandePiece $demande_piece)
    {
        return Inertia::render('Scentre/DemandesPieces/Show', [
            'demande' => $demande_piece->load([
                'magasin.centre',
                'atelier.centre',
                'piece'
            ])
        ]);
    }


    public function update(Request $request, DemandePiece $demande_piece)
    {
        $validated = $request->validate([
            'etat_dp' => 'required|string|in:En attente,Validée,Refusée,Livrée',
        ]);

        $demande_piece->update($validated);

        return redirect()->route('scentre.demandes-pieces.index', $demande_piece)
            ->with('success', 'État mis à jour avec succès');
    }


    public function exportPdf(DemandePiece $demande_piece)
    {
        $demande_piece->load(['magasin.centre', 'atelier.centre', 'piece']);

        $pdf = Pdf::loadView('scentre.demandespieces.pdf', [
            'demande' => $demande_piece
        ]);

        return $pdf->download('demande_piece_' . $demande_piece->id_dp . '.pdf');
    }

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
            $query->whereNotNull('id_magasin')
                ->whereNull('id_atelier')
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
        $pdf = Pdf::loadView('scentre.demandespieces.pdf-export', [
            'demandes' => $filteredDemandes,
            'etat'     => $etat,
        ]);

        return $pdf->download('mes-demandes-list-'.now()->format('Y-m-d').'.pdf');
    }
}
