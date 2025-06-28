<?php

namespace App\Http\Controllers;

use App\Models\Prestation;
use App\Models\CompteGeneral;
use App\Models\CompteAnalytique;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PrestationController extends Controller
{
    public function index()
    {
        $prestations = Prestation::with(['compteGeneral', 'compteAnalytique'])
            ->select(
                'id_prest',
                'nom_prest',
                'date_prest',
                'tva',
                'compte_general_code',
                'compte_analytique_code',
                'desc_prest' // Added description since it was missing in original select
            )
            ->orderBy('date_prest', 'desc')
            ->get();

        return Inertia::render('Prestations/Index', [
            'prestations' => $prestations,
        ]);
    }

    public function create()
    {
        $comptesGeneraux = CompteGeneral::all();
        $comptesAnalytiques = CompteAnalytique::all();

        return Inertia::render('Prestations/Create', [
            'comptesGeneraux' => $comptesGeneraux,
            'comptesAnalytiques' => $comptesAnalytiques,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_prest' => 'required|integer',
            'nom_prest' => 'required|string|max:255',
            'desc_prest' => 'required|string|max:255',
            'date_prest' => 'required|date',
            'tva' => 'required|numeric',
            'compte_general_code' => 'required|exists:comptes_generaux,code',
            'compte_analytique_code' => 'required|exists:comptes_analytiques,code',
            // Removed prix_prest validation as it's no longer part of Prestation
        ]);

        Prestation::create($validated);

        return redirect()->route('scentre.prestations.index')
            ->with('success', 'Prestation created successfully.');
    }

    public function show(Prestation $prestation)
    {
        return Inertia::render('Prestations/Show', [
            'prestation' => $prestation->load(['compteGeneral', 'compteAnalytique']),
        ]);
    }

    public function edit(Prestation $prestation)
    {
        $comptesGeneraux = CompteGeneral::all();
        $comptesAnalytiques = CompteAnalytique::all();

        return Inertia::render('Prestations/Edit', [
            'prestation' => $prestation,
            'comptesGeneraux' => $comptesGeneraux,
            'comptesAnalytiques' => $comptesAnalytiques,
        ]);
    }

    public function update(Request $request, Prestation $prestation)
    {
        $validated = $request->validate([
            'id_prest' => 'required|integer',
            'nom_prest' => 'required|string|max:255',
            'desc_prest' => 'required|string|max:255',
            'date_prest' => 'required|date',
            'tva' => 'required|numeric',
            'compte_general_code' => 'required|exists:comptes_generaux,code',
            'compte_analytique_code' => 'required|exists:comptes_analytiques,code',
            // Removed prix_prest validation as it's no longer part of Prestation
        ]);

        $prestation->update($validated);

        return redirect()->route('scentre.prestations.index')
            ->with('success', 'Prestation updated successfully.');
    }

    public function destroy(Prestation $prestation)
    {
        $prestation->delete();

        return redirect()->route('scentre.prestations.index')
            ->with('success', 'Prestation deleted successfully.');
    }
}
