<?php

namespace App\Http\Controllers;

use App\Models\Dra;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DraController extends Controller
{
    public function index()
    {
        $dras = Dra::all();
        return Inertia::render('Dra/Index', compact('dras'));
    }

    public function create()
    {
        return Inertia::render('Dra/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'n_dra' => 'required|unique:dras,n_dra',
            'date_creation' => 'required|date',
            'etat' => 'required|in:actif,cloture',
            'seuil_dra' => 'required|integer',
            'total_dra' => 'required|integer',
        ]);

        Dra::create($request->all());

        return redirect()->route('dras.index')->with('success', 'DRA created successfully.');
    }

    public function edit(Dra $dra)
    {
        return Inertia::render('Dra/Edit', compact('dra'));
    }

    public function update(Request $request, Dra $dra)
    {
        $request->validate([
            'date_creation' => 'required|date',
            'etat' => 'required|in:actif,cloture',
            'seuil_dra' => 'required|integer',
            'total_dra' => 'required|integer',
        ]);

        $dra->update($request->all());

        return redirect()->route('dras.index')->with('success', 'DRA updated successfully.');
    }

    public function destroy(Dra $dra)
    {
        $dra->delete();
        return redirect()->route('dras.index')->with('success', 'DRA deleted successfully.');
    }
}
