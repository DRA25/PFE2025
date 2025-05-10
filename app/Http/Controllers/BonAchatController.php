<?php

namespace App\Http\Controllers;

use App\Models\Dra;
use App\Models\BonAchat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BonAchatController extends Controller
{
    public function index(Dra $dra)
    {
        $bonAchats = $dra->bonAchats;
        return Inertia::render('BonAchat/Index', compact('dra', 'bonAchats'));
    }

    public function create(Dra $dra)
    {
        return Inertia::render('BonAchat/Create', compact('dra'));
    }

    public function store(Request $request, Dra $dra)
    {
        $request->validate([
            'n_ba' => 'required|unique:bon_achats,n_ba',
            'montant_ba' => 'required|integer',
            'date_ba' => 'required|date',
            'id_fourn' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $dra->bonAchats()->create([
                'n_ba' => $request->n_ba,
                'montant_ba' => $request->montant_ba,
                'date_ba' => $request->date_ba,
                'id_fourn' => $request->id_fourn,
                'n_dra' => $dra->n_dra,
            ]);

            // Update total_dra by summing bonAchats and factures
            $dra->update([
                'total_dra' => $dra->bonAchats()->sum('montant_ba') + $dra->factures()->sum('montant_facture')
            ]);

            DB::commit();

            return redirect()->route('dras.bon-achats.index', $dra->n_dra)
                ->with('success', 'Bon d\'achat créé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la création du bon d\'achat.']);
        }
    }

    public function edit(Dra $dra, BonAchat $bonAchat)
    {
        return Inertia::render('BonAchat/Edit', compact('dra', 'bonAchat'));
    }

    public function update(Request $request, $n_dra, $n_ba)
    {
        $request->validate([
            'n_ba' => 'required|unique:bon_achats,n_ba,'.$n_ba.',n_ba',
            'montant_ba' => 'required|integer',
            'date_ba' => 'required|date',
            'id_fourn' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            $dra = Dra::where('n_dra', $n_dra)->firstOrFail();
            $bonAchat = BonAchat::where('n_ba', $n_ba)->firstOrFail();

            $bonAchat->update([
                'n_ba' => $request->n_ba,
                'montant_ba' => $request->montant_ba,
                'date_ba' => $request->date_ba,
                'id_fourn' => $request->id_fourn
            ]);

            // Update total_dra by summing bonAchats and factures
            $dra->update([
                'total_dra' => $dra->bonAchats()->sum('montant_ba') + $dra->factures()->sum('montant_facture')
            ]);

            DB::commit();

            return redirect()->route('dras.bon-achats.index', $dra->n_dra)
                ->with('success', 'Bon d\'achat mis à jour avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
        }
    }

    public function destroy(Dra $dra, BonAchat $bonAchat)
    {
        DB::beginTransaction();

        try {
            $bonAchat->delete();

            // Update total_dra by summing bonAchats and factures
            $dra->update([
                'total_dra' => $dra->bonAchats()->sum('montant_ba') + $dra->factures()->sum('montant_facture')
            ]);

            DB::commit();

            return redirect()->route('dras.bonAchats.index', $dra->n_dra)
                ->with('success', 'Bon d\'achat supprimé avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Erreur lors de la suppression']);
        }
    }
}
