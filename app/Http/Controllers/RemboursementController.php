<?php

namespace App\Http\Controllers;

use App\Models\Dra;
use App\Models\Remboursement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Centre;
use Illuminate\Validation\Rule;


class RemboursementController extends Controller
{
    public function index()
    {
        $remboursements = Remboursement::with('dra.centre')->get()->map(function ($remboursement) {
            $dra = $remboursement->dra;
            $centre = $dra?->centre;

            $seuil_centre = $centre?->seuil_centre ?? 0;
            $total_dra = $dra?->total_dra ?? 0;

            return [
                'n_remb' => $remboursement->n_remb,
                'date_remb' => $remboursement->date_remb,
                'method_remb' => $remboursement->method_remb,
                'n_dra' => $dra?->n_dra,
                'id_centre' => $centre?->id_centre ?? null, // ← added
                'seuil_centre' => $seuil_centre,
                'total_dra' => $total_dra,
                'montant_rembourse' => $total_dra,
            ];
        });

        return Inertia::render('Paiment/Remboursements/Index', [
            'remboursements' => $remboursements
        ]);
    }

    public function create()
    {
        $dras = Dra::where('etat', 'accepte')
            ->doesntHave('remboursement')
            ->get(['n_dra']);

        return Inertia::render('Paiment/Remboursements/Create', [
            'dras' => $dras,
        ]);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_remb' => 'required|date',
            'method_remb' => 'required|in:espece,cheque',
            'n_dra' => [
                'required',
                'exists:dras,n_dra',
                Rule::unique('remboursements', 'n_dra'), // prevents duplicate remboursement
            ],
        ]);

        Remboursement::create($validated);

        return redirect()->route('paiment.remboursements.index')->with('success', 'Remboursement créé avec succès.');
    }


    public function edit($id)
    {
        $remboursement = Remboursement::findOrFail($id);
        $dras = Dra::all(['n_dra']); // Same as in create
        return Inertia::render('Paiment/Remboursements/Edit', [
            'remboursement' => $remboursement,
            'dras' => $dras,
        ]);
    }


    public function update(Request $request, Remboursement $remboursement)
    {
        $request->validate([
            'date_remb' => 'required|date',
            'method_remb' => 'required|in:espece,cheque',
            'n_dra' => 'required|exists:dras,n_dra',
        ]);

        $remboursement->update($request->all());

        return redirect()->route('paiment.remboursements.index')->with('success', 'Remboursement modifié.');
    }

    public function destroy(Remboursement $remboursement)
    {
        $remboursement->delete();

        return redirect()->route('paiment.remboursements.index')->with('success', 'Remboursement supprimé.');
    }

}
