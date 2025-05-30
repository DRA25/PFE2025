<?php

namespace App\Http\Controllers\Magasin;

use App\Http\Controllers\Controller;
use App\Models\DemandePiece;
use App\Models\Magasin;
use App\Models\Atelier;
use App\Models\Piece;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DMPieceController extends Controller
{
    public function index()
    {
        $userCentreId = Auth::user()->id_centre;

        // Get the magasins belonging to the user's center
        $magasinIds = Magasin::where('id_centre', $userCentreId)
            ->pluck('id_magasin')
            ->toArray();

        // Get the demandes pieces associated with those magasins
        $demandes = DemandePiece::with([
            'magasin:id_magasin,adresse_magasin',
            'atelier:id_atelier,adresse_atelier',
            'piece:id_piece,nom_piece'
        ])
            ->whereIn('id_magasin', $magasinIds)
            ->orderBy('date_dp', 'desc')
            ->get();

        return Inertia::render('Magasin/DemandesPieces/Index', [
            'demandes' => $demandes,
        ]);
    }

    public function create()
    {
        $userCentreId = Auth::user()->id_centre;
        $magasins = Magasin::where('id_centre', $userCentreId)
            ->select('id_magasin', 'adresse_magasin')
            ->get();
        $ateliers = Atelier::select('id_atelier', 'adresse_atelier')->get();
        $pieces = Piece::select('id_piece', 'nom_piece')->get();

        $defaultMagasin = $magasins->first();

        return Inertia::render('Magasin/DemandesPieces/Create', [
            'ateliers' => $ateliers,
            'pieces' => $pieces,
            'defaultMagasin' => $defaultMagasin,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_dp' => 'required|date',
            'etat_dp' => 'required|string|max:255',
            'id_piece' => 'required|max:255',
            'qte_demandep' => 'required|integer|min:1',
            'id_magasin' => 'nullable|exists:magasins,id_magasin',
            'id_atelier' => 'nullable|exists:ateliers,id_atelier',
        ], [
            'id_magasin.required_without' => 'Vous devez sélectionner un magasin si vous êtes du service magasin.',
            'id_atelier.required_without' => 'Vous devez sélectionner un atelier si vous êtes du service atelier.',
        ]);

        $user = Auth::user();
        if ($user->hasRole('service magasin') && !isset($validated['id_magasin'])) {
            $magasin = Magasin::where('id_centre', $user->id_centre)->firstOrFail();
            $validated['id_magasin'] = $magasin->id_magasin;
        } elseif ($user->hasRole('service atelier') && !isset($validated['id_atelier'])) {
            $atelier = Atelier::where('id_centre', $user->id_centre)->firstOrFail();
            $validated['id_atelier'] = $atelier->id_atelier;
        }

        DemandePiece::create($validated);

        return redirect()->route('magasin.demandes-pieces.index')
            ->with('success', 'Demande créée avec succès');
    }

    public function edit(DemandePiece $demandePiece)
    {
        $userCentreId = Auth::user()->id_centre;

        // Fetch magasins for the user's center for authorization check
        $magasins = Magasin::where('id_centre', $userCentreId)->get();

        // Ensure the demande_piece belongs to a magasin in the user's center
        if ($demandePiece->id_magasin) {
            $demandeMagasin = Magasin::find($demandePiece->id_magasin);
            if (!$demandeMagasin || $demandeMagasin->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(400, 'Demande Piece not associated with a magasin');
        }

        $pieces = Piece::select('id_piece', 'nom_piece')->get();

        return Inertia::render('Magasin/DemandesPieces/Edit', [
            'demande_piece' => $demandePiece->load(['magasin', 'atelier', 'piece']),
            'pieces' => $pieces,
        ]);
    }

    public function update(Request $request, DemandePiece $demandePiece)
    {
        $userCentreId = Auth::user()->id_centre;

        $validated = $request->validate([
            'date_dp' => 'required|date',
            'etat_dp' => 'required|string|max:255',
            'id_piece' => 'required|max:255',
            'qte_demandep' => 'required|integer|min:1',
        ]);

        // Authorization: Ensure the user can only update demandes from their center's magasins
        if ($demandePiece->id_magasin) {
            $magasin = Magasin::find($demandePiece->id_magasin);
            if (!$magasin || $magasin->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(400, 'Demande Piece not associated with a magasin');
        }

        $demandePiece->update($validated);

        return redirect()->route('magasin.demandes-pieces.index')
            ->with('success', 'Demande mise à jour avec succès');
    }

    public function destroy(DemandePiece $demandePiece)
    {
        $userCentreId = Auth::user()->id_centre;

        if ($demandePiece->id_magasin) {
            $magasin = Magasin::find($demandePiece->id_magasin);
            if (!$magasin || $magasin->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(400, 'Demande Piece not associated with a magasin');
        }

        $demandePiece->delete();

        return redirect()->route('magasin.demandes-pieces.index')
            ->with('success', 'Demande supprimée avec succès');
    }
}
