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
                'pieces' => Piece::where('id_centre', auth()->user()->id_centre)->get(),
            ]);
        }

        return Inertia::render('Atelier/Piece/Index', [
            'pieces' => Piece::where('id_centre', auth()->user()->id_centre)->get(),
        ]);
    }

    public function create()
    {
        if (auth()->user()->hasRole('service magasin')) {
            return Inertia::render('Magasin/Piece/Create');
        }

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

        // Add the user's centre to the validated data
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

        return Inertia::render($view, [
            'piece' => $piece,
        ]);
    }

    public function update(Request $request, Piece $piece)
    {
        if ($piece->id_centre !== auth()->user()->id_centre) {
            abort(403, 'Vous n\'êtes pas autorisé à mettre à jour cette pièce.');
        }

        $validated = $request->validate([
            'id_piece' => 'required|integer|unique:pieces,id_piece,' . $piece->id_piece . ',id_piece',
            'nom_piece' => 'required|string|max:255',
            'prix_piece' => 'required|numeric|min:0',
            'marque_piece' => 'required|string|max:255',
            'ref_piece' => 'required|string|max:255',
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
