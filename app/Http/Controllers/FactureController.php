<?php

namespace App\Http\Controllers;

use App\Models\Dra;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FactureController extends Controller
{
    public function index(Dra $dra)
    {
        $factures = $dra->factures;
        return Inertia::render('Facture/Index', compact('dra', 'factures'));
    }

    public function create(Dra $dra)
    {
        return Inertia::render('Facture/Create', compact('dra'));
    }

    public function store(Request $request, Dra $dra)
    {
        $request->validate([
            'n_facture' => 'required|unique:factures,n_facture',
            'montant_facture' => 'required|integer',
            'date_facture' => 'required|date',
            'id_fourn' => 'required|integer',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the new facture
            $dra->factures()->create([
                'n_facture' => $request->n_facture,
                'montant_facture' => $request->montant_facture,
                'date_facture' => $request->date_facture,
                'id_fourn' => $request->id_fourn,
                'n_dra' => $dra->n_dra,
            ]);

            // Update the DRA's total_dra by summing all its factures
            $dra->update([
                'total_dra' => $dra->factures()->sum('montant_facture')
            ]);

            // Commit the transaction
            DB::commit();

            return redirect()->route('dras.factures.index', $dra->n_dra)
                ->with('success', 'Facture créée avec succès.');

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la création de la facture.']);
        }
    }

    public function edit(Dra $dra, Facture $facture)
    {
        return Inertia::render('Facture/Edit', compact('dra', 'facture'));
    }

    public function update(Request $request, $n_dra, $n_facture)
    {
        $request->validate([
            'n_facture' => 'required|unique:factures,n_facture,'.$n_facture.',n_facture',
            'montant_facture' => 'required|integer',
            'date_facture' => 'required|date',
            'id_fourn' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $dra = Dra::where('n_dra', $n_dra)->firstOrFail();
            $facture = Facture::where('n_facture', $n_facture)->firstOrFail();

            $facture->update([
                'n_facture' => $request->n_facture,
                'montant_facture' => $request->montant_facture,
                'date_facture' => $request->date_facture,
                'id_fourn' => $request->id_fourn
            ]);

            $dra->update([
                'total_dra' => $dra->factures()->sum('montant_facture')
            ]);

            DB::commit();

            return redirect()->route('dras.factures.index', $dra->n_dra)
                ->with('success', 'Facture mise à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

    public function destroy(Dra $dra, Facture $facture)
    {
        DB::beginTransaction();

        try {
            $facture->delete();

            $dra->update([
                'total_dra' => $dra->factures()->sum('montant_facture')
            ]);

            DB::commit();

            return redirect()->route('dras.factures.index', $dra->n_dra)
                ->with('success', 'Facture supprimée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la suppression']);
        }
    }
}
