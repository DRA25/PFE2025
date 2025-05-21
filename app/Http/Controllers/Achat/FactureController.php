<?php
namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Dra;
use App\Models\Facture;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FactureController extends Controller
{
public function index(Dra $dra)
{
// Eager load 'fournisseur' relationship with factures
$factures = $dra->factures()->with('fournisseur:id_fourn,nom_fourn')->get();

return Inertia::render('Facture/Index', [
'dra' => $dra,
'factures' => $factures,
]);
}

    public function Show(Dra $dra)
    {
// Eager load 'fournisseur' relationship with factures
        $factures = $dra->factures()->with('fournisseur:id_fourn,nom_fourn')->get();

        return Inertia::render('Facture/Show', [
            'dra' => $dra,
            'factures' => $factures,
        ]);
    }

public function create(Dra $dra)
{
// Fetch all fournisseurs for the select dropdown in create page
$fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);
return inertia('Facture/Create', [
'dra' => $dra,
'fournisseurs' => $fournisseurs,
]);
}

public function store(Request $request, Dra $dra)
{
$request->validate([
'n_facture' => 'required|unique:factures,n_facture',
'montant_facture' => 'required|integer',
'date_facture' => 'required|date',
'id_fourn' => 'required|exists:fournisseurs,id_fourn',
]);

DB::beginTransaction();

try {
// Create the facture
$facture = $dra->factures()->create([
'n_facture' => $request->n_facture,
'montant_facture' => $request->montant_facture,
'date_facture' => $request->date_facture,
'id_fourn' => $request->id_fourn,
'n_dra' => $dra->n_dra,
]);

// Recalculate total
$totalDra = $dra->bonAchats()->sum('montant_ba') + $dra->factures()->sum('montant_facture');

// Check if the total exceeds the threshold
if ($totalDra > $dra->centre->seuil_centre) {
DB::rollBack();
return back()->withErrors(['total_dra' => 'Le total du DRA dépasse le seuil autorisé du centre.']);
}

// Otherwise, update and commit
$dra->update([
'total_dra' => $totalDra,
]);

DB::commit();

return redirect()->route('achat.dras.factures.index', $dra->n_dra)
->with('success', 'Facture créée avec succès.');

} catch (\Exception $e) {
DB::rollBack();
return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()]);
}
}

public function edit(Dra $dra, Facture $facture)
{
// Fetch all fournisseurs for the select dropdown in edit page
$fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);

return Inertia::render('Facture/Edit', [
'dra' => $dra,
'facture' => $facture,
'fournisseurs' => $fournisseurs,
]);
}

public function update(Request $request, $n_dra, $n_facture)
{
// Validate incoming request for the update
$request->validate([
'n_facture' => 'required|unique:factures,n_facture,' . $n_facture . ',n_facture',
'montant_facture' => 'required|integer',
'date_facture' => 'required|date',
'id_fourn' => 'required|exists:fournisseurs,id_fourn',
]);

DB::beginTransaction();

try {
// Find the dra and facture
$dra = Dra::where('n_dra', $n_dra)->firstOrFail();
$facture = Facture::where('n_facture', $n_facture)->firstOrFail();

// Update the facture
$facture->update([
'n_facture' => $request->n_facture,
'montant_facture' => $request->montant_facture,
'date_facture' => $request->date_facture,
'id_fourn' => $request->id_fourn,
]);

// Update total_dra by summing bonAchats and factures
$dra->update([
'total_dra' => $dra->bonAchats()->sum('montant_ba') + $dra->factures()->sum('montant_facture')
]);

    if ($totalDra > $dra->centre->seuil_centre) {
        DB::rollBack();
        return back()->withErrors(['total_dra' => 'Le total du DRA dépasse le seuil autorisé du centre.']);
    }

    $dra->update([
        'total_dra' => $totalDra
    ]);

    DB::commit();

    return redirect()->route('achat.dras.factures.index', $dra->n_dra)
        ->with('success', 'Facture mis à jour avec succès.');
} catch (\Exception $e) {
    DB::rollBack();
    return back()->withErrors(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
}
}

public function destroy(Dra $dra, Facture $facture)
{
DB::beginTransaction();

try {
// Delete the facture
$facture->delete();

// Update total_dra by summing bonAchats and factures
$dra->update([
'total_dra' => $dra->bonAchats()->sum('montant_ba') + $dra->factures()->sum('montant_facture')
]);

DB::commit();

return redirect()->route('achat.dras.factures.index', $dra->n_dra)
->with('success', 'Facture supprimée avec succès.');

} catch (\Exception $e) {
DB::rollBack();
return back()->withErrors(['error' => 'Erreur lors de la suppression']);
}
}
}
