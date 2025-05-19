<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Models\Piece;
use Inertia\Inertia;
use Illuminate\Http\Request;

class PieceController extends Controller
{
    public function index()
    {
        if (auth()->user()->hasRole('service magasin')) {
            return Inertia::render('Magasin/Piece/Index', [
                'pieces' => Piece::all(),
            ]);
        }

        return Inertia::render('Atelier/Piece/Index', [
            'pieces' => Piece::all(),
        ]);
    }

    public function create()
    {
        if (auth()->user()->hasRole('service magasin')) {
            // For magasin service, show Magasin Create page (adjust path as needed)
            return Inertia::render('Magasin/Piece/Create');
        }

// Otherwise, show Atelier Create page
        return Inertia::render('Atelier/Piece/Create');

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_piece' => 'required|integer|unique:pieces,id_piece',
            'nom_piece' => 'required|string',
            'prix_piece' => 'required|numeric',
            'marque_piece' => 'required|string',
            'ref_piece' => 'required|string',
        ]);

        Piece::create($validated);

        if (auth()->user()->hasRole('service magasin')) {
            return redirect()->route('magasin.pieces.index')
                ->with('success', 'Pièce créée avec succès.');
        }

        return redirect()->route('atelier.pieces.index')
            ->with('success', 'Pièce créée avec succès.');

    }

    public function edit(Piece $piece)
    {

        if (auth()->user()->hasRole('service magasin')) {
            return Inertia::render('Magasin/Piece/Edit', [
                'piece' => $piece,
            ]);
        }

        return Inertia::render('Atelier/Piece/Edit', [
            'piece' => $piece,
        ]);

    }

    public function update(Request $request, Piece $piece)
    {
        $validated = $request->validate([
            'id_piece' => 'required|integer|unique:pieces,id_piece,'.$piece->id_piece.',id_piece',
            'nom_piece' => 'required|string|max:255',
            'prix_piece' => 'required|numeric|min:0',
            'marque_piece' => 'required|string|max:255',
            'ref_piece' => 'required|string|max:255',
        ]);

        $piece->update($validated);

        if (auth()->user()->hasRole('service magasin')) {
            return redirect()->route('magasin.pieces.index')->with('success', 'Pièce mise à jour avec succès.');
        }

        return redirect()->route('atelier.pieces.index')->with('success', 'Pièce mise à jour avec succès.');

    }

    public function destroy($id_piece)
    {
        try {
            // Find the piece by id_piece (not the default id)
            $piece = Piece::where('id_piece', $id_piece)->firstOrFail();

            $piece->delete();

            return redirect()->route('atelier.pieces.index')
                ->with('success', 'Pièce supprimée avec succès');

        } catch (\Exception $e) {
            if (auth()->user()->hasRole('service magasin')) {
                return redirect()->route('magasin.pieces.index')
                    ->with('error', 'Erreur lors de la suppression de la pièce');
            }

            return redirect()->route('atelier.pieces.index')
                ->with('error', 'Erreur lors de la suppression de la pièce');

        }
    }
}
