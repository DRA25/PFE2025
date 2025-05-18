<?php
namespace App\Http\Controllers\Magasin;

use App\Http\Controllers\Controller;
use App\Models\DemandePiece;
use Illuminate\Http\Request;
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
    public function show(DemandePiece $demande_piece)
    {


        return Inertia::render('Magasin/DemandeAtelier/Show', [
            'demande' => $demande_piece->load([ 'atelier.centre', 'piece'])
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
    public function exportListPdf()
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

        $pdf = Pdf::loadView('magasin.demandespieces.pdf-export', ['demandes' => $demandes]);
        return $pdf->download('mes-demandes-list-'.now()->format('Y-m-d').'.pdf');
    }
}
