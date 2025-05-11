<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FournisseurController extends Controller
{
    public function index()
    {
        $fournisseurs = Fournisseur::all();

        return Inertia::render('Fournisseurs/Index', [
            'fournisseurs' => $fournisseurs,
        ]);
    }

    public function create()
    {
        return Inertia::render('Fournisseurs/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_fourn' => 'required|integer|unique:fournisseurs,id_fourn',
            'nom_fourn' => 'required|string|max:255',
            'adress_fourn' => 'required|string|max:255',
            'nrc_fourn' => 'required|string|max:255',
        ]);

        Fournisseur::create($validated);

        return redirect()->route('fournisseurs.index')->with('success', 'Fournisseur créé avec succès.');
    }

    public function edit(Fournisseur $fournisseur)
    {
        return Inertia::render('Fournisseurs/Edit', [
            'fournisseur' => $fournisseur,
        ]);
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $validated = $request->validate([
            'id_fourn' => 'required|integer|unique:fournisseurs,id_fourn,' . $fournisseur->id_fourn . ',id_fourn',
            'nom_fourn' => 'required|string|max:255',
            'adress_fourn' => 'required|string|max:255',
            'nrc_fourn' => 'required|string|max:255',
        ]);

        $fournisseur->update($validated);

        return redirect()->route('fournisseurs.index')->with('success', 'Fournisseur mis à jour avec succès.');
    }

    public function destroy(Fournisseur $fournisseur)
    {
        $fournisseur->delete();

        return redirect()->route('fournisseurs.index')->with('success', 'Fournisseur supprimé avec succès.');
    }
}
