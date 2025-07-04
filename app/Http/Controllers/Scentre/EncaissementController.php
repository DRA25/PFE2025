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
        $userCentre = Auth::user()->id_centre;

        $encaissements = Encaissement::with([
            'centre',
            'remboursement.dra',
        ])
            ->where('id_centre', $userCentre) // Filter by user's centre
            ->latest()
            ->get();

        return Inertia::render('Encaissements/Index', ['encaissements' => $encaissements]);
    }

    public function create()
    {
        $centres = Centre::all();
        $userCentre = Auth::user()->id_centre;

        $remboursements = Remboursement::select('remboursements.n_remb', 'dras.total_dra', 'dras.n_dra')
            ->join('dras', 'remboursements.n_dra', '=', 'dras.n_dra')
            ->where('dras.id_centre', $userCentre) // Filter by user's centre
            ->whereDoesntHave('encaissements')
            ->get();

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
            'n_remb' => 'required|exists:remboursements,n_remb|unique:encaissements,n_remb,NULL,id,id_centre,'.$request->id_centre,
            'date_enc' => 'required|date',
        ]);

        $remboursement = Remboursement::select('remboursements.*', 'dras.total_dra', 'dras.n_dra', 'dras.etat')
            ->join('dras', 'remboursements.n_dra', '=', 'dras.n_dra')
            ->where('remboursements.n_remb', $validated['n_remb'])
            ->first();

        $validated['montant_enc'] = $remboursement->total_dra ?? 0.00;

        $encaissement = Encaissement::create($validated);

        Centre::where('id_centre', $validated['id_centre'])
            ->increment('montant_disponible', $validated['montant_enc']);

        if ($remboursement && $remboursement->etat === 'accepte') {
            Dra::where('n_dra', $remboursement->n_dra)->update(['etat' => 'rembourse']);
        }

        return redirect()->route('encaissements.index')->with('success', 'Encaissement créé avec succès et état du DRA mis à jour.');
    }

    public function edit($id_centre, $n_remb)
    {
        $encaissement = Encaissement::where('id_centre', $id_centre)
            ->where('n_remb', $n_remb)
            ->firstOrFail();

        $centres = Centre::all();
        $remboursements = Remboursement::all();

        return Inertia::render('Encaissements/Edit', [
            'encaissement' => $encaissement,
            'centres' => $centres,
            'remboursements' => $remboursements
        ]);
    }

    public function update(Request $request, $id_centre, $n_remb)
    {
        $validated = $request->validate([
            'id_centre' => 'required|exists:centres,id_centre',
            'n_remb' => 'required|exists:remboursements,n_remb',
            'montant_enc' => 'required|numeric|min:0',
            'date_enc' => 'required|date',
        ]);

        $encaissement = Encaissement::where('id_centre', $id_centre)
            ->where('n_remb', $n_remb)
            ->firstOrFail();

        $oldMontant = $encaissement->montant_enc;
        $oldCentreId = $encaissement->id_centre;

        Centre::where('id_centre', $oldCentreId)
            ->decrement('montant_disponible', $oldMontant);

        // Delete the old record and create a new one if the keys changed
        if ($id_centre != $validated['id_centre'] || $n_remb != $validated['n_remb']) {
            $encaissement->delete();
            $encaissement = Encaissement::create($validated);
        } else {
            $encaissement->update($validated);
        }

        Centre::where('id_centre', $validated['id_centre'])
            ->increment('montant_disponible', $validated['montant_enc']);

        return redirect()->route('encaissements.index')->with('success', 'Encaissement mis à jour avec succès.');
    }

    public function destroy($id_centre, $n_remb)
    {
        $encaissement = Encaissement::where('id_centre', $id_centre)
            ->where('n_remb', $n_remb)
            ->firstOrFail();

        $centre = Centre::findOrFail($encaissement->id_centre);

        if ($centre->montant_disponible >= $encaissement->montant_enc) {
            $centre->decrement('montant_disponible', $encaissement->montant_enc);
        } else {
            return redirect()->route('encaissements.index')->with('error', 'Impossible de supprimer: montant disponible insuffisant.');
        }

        $remboursement = Remboursement::where('n_remb', $encaissement->n_remb)->first();

        if ($remboursement) {
            Dra::where('n_dra', $remboursement->n_dra)
                ->where('etat', 'rembourse')
                ->update(['etat' => 'accepte']);
        }

        $encaissement->delete();

        return redirect()->route('encaissements.index')->with('success', 'Encaissement supprimé et état du DRA mis à jour.');
    }
}
