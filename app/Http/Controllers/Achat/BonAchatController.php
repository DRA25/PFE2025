<?php
namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\BonAchat;
use App\Models\Dra;
use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BonAchatController extends Controller
{
public function index(Dra $dra)
{
$bonAchats = $dra->bonAchats()->with('fournisseur:id_fourn,nom_fourn')->get();

return Inertia::render('BonAchat/Index', [
'dra' => $dra,
'bonAchats' => $bonAchats,
]);
}

    public function show(Dra $dra)
    {
        $bonAchats = $dra->bonAchats()->with('fournisseur:id_fourn,nom_fourn')->get();

        return Inertia::render('BonAchat/Show', [
            'dra' => $dra,
            'bonAchats' => $bonAchats,
        ]);
    }

public function create(Dra $dra)
{
$fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);

return Inertia::render('BonAchat/Create', [
'dra' => $dra,
'fournisseurs' => $fournisseurs,
]);
}

public function store(Request $request, Dra $dra)
{
$request->validate([
'n_ba' => 'required|unique:bon_achats,n_ba',
'montant_ba' => 'required|integer',
'date_ba' => 'required|date',
'id_fourn' => 'required|exists:fournisseurs,id_fourn',
]);

DB::beginTransaction();

try {
$bonAchat = $dra->bonAchats()->create([
'n_ba' => $request->n_ba,
'montant_ba' => $request->montant_ba,
'date_ba' => $request->date_ba,
'id_fourn' => $request->id_fourn,
'n_dra' => $dra->n_dra,
]);

$totalDra = $dra->bonAchats()->sum('montant_ba') + $dra->factures()->sum('montant_facture');

if ($totalDra > $dra->centre->seuil_centre) {
DB::rollBack();
return back()->withErrors(['total_dra' => 'Le total du DRA dépasse le seuil autorisé du centre.']);
}

$dra->update([
'total_dra' => $totalDra,
]);

DB::commit();

return redirect()->route('achat.dras.bon-achats.index', $dra->n_dra)
->with('success', 'Bon d\'achat créé avec succès.');
} catch (\Exception $e) {
DB::rollBack();
return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()]);
}
}

public function edit(Dra $dra, BonAchat $bonAchat)
{
$fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);

return Inertia::render('BonAchat/Edit', [
'dra' => $dra,
'bonAchat' => $bonAchat,
'fournisseurs' => $fournisseurs,
]);
}

public function update(Request $request, $n_dra, $n_ba)
{
$request->validate([
'n_ba' => 'required|unique:bon_achats,n_ba,' . $n_ba . ',n_ba',
'montant_ba' => 'required|integer',
'date_ba' => 'required|date',
'id_fourn' => 'required|exists:fournisseurs,id_fourn',
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

$totalDra = $dra->bonAchats()->sum('montant_ba') + $dra->factures()->sum('montant_facture');

if ($totalDra > $dra->centre->seuil_centre) {
DB::rollBack();
return back()->withErrors(['total_dra' => 'Le total du DRA dépasse le seuil autorisé du centre.']);
}

$dra->update([
'total_dra' => $totalDra
]);

DB::commit();

return redirect()->route('achat.dras.bon-achats.index', $dra->n_dra)
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

$totalDra = $dra->bonAchats()->sum('montant_ba') + $dra->factures()->sum('montant_facture');

$dra->update([
'total_dra' => $totalDra
]);

DB::commit();

return redirect()->route('achat.dras.bon-achats.index', $dra->n_dra)
->with('success', 'Bon d\'achat supprimé avec succès.');
} catch (\Exception $e) {
DB::rollBack();
return back()->withErrors(['error' => 'Erreur lors de la suppression : ' . $e->getMessage()]);
}
}
}
