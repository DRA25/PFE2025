<?php

namespace App\Http\Controllers\Atelier;

use App\Http\Controllers\Controller;
use App\Models\Piece;
use Inertia\Inertia;
use Illuminate\Http\Request;

class DemandepieceController extends Controller
{
   /* public function index()
    {
        return Inertia::render('Atelier/demandepiece', [
            'userHasRole' => auth()->user()?->getRoleNames()->first(), // e.g. 'atelier'
        ]);
    }*/
    public function index()
    {
        return Inertia::render('Atelier/Index', [
            'pieces' => Piece::all(),  // Tu récupères toujours les pièces
        ]);
    }

    public function create()
    {
        return Inertia::render('Atelier/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_piece' => 'required|integer|unique:pieces,id_piece',
            'nom_piece' => 'required|string',
            'prix_piece' => 'required|integer',
            'marque_piece' => 'required|string',
            'ref_piece' => 'required|string',
        ]);

        Piece::create($validated);

        return redirect()->route('atelier.index')->with('success', 'Piece created successfully.');
    }
    public function show(Piece $piece)
    {
        return Inertia::render('Atelier/Show', [
            'piece' => $piece,
        ]);
    }

    public function edit(Piece $piece)
    {
        return Inertia::render('Atelier/Edit', [
            'piece' => $piece,
        ]);
    }

    public function update(Request $request, Piece $piece)
    {
        $validated = $request->validate([
            'id_piece' => 'required|integer|unique:pieces,id_piece',
            'nom_piece' => 'required|string',
            'prix_piece' => 'required|integer',
            'marque_piece' => 'required|string',
            'ref_piece' => 'required|string',
        ]);

        $piece->update($validated);

        return redirect()->route('atelier.show')->with('success', 'Piece updated successfully.');
    }

    public function destroy(Piece $piece)
    {
        $piece->delete();

        return redirect()->route('atelier.index')->with('success', 'Piece deleted successfully.');
    }
    }
