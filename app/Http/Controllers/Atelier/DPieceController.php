<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Models\DemandePiece;
use App\Models\Magasin;
use App\Models\Atelier;
use App\Models\Piece;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DPieceController extends Controller
{
    public function index()
    {
        $userCentreId = Auth::user()->id_centre;

        // Get the ateliers belonging to the user's center
        $atelierIds = Atelier::where('id_centre', $userCentreId)
            ->pluck('id_atelier')
            ->toArray();

        // Get the demandes pieces associated with those ateliers
        $demandes = DemandePiece::with([
            'magasin:id_magasin,adresse_magasin',
            'atelier:id_atelier,adresse_atelier',
            'piece:id_piece,nom_piece'
        ])
            ->whereIn('id_atelier', $atelierIds)
            ->orderBy('date_dp', 'desc')
            ->get();

        return Inertia::render('Atelier/DemandesPieces/Index', [
            'demandes' => $demandes,
        ]);
    }

    public function create()
    {
        $userCentreId = Auth::user()->id_centre;
        $ateliers = Atelier::where('id_centre', $userCentreId)
            ->select('id_atelier', 'adresse_atelier')
            ->get();
        $magasins = Magasin::select('id_magasin', 'adresse_magasin')->get();
        $pieces = Piece::select('id_piece', 'nom_piece')->get();

        $defaultAtelier = $ateliers->first();

        return Inertia::render('Atelier/DemandesPieces/Create', [
            'magasins' => $magasins,
            'pieces' => $pieces,
            'defaultAtelier' => $defaultAtelier,
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
        if ($user->hasRole('service atelier') && !isset($validated['id_atelier'])) {
            $atelier = Atelier::where('id_centre', $user->id_centre)->firstOrFail();
            $validated['id_atelier'] = $atelier->id_atelier;
        } elseif ($user->hasRole('service magasin') && !isset($validated['id_magasin'])) {
            $magasin = Magasin::where('id_centre', $user->id_centre)->firstOrFail();
            $validated['id_magasin'] = $magasin->id_magasin;
        }

        DemandePiece::create($validated);

        return redirect()->route('atelier.demandes-pieces.index')
            ->with('success', 'Demande créée avec succès');
    }

    public function edit(DemandePiece $demandePiece)
    {
        $userCentreId = Auth::user()->id_centre;

        // Fetch ateliers for the user's center.  This is needed for the authorization check.
        $ateliers = Atelier::where('id_centre', $userCentreId)->get();

        // Ensure the demande_piece belongs to an atelier in the user's center.
        if ($demandePiece->id_atelier) {
            $demandeAtelier = Atelier::find($demandePiece->id_atelier); //Efficient, loads only if id_atelier is set
            if (!$demandeAtelier || $demandeAtelier->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }
        }
        elseif ($demandePiece->id_magasin){
            //No check needed, Magasins do not belong to a center
        }
        else{
            abort(400, 'Demande Piece not associated with an atelier or magasin');
        }

        $pieces = Piece::select('id_piece', 'nom_piece')->get();

        return Inertia::render('Atelier/DemandesPieces/Edit', [
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

        // Authorization: Ensure the user can only update demandes from their center's ateliers.
        if ($demandePiece->id_atelier) {
            $atelier = Atelier::find($demandePiece->id_atelier);
            if (!$atelier || $atelier->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }
        }
        elseif($demandePiece->id_magasin){
            //  No check needed for magasin
        }
        else{
            abort(400, 'Demande Piece not associated with an atelier or magasin');
        }

        $demandePiece->update($validated);

        return redirect()->route('atelier.demandes-pieces.index')
            ->with('success', 'Demande mise à jour avec succès');
    }

    public function destroy(DemandePiece $demandePiece)
    {
        $userCentreId = Auth::user()->id_centre;

        if ($demandePiece->id_atelier) {
            $atelier = Atelier::find($demandePiece->id_atelier);
            if (!$atelier || $atelier->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }
        }
        elseif($demandePiece->id_magasin){
            //  No check needed for magasin
        }
        else{
            abort(400, 'Demande Piece not associated with an atelier or magasin');
        }

        $demandePiece->delete();

        return redirect()->route('atelier.demandes-pieces.index')
            ->with('success', 'Demande supprimée avec succès');
    }
}
