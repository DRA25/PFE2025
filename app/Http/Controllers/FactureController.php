<?php

namespace App\Http\Controllers;

use App\Models\Dra;
use App\Models\Facture;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FactureController extends Controller
{
    public function index(Dra $dra)
    {
        $factures = $dra->factures;
        return Inertia::render('Facture/Index', compact('dra', 'factures'));
    }

    public function create(Dra $dra)
    {
        return Inertia::render('Facture/Create', compact('dra'));
    }

    public function store(Request $request, Dra $dra)
    {
        $request->validate([
            'n_facture' => 'required|unique:factures,n_facture',
            'montant_facture' => 'required|integer',
            'date_facture' => 'required|date',
            'id_fourn' => 'required|integer',
        ]);

        $dra->factures()->create([
            'n_facture' => $request->n_facture,
            'montant_facture' => $request->montant_facture,
            'date_facture' => $request->date_facture,
            'id_fourn' => $request->id_fourn,
            'n_dra' => $dra->n_dra,
        ]);

        return redirect()->route('dras.factures.index', $dra->n_dra)->with('success', 'Facture created successfully.');
    }

    public function edit(Dra $dra, Facture $facture)
    {
        return Inertia::render('Facture/Edit', compact('dra', 'facture'));
    }

    public function update(Request $request, Dra $dra, Facture $facture)
    {
        $request->validate([
            'montant_facture' => 'required|integer',
            'date_facture' => 'required|date',
            'id_fourn' => 'required|integer',
        ]);

        $facture->update($request->all());

        return redirect()->route('dras.factures.index', $dra->n_dra)->with('success', 'Facture updated successfully.');
    }

    public function destroy(Dra $dra, Facture $facture)
    {
        $facture->delete();
        return redirect()->route('dras.factures.index', $dra->n_dra)->with('success', 'Facture deleted successfully.');
    }
}
