<?php

namespace App\Http\Controllers;

use App\Models\Dra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DraController extends Controller
{
    // In your DraController.php
    public function index()
    {
        $dras = Dra::query()
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Dra/Index', [
            'dras' => $dras->map(function ($dra) {
                return [
                    'n_dra' => $dra->n_dra,
                    'date_creation' => $dra->date_creation->format('Y-m-d'),
                    'etat' => $dra->etat,
                    'total_dra' => $dra->total_dra,
                    'created_at' => $dra->created_at ? $dra->created_at->toISOString() : now()->toISOString()
                ];
            })
        ]);
    }

    public function create()
    {
        return Inertia::render('Dra/Create');
    }

    public function store(Request $request)
    {
        if (Dra::where('etat', 'actif')->exists()) {
            return back()->withErrors([
                'etat' => 'Un DRA actif existe déjà. Veuillez le clôturer avant de créer un nouveau.'
            ]);
        }

        $validated = $request->validate([
            'n_dra' => 'required|unique:dras,n_dra',
            'date_creation' => 'required|date',
            'seuil_dra' => 'required|numeric',
        ]);

        Dra::create(array_merge($validated, [
            'etat' => 'actif',
            'total_dra' => 0,
            'created_at' => now() // Explicitly set current timestamp
        ]));

        return redirect()->route('dras.index')->with('success', 'DRA créé avec succès');
    }
    public function edit(Dra $dra)
    {
        return Inertia::render('Dra/Edit', compact('dra'));
    }
    public function update(Request $request, Dra $dra)
    {
        $validated = $request->validate([
            'date_creation' => 'required|date',
            'seuil_dra' => 'required|numeric',
            // total_dra is no longer validated or updated here
        ]);

        $dra->update($validated);

        return redirect()->route('dras.index')
            ->with('success', 'DRA mis à jour avec succès');
    }

    public function destroy($n_dra)
    {
        DB::beginTransaction();

        try {
            $dra = Dra::where('n_dra', $n_dra)->firstOrFail();

            // Check if DRA is active
            if ($dra->etat !== 'actif') {
                return back()->withErrors(['error' => 'Seuls les DRAs actifs peuvent être supprimés']);
            }

            // Delete associated factures first
            $dra->factures()->delete();

            // Then delete the DRA
            $dra->delete();

            DB::commit();

            return redirect()->route('dras.index')
                ->with('success', 'DRA supprimé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    public function close(Dra $dra)
    {
        if ($dra->etat !== 'actif') {
            return back()->withErrors(['etat' => 'Seuls les DRAs actifs peuvent être clôturés']);
        }

        $dra->update(['etat' => 'cloture']);

        return redirect()->route('dras.index')
            ->with('success', 'DRA clôturé avec succès');
    }

}
