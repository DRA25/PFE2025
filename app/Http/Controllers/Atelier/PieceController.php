<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Models\Piece;
use App\Models\CompteGeneral;
use App\Models\CompteAnalytique;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PieceController extends Controller
{
    public function index()
    {
        // Eager load or join compte general and analytique info for each piece
        // Assuming your Piece model has relationships or you can do a join query

        // Example if Piece has relationships (adjust relation names as needed):
        $pieces = Piece::where('id_centre', auth()->user()->id_centre)
            ->with(['compteGeneral:code,libelle', 'compteAnalytique:code,libelle'])
            ->get()
            ->map(function ($piece) {
                return [
                    'id' => $piece->id,
                    'id_piece' => $piece->id_piece,
                    'nom_piece' => $piece->nom_piece,
                    'prix_piece' => $piece->prix_piece,
                    'marque_piece' => $piece->marque_piece,
                    'ref_piece' => $piece->ref_piece,
                    'tva' => $piece->tva,
                    'compte_general' => $piece->compteGeneral ? $piece->compteGeneral->code : null ,
                    'compte_analytique' => $piece->compteAnalytique ? $piece->compteAnalytique->code : null,
                    'created_at' => $piece->created_at,
                    'updated_at' => $piece->updated_at,
                ];
            });

        $view = auth()->user()->hasRole('service magasin')
            ? 'Magasin/Piece/Index'
            : 'Atelier/Piece/Index';

        return Inertia::render($view, [
            'pieces' => $pieces,
        ]);
    }


    public function create()
    {
        $compteGenerals = CompteGeneral::all(['code', 'libelle']); // adjust columns as needed
        $compteAnalytiques = CompteAnalytique::all(['code', 'libelle']);

        if (auth()->user()->hasRole('service magasin')) {
            return Inertia::render('Magasin/Piece/Create', [
                'compteGenerals' => $compteGenerals,
                'compteAnalytiques' => $compteAnalytiques,
            ]);
        }

        return Inertia::render('Atelier/Piece/Create', [
            'compteGenerals' => $compteGenerals,
            'compteAnalytiques' => $compteAnalytiques,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_piece' => 'required|integer|unique:pieces,id_piece',
            'nom_piece' => 'required|string|max:255',
            'prix_piece' => 'required|numeric|min:0',
            'tva' => 'required|numeric|min:0|max:100',
            'marque_piece' => 'required|string|max:255',
            'ref_piece' => 'required|string|max:255',
            'compte_general_code' => 'required|string|exists:comptes_generaux,code',
            'compte_analytique_code' => 'required|string|exists:comptes_analytiques,code',
        ]);

        $validated['id_centre'] = auth()->user()->id_centre;

        Piece::create($validated);

        $route = auth()->user()->hasRole('service magasin')
            ? 'magasin.pieces.index'
            : 'atelier.pieces.index';

        return redirect()->route($route)
            ->with('success', 'Pièce créée avec succès.');
    }

    public function edit(Piece $piece)
    {
        if ($piece->id_centre !== auth()->user()->id_centre) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier cette pièce.');
        }

        $view = auth()->user()->hasRole('service magasin')
            ? 'Magasin/Piece/Edit'
            : 'Atelier/Piece/Edit';

        $comptesGeneraux = CompteGeneral::all();
        $comptesAnalytiques = CompteAnalytique::all();

        return Inertia::render($view, [
            'piece' => $piece,
            'comptesGeneraux' => $comptesGeneraux,
            'comptesAnalytiques' => $comptesAnalytiques,
        ]);
    }


    public function update(Request $request, Piece $piece)
    {
        if ($piece->id_centre !== auth()->user()->id_centre) {
            abort(403, 'Vous n\'êtes pas autorisé à mettre à jour cette pièce.');
        }

        $validated = $request->validate([
            // Usually id_piece is not updated, so no need to validate it here.
            'nom_piece' => 'required|string|max:255',
            'prix_piece' => 'required|numeric|min:0',
            'tva' => 'required|numeric|min:0|max:100', // keep if you use tva
            'marque_piece' => 'required|string|max:255',
            'ref_piece' => 'required|string|max:255',
            'compte_general_code' => 'required|string|exists:comptes_generaux,code',
            'compte_analytique_code' => 'required|string|exists:comptes_analytiques,code',
        ]);

        $piece->update($validated);

        $route = auth()->user()->hasRole('service magasin')
            ? 'magasin.pieces.index'
            : 'atelier.pieces.index';

        return redirect()->route($route)
            ->with('success', 'Pièce mise à jour avec succès.');
    }

    public function destroy($id_piece)
    {
        try {
            $piece = Piece::where('id_piece', $id_piece)
                ->where('id_centre', auth()->user()->id_centre)
                ->firstOrFail();

            $piece->delete();

            $route = auth()->user()->hasRole('service magasin')
                ? 'magasin.pieces.index'
                : 'atelier.pieces.index';

            return redirect()->route($route)
                ->with('success', 'Pièce supprimée avec succès');

        } catch (\Exception $e) {
            $route = auth()->user()->hasRole('service magasin')
                ? 'magasin.pieces.index'
                : 'atelier.pieces.index';

            return redirect()->route($route)
                ->with('error', 'Erreur lors de la suppression de la pièce');
        }
    }
}
