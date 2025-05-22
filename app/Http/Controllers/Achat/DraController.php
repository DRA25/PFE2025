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

    public function autocreate(Request $request)
    {
        $user = Auth::user();
        $centreId = $user->id_centre;

        // Count existing DRA entries for this centre
        $count = Dra::where('id_centre', $centreId)->count() + 1;

        // Create the n_dra format like "CENTREID-0001"
        $n_dra = $centreId . str_pad($count, 6, '0', STR_PAD_LEFT);

        // Create the DRA
        Dra::create([
            'n_dra' => $n_dra,
            'id_centre' => $centreId,
            'date_creation' => now(),
            'etat' => 'actif',
            'total_dra' => 0,
        ]);

        return redirect()->route('achat.dras.index');
    }


    public function store(Request $request)
    {
        $centreId = Auth::user()->id_centre;
        $count = Dra::where('id_centre', $centreId)->count();
        $n_dra = $centreId . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        $dra = new Dra();
        $dra->n_dra = $n_dra;
        $dra->id_centre = $centreId;
        $dra->date_creation = now()->toDateString();
        $dra->etat = 'actif';
        $dra->total_dra = 0;
        $dra->save();

        return redirect()->route('achat.dras.index')->with('success', 'DRA créé avec succès.');
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
