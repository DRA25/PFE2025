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
            'atelier.centre',
            'piece'
        ])
            ->where(function ($query) {
                $query->whereNotNull('id_atelier');

            })

            ->where('etat_dp', 'non disponible')
            ->orderBy('date_dp', 'desc')
            ->get();

        $etatOptions = ['en attente', 'non disponible', 'livre', 'refuse'];

        return Inertia::render('Scentre/DemandesPieces/Index', [
            'demandes' => $demandes,
            'etatOptions' => $etatOptions,
        ]);
    }

    public function show(DemandePiece $demande_piece)
    {
        $etatOptions = ['en attente', 'non disponible', 'livre', 'refuse'];

        return Inertia::render('Scentre/DemandesPieces/Show', [
            'demande' => $demande_piece->load([
                'atelier.centre',
                'piece'
            ]),
            'etatOptions' => $etatOptions,
        ]);
    }


    public function update(Request $request, DemandePiece $demande_piece)
    {
        $validated = $request->validate([
            'etat_dp' => 'required|string|in:en attente,non disponible,livre,refuse',
        ]);

        $demande_piece->update($validated);

        return redirect()->route('scentre.demandes-pieces.index', $demande_piece)
            ->with('success', 'État mis à jour avec succès');
    }


    public function exportPdf(DemandePiece $demande_piece)
    {
        $demande_piece->load(['atelier.centre', 'piece']);

        $pdf = Pdf::loadView('scentre.demandespieces.pdf', [
            'demande' => $demande_piece
        ]);

        return $pdf->download('demande_piece_' . $demande_piece->id_dp . '.pdf');
    }


    public function exportListPdf(Request $request)
    {

        $demandes = DemandePiece::with([
            'atelier.centre',
            'piece'
        ]);

        $etat = $request->input('etat');


        $demandes->where(function ($query) use ($etat) {
            $query->whereNotNull('id_atelier')
                ->when($etat, function ($q) use ($etat) {
                    $q->where('etat_dp', $etat);
                });
            });


        $demandes->orderBy('date_dp', 'desc');


        $filteredDemandes = $demandes->get();


        $pdf = Pdf::loadView('scentre.demandespieces.pdf-export', [
            'demandes' => $filteredDemandes,
            'etat'     => $etat,
        ]);

        return $pdf->download('mes-demandes-list-'.now()->format('Y-m-d').'.pdf');
    }
}
