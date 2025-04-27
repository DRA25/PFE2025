<?php
namespace App\Http\Controllers;

use App\Models\DRA;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DRAController extends Controller
{
    public function index()
    {
        return Inertia::render('Achat/DRA/Index', [
            'dras' => DRA::all()
        ]);
    }

    public function create()
    {
        // Fetch all fournisseurs (id + name)
        $fournisseurs = Fournisseur::select('id_fourn', 'nom_fourn')->get();

        // Pass them to the Vue component
        return Inertia::render('Achat/DRA/Create', [
            'fournisseurs' => $fournisseurs,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'n_dra' => 'required|string|unique:d_r_a_s,n_dra',
            'periode' => 'required|date',
            'etat' => 'required|string',
            'cmp_gen' => 'required|integer',
            'cmp_ana' => 'required|integer',
            'debit' => 'required|numeric',
            'libelle_dra' => 'required|string',
            'date_dra' => 'required|date',
            'fourn_dra' => 'required|integer|exists:fournisseurs,id_fourn', // Validate fournisseur id
            'destinataire_dra' => 'required|string',
        ]);

        DRA::create($validated);

        return redirect()->route('dra.index')->with('success', 'DRA created successfully.');
    }

    public function edit(DRA $dra)
    {
        $fournisseurs = Fournisseur::select('id_fourn', 'nom_fourn')->get();

        return Inertia::render('Achat/DRA/Edit', [
            'dra' => $dra,
            'fournisseurs' => $fournisseurs,
        ]);
    }

    public function update(Request $request, $n_dra)
    {
        $dra = DRA::where('n_dra', $n_dra)->firstOrFail();

        $validated = $request->validate([
            'periode' => 'required|date',
            'etat' => 'required|string',
            'cmp_gen' => 'nullable|numeric',
            'cmp_ana' => 'nullable|numeric',
            'debit' => 'nullable|numeric',
            'libelle_dra' => 'required|string',
            'date_dra' => 'required|date',
            'fourn_dra' => 'required|integer|exists:fournisseurs,id_fourn', // Validate fournisseur id
            'destinataire_dra' => 'required|string',
        ]);

        // Update the dra object with validated data
        $dra->update($validated);

        // Return a redirect response after successful update
        return redirect()->route('dra.index')->with('success', 'DRA mise à jour avec succès.');
    }

    public function destroy(DRA $dra)
    {
        $dra->delete();
        return redirect()->route('dra.index')->with('success', 'DRA deleted.');
    }
}
