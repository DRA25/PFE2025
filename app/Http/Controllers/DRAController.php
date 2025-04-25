<?php
namespace App\Http\Controllers;
use App\Models\DRA;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DRAController extends Controller
{
    public function index()
    {
        return Inertia::render('DRA/Index', [
            'dras' => DRA::all()
        ]);
    }

    public function create()
    {
        return Inertia::render('DRA/Create');
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
            'fourn_dra' => 'required|string',
            'destinataire_dra' => 'required|string',
        ]);

        DRA::create($validated);

        return redirect()->route('dra.index')->with('success', 'DRA created successfully.');
    }

    public function edit(DRA $dra)
    {
        return Inertia::render('DRA/Edit', [
            'dra' => $dra
        ]);
    }

    public function update(Request $request, DRA $dra)
    {
        $validated = $request->validate([
            'periode' => 'required|date',
            'etat' => 'required|string',
            'cmp_gen' => 'required|integer',
            'cmp_ana' => 'required|integer',
            'debit' => 'required|numeric',
            'libelle_dra' => 'required|string',
            'date_dra' => 'required|date',
            'fourn_dra' => 'required|string',
            'destinataire_dra' => 'required|string',
        ]);

        $dra->update($validated);

        return redirect()->route('dra.index')->with('success', 'DRA updated successfully.');
    }

    public function destroy(DRA $dra)
    {
        $dra->delete();
        return redirect()->route('dra.index')->with('success', 'DRA deleted.');
    }
}
