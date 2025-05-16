<?php
namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use App\Models\Dra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DraController extends Controller
{
public function index()
{
$dras = Dra::query()
->orderBy('created_at', 'desc')
->get();

return Inertia::render('Dra/Index', [
'dras' => $dras->map(function ($dra) {
return [
'n_dra' => $dra->n_dra,
'id_centre' => $dra->id_centre,
'date_creation' => $dra->date_creation->format('Y-m-d'),
'etat' => $dra->etat,
'total_dra' => $dra->total_dra,
'created_at' => $dra->created_at ? $dra->created_at->toISOString() : now()->toISOString(),
    'centre' => [
        'seuil_centre' => $dra->centre->seuil_centre]
];
})
]);
}

public function create()
{
$centres = Centre::all();

return Inertia::render('Dra/Create', [
'centres' => $centres,
]);
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
'id_centre' => 'required',
'date_creation' => 'required|date',
]);

Dra::create(array_merge($validated, [
'etat' => 'actif',
'total_dra' => 0,
'created_at' => now()
]));

return redirect()->route('achat.dras.index')->with('success', 'DRA créé avec succès');
}

public function edit(Dra $dra)
{
$centres = Centre::all();

return Inertia::render('Dra/Edit', [
'dra' => $dra,
'centres' => $centres,
]);
}

public function update(Request $request, Dra $dra)
{
$validated = $request->validate([
'id_centre' => 'required',
'date_creation' => 'required|date',
]);

$dra->update($validated);

return redirect()->route('achat.dras.index')
->with('success', 'DRA mis à jour avec succès');
}

public function destroy($n_dra)
{
DB::beginTransaction();

try {
$dra = Dra::where('n_dra', $n_dra)->firstOrFail();

if ($dra->etat !== 'actif') {
return back()->withErrors(['error' => 'Seuls les DRAs actifs peuvent être supprimés']);
}

$dra->factures()->delete();
$dra->delete();

DB::commit();

return redirect()->route('achat.dras.index')
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

return redirect()->route('achat.dras.index')
->with('success', 'DRA clôturé avec succès');
}
}
