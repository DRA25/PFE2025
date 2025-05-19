<?php

namespace App\Http\Controllers\Magasin;

use App\Http\Controllers\Controller;
use App\Models\Magasin;
use App\Models\Centre;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GestionMagasinController extends Controller
{
public function index()
{
$magasins = Magasin::with('centre')->get();
return Inertia::render('Magasin/GestionMagasin/Index', compact('magasins'));
}

public function create()
{
$centres = Centre::all();
return Inertia::render('Magasin/GestionMagasin/Create', compact('centres'));
}

public function store(Request $request)
{
$request->validate([
'id_magasin' => 'required|integer|unique:magasins',
'adresse_magasin' => 'required|string|max:255',
'id_centre' => 'nullable|string|exists:centres,id_centre',
]);

Magasin::create($request->all());

return redirect()->route('gestionmagasin.index')->with('success', 'Magasin ajouté avec succès.');
}

public function edit($id)
{
$magasin = Magasin::findOrFail($id);
$centres = Centre::all();
return Inertia::render('Magasin/GestionMagasin/Edit', compact('magasin', 'centres'));
}

public function update(Request $request, $id)
{
$request->validate([
'adresse_magasin' => 'required|string|max:255',
'id_centre' => 'nullable|string|exists:centres,id_centre',
]);

$magasin = Magasin::findOrFail($id);
$magasin->update($request->all());

return redirect()->route('gestionmagasin.index')->with('success', 'Magasin modifié avec succès.');
}

public function destroy($id)
{
Magasin::findOrFail($id)->delete();
return redirect()->route('gestionmagasin.index')->with('success', 'Magasin supprimé.');
}
}
