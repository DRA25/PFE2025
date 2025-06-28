<?php

namespace App\Http\Controllers;

use App\Models\Charge;
use App\Models\CompteGeneral;
use App\Models\CompteAnalytique;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChargeController extends Controller
{
    public function index()
    {
        $charges = Charge::with(['compteGeneral', 'compteAnalytique'])
            ->select(
                'id_charge',
                'nom_charge',
                'type_change',
                'tva',
                'compte_general_code',
                'compte_analytique_code',
            )
            ->get();

        return Inertia::render('Charges/Index', [
            'charges' => $charges,
        ]);
    }

    public function create()
    {
        $comptesGeneraux = CompteGeneral::all();
        $comptesAnalytiques = CompteAnalytique::all();

        return Inertia::render('Charges/Create', [
            'comptesGeneraux' => $comptesGeneraux,
            'comptesAnalytiques' => $comptesAnalytiques,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_charge' => 'required|integer',
            'nom_charge' => 'required|string|max:255',
            'desc_change' => 'required|string|max:255',
            'type_change' => 'required|string|max:255',
            'tva' => 'required|numeric',
            'compte_general_code' => 'required|exists:comptes_generaux,code',
            'compte_analytique_code' => 'required|exists:comptes_analytiques,code',
        ]);

        Charge::create($validated);

        return redirect()->route('scentre.charges.index')
            ->with('success', 'Charge created successfully.');
    }

    public function show(Charge $charge)
    {
        return Inertia::render('Charges/Show', [
            'charge' => $charge->load(['compteGeneral', 'compteAnalytique']),
        ]);
    }

    public function edit(Charge $charge)
    {
        $comptesGeneraux = CompteGeneral::all();
        $comptesAnalytiques = CompteAnalytique::all();

        return Inertia::render('Charges/Edit', [
            'charge' => $charge,
            'comptesGeneraux' => $comptesGeneraux,
            'comptesAnalytiques' => $comptesAnalytiques,
        ]);
    }

    public function update(Request $request, Charge $charge)
    {
        $validated = $request->validate([
            'id_charge' => 'required|integer',
            'nom_charge' => 'required|string|max:255',
            'desc_change' => 'required|string|max:255',
            'type_change' => 'required|string|max:255',
            'tva' => 'required|numeric',
            'compte_general_code' => 'required|exists:comptes_generaux,code',
            'compte_analytique_code' => 'required|exists:comptes_analytiques,code',
        ]);

        $charge->update($validated);

        return redirect()->route('scentre.charges.index')
            ->with('success', 'Charge updated successfully.');
    }

    public function destroy(Charge $charge)
    {
        $charge->delete();

        return redirect()->route('scentre.charges.index')
            ->with('success', 'Charge deleted successfully.');
    }
}
