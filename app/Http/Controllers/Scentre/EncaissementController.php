<?php

namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use App\Models\Dra;
use App\Models\Encaissement;
use App\Models\Remboursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;


class EncaissementController extends Controller
{
    public function index()
    {
        $encaissements = Encaissement::with([
            'centre',
            'remboursement.dra',
        ])->latest()->get();

        return Inertia::render('Encaissements/Index', ['encaissements' => $encaissements]);
    }


    public function create()
    {
        $centres = Centre::all();

        // Only get remboursements that are not already used in encaissements
        $remboursements = Remboursement::select('remboursements.n_remb', 'dras.total_dra','dras.n_dra')
            ->join('dras', 'remboursements.n_dra', '=', 'dras.n_dra')
            ->whereDoesntHave('encaissements')
            ->get();

        // Get the authenticated user's centre
        $userCentre = Auth::user()->id_centre;

        return Inertia::render('Encaissements/Create', [
            'centres' => $centres,
            'remboursements' => $remboursements,
            'userCentre' => $userCentre,
        ]);
    }




    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_centre' => 'required|exists:centres,id_centre',
            'n_remb' => 'required|exists:remboursements,n_remb',
            'date_enc' => 'required|date',
        ]);

        // Join dras to get total_dra and n_dra for the selected remboursement
        $remboursement = Remboursement::select('remboursements.*', 'dras.total_dra', 'dras.n_dra', 'dras.etat')
            ->join('dras', 'remboursements.n_dra', '=', 'dras.n_dra')
            ->where('remboursements.n_remb', $validated['n_remb'])
            ->first();

        $validated['montant_enc'] = $remboursement->total_dra ?? 0;

        // Create the encaissement record
        $encaissement = Encaissement::create($validated);

        // Increment montant_disponible for the centre
        Centre::where('id_centre', $validated['id_centre'])
            ->increment('montant_disponible', $validated['montant_enc']);


        if ($remboursement && $remboursement->etat === 'accepte') {
            Dra::where('n_dra', $remboursement->n_dra)->update(['etat' => 'rembourse']);
        }

        return redirect()->route('encaissements.index')->with('success', 'Encaissement créé avec succès et état du DRA mis à jour.');
    }



    public function edit(Encaissement $encaissement)
    {
        $centres = Centre::all();
        $remboursements = Remboursement::all();

        return Inertia::render('Encaissements/Edit', [
            'encaissement' => $encaissement,
            'centres' => $centres,
            'remboursements' => $remboursements
        ]);
    }

    public function update(Request $request, Encaissement $encaissement)
    {
        $validated = $request->validate([
            'id_centre' => 'required|exists:centres,id_centre',
            'n_remb' => 'required|exists:remboursements,n_remb',
            'montant_enc' => 'required|integer|min:0',
            'date_enc' => 'required|date',
        ]);


        $oldMontant = $encaissement->montant_enc;
        $oldCentreId = $encaissement->id_centre;

        // Adjust montant_disponible of the old centre
        Centre::where('id_centre', $oldCentreId)
            ->decrement('montant_disponible', $oldMontant);

        // Update the encaissement
        $encaissement->update($validated);

        // Increment montant_disponible of the new centre
        Centre::where('id_centre', $validated['id_centre'])
            ->increment('montant_disponible', $validated['montant_enc']);

        return redirect()->route('encaissements.index')->with('success', 'Encaissement mis à jour avec succès.');
    }


    public function destroy(Encaissement $encaissement)
    {
        $centre = Centre::findOrFail($encaissement->id_centre);

        if ($centre->montant_disponible >= $encaissement->montant_enc) {
            $centre->decrement('montant_disponible', $encaissement->montant_enc);
        } else {
            return redirect()->route('encaissements.index')->with('error', 'Impossible de supprimer: montant disponible insuffisant.');
        }


        // Find the related remboursement
        $remboursement = Remboursement::where('n_remb', $encaissement->n_remb)->first();

        if ($remboursement) {
            // Get the associated dra and update its etat back to "accepte"
            Dra::where('n_dra', $remboursement->n_dra)
                ->where('etat', 'rembourse') // Only update if it's currently 'rembourse'
                ->update(['etat' => 'accepte']);
        }

        $encaissement->delete();

        return redirect()->route('encaissements.index')->with('success', 'Encaissement supprimé et état du DRA mis à jour.');
    }

}

