<?php
namespace App\Http\Controllers\Achat;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use App\Models\Dra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DraController extends Controller
{
public function index()
{
$userCentreId = Auth::user()->id_centre;

$dras = Dra::query()
->where('id_centre', $userCentreId)
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
'seuil_centre' => $dra->centre->seuil_centre
]
];
})
]);
}

public function create()
{
$userCentreId = Auth::user()->id_centre;
$centre = Centre::findOrFail($userCentreId);

return Inertia::render('Dra/Create', [
'centre' => $centre, // Only pass the user's center
]);
}

public function store(Request $request)
{
$userCentreId = Auth::user()->id_centre;

// Check if there's already an active DRA for this center
if (Dra::where('etat', 'actif')->where('id_centre', $userCentreId)->exists()) {
return back()->withErrors([
'etat' => 'Un DRA actif existe déjà pour votre centre. Veuillez le clôturer avant de créer un nouveau.'
]);
}

$validated = $request->validate([
'n_dra' => 'required|unique:dras,n_dra',
'date_creation' => 'required|date',
]);

Dra::create(array_merge($validated, [
'id_centre' => $userCentreId, // Automatically assign user's center
'etat' => 'actif',
'total_dra' => 0,
'created_at' => now()
]));

return redirect()->route('achat.dras.index')->with('success', 'DRA créé avec succès');
}

public function edit(Dra $dra)
{
$userCentreId = Auth::user()->id_centre;

// Authorization: Ensure the DRA belongs to the user's center
if ($dra->id_centre !== $userCentreId) {
abort(403, 'Unauthorized action.');
}

return Inertia::render('Dra/Edit', [
'dra' => $dra,
]);
}

    public function update(Request $request, Dra $dra)
    {
        // Authorization - ensure DRA belongs to user's center
        if ($dra->id_centre !== Auth::user()->id_centre) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'etat' => 'required|string|in:cloture,refuse,accepte',
        ]);

        $dra->update($validated);

        return redirect()->back()->with('success', 'État du DRA mis à jour avec succès.');
    }


public function destroy($n_dra)
{
DB::beginTransaction();

try {
$userCentreId = Auth::user()->id_centre;
$dra = Dra::where('n_dra', $n_dra)->firstOrFail();

// Authorization: Ensure the DRA belongs to the user's center
if ($dra->id_centre !== $userCentreId) {
abort(403, 'Unauthorized action.');
}

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
        $userCentreId = Auth::user()->id_centre;

        // Authorization: Ensure the DRA belongs to the user's center
        if ($dra->id_centre !== $userCentreId) {
            abort(403, 'Unauthorized action.');
        }

        // Convert to lowercase for consistent comparison
        $normalizedEtat = strtolower($dra->etat);

        if ($normalizedEtat !== 'refuse' && $normalizedEtat !== 'actif') {
            return back()->withErrors([
                'etat' => 'Seuls les DRAs actifs ou refusés peuvent être clôturés'
            ]);
        }

        $dra->update(['etat' => 'cloture']);

        return redirect()->route('achat.dras.index')
            ->with('success', 'DRA clôturé avec succès');
    }
}
