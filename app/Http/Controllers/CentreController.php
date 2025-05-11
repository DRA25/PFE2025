<?php


namespace App\Http\Controllers;

use App\Models\Centre;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CentreController extends Controller
{
    // Display all centres
    public function index()
    {
        return Inertia::render('Centres/Index', [
            'centres' => Centre::all()->map(function ($centre) {
                return [
                    'id_centre' => $centre->id_centre,
                    'adresse_centre' => $centre->adresse_centre,
                    'seuil_centre' => $centre->seuil_centre,
                    'type_centre' => $centre->type_centre,
                ];
            }),
            'breadcrumbs' => [
                ['title' => 'Gestion des Centres', 'href' => '/centres'],
                ['title' => 'Liste des Centres', 'href' => '/centres'],
            ]
        ]);
    }

    // Show create form
    public function create()
    {
        return Inertia::render('Centres/Create', [
            'types' => ['Aviation', 'Marine'],
            'breadcrumbs' => [
                ['title' => 'Gestion des Centres', 'href' => '/centres'],
                ['title' => 'Créer un Centre', 'href' => '/centres/create'],
            ]
        ]);
    }

    // Store new centre
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_centre' => 'required|string|max:50|unique:centres',
            'adresse_centre' => 'nullable|string|max:200',
            'seuil_centre' => 'nullable|integer|min:0',
            'type_centre' => 'nullable|in:Aviation,Marine',
        ]);

        Centre::create($validated);

        return redirect()->route('centres.index')
            ->with('success', 'Centre créé avec succès');
    }

    // Show edit form
    public function edit(Centre $centre)
    {
        return Inertia::render('Centres/Edit', [
            'centre' => [
                'id_centre' => $centre->id_centre,
                'adresse_centre' => $centre->adresse_centre,
                'seuil_centre' => $centre->seuil_centre,
                'type_centre' => $centre->type_centre,
            ],
            'types' => ['Aviation', 'Marine'],
            'breadcrumbs' => [
                ['title' => 'Gestion des Centres', 'href' => '/centres'],
                ['title' => 'Modifier Centre', 'href' => '/centres/'.$centre->id_centre.'/edit'],
            ]
        ]);
    }

    // Update centre
    public function update(Request $request, Centre $centre)
    {
        $validated = $request->validate([
            'id_centre' => 'required|string|max:50|unique:centres,id_centre,'.$centre->id_centre.',id_centre',
            'adresse_centre' => 'nullable|string|max:200',
            'seuil_centre' => 'nullable|integer|min:0',
            'type_centre' => 'nullable|in:Aviation,Marine',
        ]);

        $centre->update($validated);

        return redirect()->route('centres.index')
            ->with('success', 'Centre mis à jour avec succès');
    }

    // Delete centre
    public function destroy(Centre $centre)
    {
        try {
            $centre->delete();
            return redirect()->route('centres.index')
                ->with('success', 'Centre supprimé avec succès');
        } catch (\Exception $e) {
            return redirect()->route('centres.index')
                ->with('error', 'Impossible de supprimer: ce centre a des DRAs associés');
        }
    }
}
