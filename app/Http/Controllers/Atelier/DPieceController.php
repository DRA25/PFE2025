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
    // Define the enum options as a constant or private property for reusability
    private const ETAT_OPTIONS = ['en attente', 'non disponible', 'livre', 'refuse'];

    public function index()
    {
        $userCentreId = Auth::user()->id_centre;


        $atelierIds = Atelier::where('id_centre', $userCentreId)
            ->pluck('id_atelier')
            ->toArray();


        $demandes = DemandePiece::with([
            'atelier:id_atelier,adresse_atelier',
            'piece:id_piece,nom_piece'
        ])
            ->whereIn('id_atelier', $atelierIds)
            // Select all columns, including 'motif'
            ->select('id_dp', 'date_dp', 'etat_dp', 'id_piece', 'qte_demandep', 'id_atelier', 'motif')
            ->orderBy('date_dp', 'desc')
            ->get();

        return Inertia::render('Atelier/DemandesPieces/Index', [
            'demandes' => $demandes,
            'etatOptions' => self::ETAT_OPTIONS, // Pass etat options to the view
        ]);
    }

    public function create()
    {
        $userCentreId = Auth::user()->id_centre;
        $ateliers = Atelier::where('id_centre', $userCentreId)
            ->select('id_atelier', 'adresse_atelier')
            ->get();
        $pieces = Piece::select('id_piece', 'nom_piece')->get();

        $defaultAtelier = $ateliers->first();

        return Inertia::render('Atelier/DemandesPieces/Create', [
            'pieces' => $pieces,
            'defaultAtelier' => $defaultAtelier,
            'etatOptions' => self::ETAT_OPTIONS, // Pass etat options to the view
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_dp' => 'required|date',
            'id_piece' => 'required|max:255',
            'qte_demandep' => 'required|integer|min:1',
            'id_magasin' => 'nullable|exists:magasins,id_magasin',
            'id_atelier' => 'nullable|exists:ateliers,id_atelier',
        ]);

        $user = Auth::user();
        if ($user->hasRole('service atelier') && !isset($validated['id_atelier'])) {
            $atelier = Atelier::where('id_centre', $user->id_centre)->firstOrFail();
            $validated['id_atelier'] = $atelier->id_atelier;
        }

        DemandePiece::create($validated);

        return redirect()->route('atelier.demandes-pieces.index')
            ->with('success', 'Demande créée avec succès');
    }

    public function edit(DemandePiece $demandePiece)
    {
        $userCentreId = Auth::user()->id_centre;

        $ateliers = Atelier::where('id_centre', $userCentreId)->get();

        if ($demandePiece->id_atelier) {
            $demandeAtelier = Atelier::find($demandePiece->id_atelier);
            if (!$demandeAtelier || $demandeAtelier->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(400, 'Demande Piece not associated with an atelier');
        }

        $pieces = Piece::select('id_piece', 'nom_piece')->get();

        return Inertia::render('Atelier/DemandesPieces/Edit', [
            'demande_piece' => $demandePiece->load(['atelier', 'piece']),
            'pieces' => $pieces,
            'etatOptions' => self::ETAT_OPTIONS, // Pass etat options to the view
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
        } else {
            abort(400, 'Demande Piece not associated with an atelier');
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
        } else {
            abort(400, 'Demande Piece not associated with an atelier');
        }

        $demandePiece->delete();

        return redirect()->route('atelier.demandes-pieces.index')
            ->with('success', 'Demande supprimée avec succès');
    }
}
