<?php
namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\DemandePiece;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AchatDemandePieceController extends Controller
{
    public function index()
    {
        $demandes = DemandePiece::with(['magasin', 'atelier'])
            ->orderBy('date_dp', 'desc')
            ->get();

        return Inertia::render('Achat/DemandesPieces/Index', [
            'demandes' => $demandes
        ]);
    }

    public function show(DemandePiece $demande_piece)
    {
        return Inertia::render('Achat/DemandesPieces/Show', [
            'demande' => $demande_piece->load(['magasin', 'atelier'])
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
}
