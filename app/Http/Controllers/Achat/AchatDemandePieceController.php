<?php
namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\DemandePiece;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;


class AchatDemandePieceController extends Controller
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

        return Inertia::render('Achat/DemandesPieces/Index', [
            'demandes' => $demandes
        ]);
    }

    public function show(DemandePiece $demande_piece)
    {
        return Inertia::render('Achat/DemandesPieces/Show', [
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

        return redirect()->route('achat.demandes-pieces.index', $demande_piece)
            ->with('success', 'État mis à jour avec succès');
    }


    public function exportPdf(DemandePiece $demande_piece)
    {
        $demande_piece->load(['magasin.centre', 'atelier.centre', 'piece']);

        $pdf = Pdf::loadView('achat.demandespieces.pdf', [
            'demande' => $demande_piece
        ]);

        return $pdf->download('demande_piece_' . $demande_piece->id_dp . '.pdf');
    }

    public function exportListPdf()
    {
        $demandes = DemandePiece::with(['magasin.centre', 'atelier.centre', 'piece'])
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

        // Debugging: Check if data exists
        if ($demandes->isEmpty()) {
            abort(404, 'No demandes found');
        }

        return Pdf::loadView('achat.demandespieces.pdf-export', [
            'demandes' => $demandes
        ])->download('demandes-list-'.now()->format('Y-m-d').'.pdf');
    }
}
