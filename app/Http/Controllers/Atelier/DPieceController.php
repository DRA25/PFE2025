<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Models\DemandePiece;
use App\Models\Magasin;
use App\Models\Atelier;
use App\Models\Piece;
use Illuminate\Http\Request;

class DPieceController extends Controller
{
    public function index()
    {
        $demandes = DemandePiece::with([
            'magasin:id_magasin,adresse_magasin',
            'atelier:id_atelier,adresse_atelier',
            'piece:id_piece,nom_piece'
        ])->orderBy('date_dp', 'desc')->get();

        return inertia('Atelier/DemandesPieces/Index', [
            'demandes' => $demandes,
        ]);
    }

    public function create()
    {
        $magasins = Magasin::select('id_magasin', 'adresse_magasin')->get();
        $ateliers = Atelier::select('id_atelier', 'adresse_atelier')->get();
        $pieces = Piece::select('id_piece','nom_piece')->get();

        return inertia('Atelier/DemandesPieces/Create', [
            'magasins' => $magasins,
            'ateliers' => $ateliers,
            'pieces' => $pieces,
        ]);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_dp' => 'required|date',
            'etat_dp' => 'required|string|max:255',
            'id_piece' => 'required|max:255',
            'qte_demandep' => 'required|integer|min:1',
            'id_magasin' => 'nullable|required_without:id_atelier|exists:magasins,id_magasin',
            'id_atelier' => 'nullable|required_without:id_magasin|exists:ateliers,id_atelier',
        ], [
            'id_magasin.required_without' => 'Vous devez sélectionner soit un magasin soit un atelier',
            'id_atelier.required_without' => 'Vous devez sélectionner soit un magasin soit un atelier'
        ]);

        DemandePiece::create($validated);

        return redirect()->route('atelier.demandes-pieces.index')
            ->with('success', 'Demande créée avec succès');
    }

    public function edit(DemandePiece $demande_piece)  // Changed parameter name
    {
        $magasins = Magasin::select('id_magasin', 'adresse_magasin')->get();
        $ateliers = Atelier::select('id_atelier', 'adresse_atelier')->get();
        $pieces = Piece::select('id_piece','nom_piece')->get();


        return inertia('Atelier/DemandesPieces/Edit', [
            'demande_piece' => $demande_piece->load(['magasin', 'atelier']),
            'magasins' => $magasins,
            'ateliers' => $ateliers,
            'pieces' => $pieces,
        ]);
    }

    public function update(Request $request, DemandePiece $demandePiece)
    {
        $validated = $request->validate([
            'date_dp' => 'required|date',
            'etat_dp' => 'required|string|max:255',
            'id_piece' => 'required|max:255',
            'qte_demandep' => 'required|integer|min:1',
            'id_magasin' => 'nullable|required_without:id_atelier|exists:magasins,id_magasin',
            'id_atelier' => 'nullable|required_without:id_magasin|exists:ateliers,id_atelier',
        ], [
            'id_magasin.required_without' => 'Vous devez sélectionner soit un magasin soit un atelier',
            'id_atelier.required_without' => 'Vous devez sélectionner soit un magasin soit un atelier'
        ]);

        $demandePiece->update($validated);

        return redirect()->route('atelier.demandes-pieces.index')
            ->with('success', 'Demande mise à jour avec succès');
    }

    public function destroy(DemandePiece $demandePiece)
    {
        $demandePiece->delete();

        return redirect()->route('atelier.demandes-pieces.index')
            ->with('success', 'Demande supprimée avec succès');
    }
}
