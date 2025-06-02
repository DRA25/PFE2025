<?php

namespace App\Http\Controllers\Magasin;

use App\Http\Controllers\Controller;
use App\Models\QuantiteStocke;
use App\Models\Magasin;
use App\Models\Piece;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QuantiteStockeController extends Controller
{
    public function index()
    {
        // Get the user's center ID
        $centreId = auth()->user()->id_centre;

        // Get all QuantiteStocke records where the magasin belongs to the user's center
        $quantites = QuantiteStocke::with(['magasin', 'piece'])
            ->whereHas('magasin', function($query) use ($centreId) {
                $query->where('id_centre', $centreId);
            })
            ->get();

        return Inertia::render('Magasin/QuantiteStocke/Index', [
            'quantites' => $quantites,
        ]);
    }

    public function create()
    {
        $centreId = auth()->user()->id_centre;
        $magasin = Magasin::where('id_centre', $centreId)->firstOrFail();

        // Get all pieces
        $allPieces = Piece::all();

        // Get pieces already assigned to this magasin
        $assignedPieceIds = QuantiteStocke::where('id_magasin', $magasin->id_magasin)
            ->pluck('id_piece')
            ->toArray();

        // Filter out assigned pieces
        $availablePieces = $allPieces->reject(function ($piece) use ($assignedPieceIds) {
            return in_array($piece->id_piece, $assignedPieceIds);
        });

        return Inertia::render('Magasin/QuantiteStocke/Create', [
            'defaultMagasin' => $magasin,
            'pieces' => $availablePieces->values(), // Reset array keys
        ]);
    }

    public function store(Request $request)
    {
        $centreId = auth()->user()->id_centre;
        $magasin = Magasin::where('id_centre', $centreId)->firstOrFail();

        $request->validate([
            'id_piece' => 'required|integer|exists:pieces,id_piece',
            'qte_stocke' => 'required|integer|min:0',
        ]);

        // Check if the combination already exists
        $existing = QuantiteStocke::where('id_magasin', $magasin->id_magasin)
            ->where('id_piece', $request->id_piece)
            ->first();

        if ($existing) {
            return redirect()
                ->back()
                ->with('error', 'Cette combinaison magasin/pièce existe déjà. Veuillez la modifier plutôt que de créer un nouveau.');
        }

        QuantiteStocke::create([
            'id_magasin' => $magasin->id_magasin,
            'id_piece' => $request->id_piece,
            'qte_stocke' => $request->qte_stocke
        ]);

        return redirect()
            ->route('magasin.quantites.index')
            ->with('success', 'Quantité stockée créée avec succès.');
    }

    public function edit($id_magasin, $id_piece)
    {
        $centreId = auth()->user()->id_centre;
        $magasin = Magasin::where('id_centre', $centreId)->firstOrFail();

        $quantite = QuantiteStocke::where('id_magasin', $magasin->id_magasin)
            ->where('id_piece', $id_piece)
            ->firstOrFail();

        // Freshly load relationships if needed
        $quantite->load(['magasin', 'piece']);

        return Inertia::render('Magasin/QuantiteStocke/Edit', [
            'quantite' => $quantite,
        ]);
    }

    public function update(Request $request, $id_magasin, $id_piece)
    {
        $centreId = auth()->user()->id_centre;
        $magasin = Magasin::where('id_centre', $centreId)->firstOrFail();

        $request->validate([
            'qte_stocke' => 'required|integer|min:0',
        ]);

        $quantite = QuantiteStocke::where('id_magasin', $magasin->id_magasin)
            ->where('id_piece', $id_piece)
            ->firstOrFail();

        $quantite->update([
            'qte_stocke' => $request->qte_stocke
        ]);

        return redirect()
            ->route('magasin.quantites.index')
            ->with('success', 'Quantité stockée mise à jour avec succès.');
    }

    public function destroy($id_magasin, $id_piece)
    {
        $centreId = auth()->user()->id_centre;
        $magasin = Magasin::where('id_centre', $centreId)->firstOrFail();

        $quantite = QuantiteStocke::where('id_magasin', $magasin->id_magasin)
            ->where('id_piece', $id_piece)
            ->firstOrFail();

        $quantite->delete();

        return redirect()
            ->route('magasin.quantites.index')
            ->with('success', 'Quantité stockée supprimée avec succès.');
    }
}
