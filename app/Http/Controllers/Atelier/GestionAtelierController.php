<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Models\Atelier;
use App\Models\Centre;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GestionAtelierController extends Controller
{
    public function index()
    {
        return Inertia::render('Atelier/GestionAtelier/Index', [
            'ateliers' => Atelier::with('centre')->get(),
        ]);
    }

    public function create()
    {
        return Inertia::render('Atelier/GestionAtelier/Create', [
            'centres' => Centre::all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_atelier' => 'required|integer|unique:ateliers,id_atelier',
            'adresse_atelier' => 'required|string|max:255',
            'id_centre' => 'nullable|exists:centres,id_centre',
        ]);

        Atelier::create($validated);

        return redirect()->route('gestionatelier.index')->with('success', 'Atelier créé avec succès.');
    }

    public function edit($id)
    {
        return Inertia::render('Atelier/GestionAtelier/Edit', [
            'atelier' => Atelier::findOrFail($id),
            'centres' => Centre::all(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $atelier = Atelier::findOrFail($id);

        $validated = $request->validate([
            'adresse_atelier' => 'required|string|max:255',
            'id_centre' => 'nullable|exists:centres,id_centre',
        ]);

        $atelier->update($validated);

        return redirect()->route('gestionatelier.index')->with('success', 'Atelier mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $atelier = Atelier::findOrFail($id);
        $atelier->delete();

        return redirect()->route('gestionatelier.index')->with('success', 'Atelier supprimé avec succès.');
    }
}
