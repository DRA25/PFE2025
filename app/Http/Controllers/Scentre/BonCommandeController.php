<?php

namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\BonDeCommande;
use App\Models\Piece;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BonCommandeController extends Controller
{
    public function index()
    {
        $boncommandes = BonDeCommande::with(['pieces:id_piece,nom_piece'])
            ->get()
            ->map(function ($bc) {
                return [
                    'n_bc' => $bc->n_bc,
                    'date_bc' => $bc->date_bc,
                    'pieces' => $bc->pieces->map(function ($piece) {
                        return [
                            'id_piece' => $piece->id_piece,
                            'nom_piece' => $piece->nom_piece,
                            'qte_commandep' => $piece->pivot->qte_commandep,
                        ];
                    }),
                ];
            });

        return Inertia::render('BonCommande/Index', [
            'boncommandes' => $boncommandes,
        ]);
    }


    public function show($n_bc)
    {
        $boncommande = BonDeCommande::with('pieces:id_piece,nom_piece')
            ->findOrFail($n_bc);

        return Inertia::render('BonCommande/Show', [
            'boncommande' => [
                'n_bc' => $boncommande->n_bc,
                'date_bc' => $boncommande->date_bc,
                'pieces' => $boncommande->pieces->map(function ($piece) {
                    return [
                        'id_piece' => $piece->id_piece,
                        'nom_piece' => $piece->nom_piece,
                        'qte_commandep' => $piece->pivot->qte_commandep,
                    ];
                }),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('BonCommande/Create', [
            'pieces' => Piece::select('id_piece', 'nom_piece')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'n_bc' => 'required|integer|unique:bon_de_commandes,n_bc',
            'date_bc' => 'required|date',
            'selectedPieces' => 'required|array|min:1',
            'selectedPieces.*.id_piece' => 'required|exists:pieces,id_piece',
            'selectedPieces.*.qte_commandep' => 'required|integer|min:1',
        ]);

        $boncommande = BonDeCommande::create([
            'n_bc' => $request->n_bc,
            'date_bc' => $request->date_bc,
        ]);

        // Attach pieces with quantities
        $pivotData = collect($request->selectedPieces)->mapWithKeys(function ($item) {
            return [
                $item['id_piece'] => ['qte_commandep' => $item['qte_commandep']]
            ];
        });

        $boncommande->pieces()->attach($pivotData);

        return redirect()->route('scentre.boncommandes.index')->with('success', 'Bon de commande créé avec succès.');
    }


    public function edit($n_bc)
    {
        $boncommande = BonDeCommande::with('pieces')->findOrFail($n_bc);

        $boncommandeData = [
            'n_bc' => $boncommande->n_bc,
            'date_bc' => $boncommande->date_bc,
            'pieces' => $boncommande->pieces->map(function ($piece) {
                return [
                    'id_piece' => $piece->id_piece,
                    'nom_piece' => $piece->nom_piece,
                    'qte_commandep' => $piece->pivot->qte_commandep, // include pivot field explicitly
                    'prix_piece' => $piece->prix_piece,
                    'tva' => $piece->tva,
                ];
            }),
        ];

        $pieces = Piece::select('id_piece', 'nom_piece', 'prix_piece', 'tva')->get();

        return Inertia::render('BonCommande/Edit', [
            'boncommande' => $boncommandeData,
            'pieces' => $pieces,
        ]);
    }



    public function update(Request $request, $n_bc)
    {
        $validated = $request->validate([
            'date_bc' => 'required|date',
            'pieces' => 'array',
            'pieces.*.id_piece' => 'required|exists:pieces,id_piece',
            'pieces.*.qte_commandep' => 'required|integer|min:1',
        ]);

        $boncommande = BonDeCommande::findOrFail($n_bc);
        $boncommande->update(['date_bc' => $validated['date_bc']]);

        // Sync pivot table
        $pivotData = collect($validated['pieces'])->mapWithKeys(function ($item) {
            return [$item['id_piece'] => ['qte_commandep' => $item['qte_commandep']]];
        });
        $boncommande->pieces()->sync($pivotData);

        return redirect()->route('scentre.boncommandes.index')->with('success', 'Bon de commande mis à jour.');
    }

    public function destroy($n_bc)
    {
        $boncommande = BonDeCommande::findOrFail($n_bc);
        $boncommande->delete();

        return redirect()->route('scentre.boncommandes.index')->with('success', 'Bon de commande supprimé.');
    }






    public function exportPdf($n_bc)
    {
        $boncommande = BonDeCommande::with('pieces:id_piece,nom_piece')
            ->findOrFail($n_bc);

        $data = [
            'boncommande' => [
                'n_bc' => $boncommande->n_bc,
                'date_bc' => $boncommande->date_bc,
                'pieces' => $boncommande->pieces->map(function ($piece) {
                    return [
                        'id_piece' => $piece->id_piece,
                        'nom_piece' => $piece->nom_piece,
                        'qte_commandep' => $piece->pivot->qte_commandep,
                    ];
                }),
            ],
        ];

        $pdf = Pdf::loadView('achat.boncommande.boncommande', $data);

        // Return with proper headers
        return $pdf->download('boncommande-'.$boncommande->n_bc.'.pdf');


    }

}
