<?php

namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\BonDeCommande;
use App\Models\Piece;
use App\Models\Prestation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BonCommandeController extends Controller
{
    public function index()
    {
        $boncommandes = BonDeCommande::with(['pieces:id_piece,nom_piece', 'prestations:id_prest,nom_prest'])
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
                    'prestations' => $bc->prestations->map(function ($prestation) {
                        return [
                            'id_prest' => $prestation->id_prest,
                            'nom_prest' => $prestation->nom_prest,
                            'qte_commandepr' => $prestation->pivot->qte_commandepr,
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
        $boncommande = BonDeCommande::with(['pieces:id_piece,nom_piece', 'prestations:id_prest,nom_prest'])
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
                'prestations' => $boncommande->prestations->map(function ($prestation) {
                    return [
                        'id_prest' => $prestation->id_prest,
                        'nom_prest' => $prestation->nom_prest,
                        'qte_commandepr' => $prestation->pivot->qte_commandepr,
                    ];
                }),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('BonCommande/Create', [
            'pieces' => Piece::select('id_piece', 'nom_piece')->get(),
            'prestations' => Prestation::select('id_prest', 'nom_prest')->get()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'n_bc' => 'required|integer|unique:bon_de_commandes,n_bc',
            'date_bc' => 'required|date',
            'selectedPieces' => 'sometimes|array',
            'selectedPieces.*.id_piece' => 'required_with:selectedPieces|exists:pieces,id_piece',
            'selectedPieces.*.qte_commandep' => 'required_with:selectedPieces|integer|min:1',
            'selectedPrestations' => 'sometimes|array',
            'selectedPrestations.*.id_prest' => 'required_with:selectedPrestations|exists:prestations,id_prest',
            'selectedPrestations.*.qte_commandepr' => 'required_with:selectedPrestations|integer|min:1',
        ]);

        // At least one piece or prestation must be selected
        if (empty($request->selectedPieces) && empty($request->selectedPrestations)) {
            return back()->withErrors(['general' => 'Vous devez sélectionner au moins une pièce ou une prestation.']);
        }

        $boncommande = BonDeCommande::create([
            'n_bc' => $request->n_bc,
            'date_bc' => $request->date_bc,
        ]);

        // Attach pieces with quantities if provided
        if ($request->selectedPieces) {
            $pivotData = collect($request->selectedPieces)->mapWithKeys(function ($item) {
                return [$item['id_piece'] => ['qte_commandep' => $item['qte_commandep']]];
            });
            $boncommande->pieces()->attach($pivotData);
        }

        // Attach prestations with quantities if provided
        if ($request->selectedPrestations) {
            $pivotData = collect($request->selectedPrestations)->mapWithKeys(function ($item) {
                return [$item['id_prest'] => ['qte_commandepr' => $item['qte_commandepr']]];
            });
            $boncommande->prestations()->attach($pivotData);
        }

        return redirect()->route('scentre.boncommandes.index')->with('success', 'Bon de commande créé avec succès.');
    }

    public function edit($n_bc)
    {
        $boncommande = BonDeCommande::with(['pieces', 'prestations'])->findOrFail($n_bc);

        $boncommandeData = [
            'n_bc' => $boncommande->n_bc,
            'date_bc' => $boncommande->date_bc,
            'pieces' => $boncommande->pieces->map(function ($piece) {
                return [
                    'id_piece' => $piece->id_piece,
                    'nom_piece' => $piece->nom_piece,
                    'qte_commandep' => $piece->pivot->qte_commandep,
                    'prix_piece' => $piece->prix_piece,
                    'tva' => $piece->tva,
                ];
            }),
            'prestations' => $boncommande->prestations->map(function ($prestation) {
                return [
                    'id_prest' => $prestation->id_prest,
                    'nom_prest' => $prestation->nom_prest,
                    'qte_commandepr' => $prestation->pivot->qte_commandepr,
                    'prix_prest' => $prestation->prix_prest,
                    'tva' => $prestation->tva,
                ];
            }),
        ];

        $pieces = Piece::select('id_piece', 'nom_piece', 'prix_piece', 'tva')->get();
        $prestations = Prestation::select('id_prest', 'nom_prest', 'prix_prest', 'tva')->get();

        return Inertia::render('BonCommande/Edit', [
            'boncommande' => $boncommandeData,
            'pieces' => $pieces,
            'prestations' => $prestations,
        ]);
    }

    public function update(Request $request, $n_bc)
    {
        $validated = $request->validate([
            'date_bc' => 'required|date',
            'pieces' => 'sometimes|array',
            'pieces.*.id_piece' => 'required_with:pieces|exists:pieces,id_piece',
            'pieces.*.qte_commandep' => 'required_with:pieces|integer|min:1',
            'prestations' => 'sometimes|array',
            'prestations.*.id_prest' => 'required_with:prestations|exists:prestations,id_prest',
            'prestations.*.qte_commandepr' => 'required_with:prestations|integer|min:1',
        ]);

        // At least one piece or prestation must be selected
        if (empty($request->pieces) && empty($request->prestations)) {
            return back()->withErrors(['general' => 'Vous devez sélectionner au moins une pièce ou une prestation.']);
        }

        $boncommande = BonDeCommande::findOrFail($n_bc);
        $boncommande->update(['date_bc' => $validated['date_bc']]);

        // Sync pieces if provided
        if (isset($validated['pieces'])) {
            $pivotData = collect($validated['pieces'])->mapWithKeys(function ($item) {
                return [$item['id_piece'] => ['qte_commandep' => $item['qte_commandep']]];
            });
            $boncommande->pieces()->sync($pivotData);
        } else {
            $boncommande->pieces()->detach();
        }

        // Sync prestations if provided
        if (isset($validated['prestations'])) {
            $pivotData = collect($validated['prestations'])->mapWithKeys(function ($item) {
                return [$item['id_prest'] => ['qte_commandepr' => $item['qte_commandepr']]];
            });
            $boncommande->prestations()->sync($pivotData);
        } else {
            $boncommande->prestations()->detach();
        }

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
        $boncommande = BonDeCommande::with(['pieces:id_piece,nom_piece', 'prestations:id_prest,nom_prest'])
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
                'prestations' => $boncommande->prestations->map(function ($prestation) {
                    return [
                        'id_prest' => $prestation->id_prest,
                        'nom_prest' => $prestation->nom_prest,
                        'qte_commandepr' => $prestation->pivot->qte_commandepr,
                    ];
                }),
            ],
        ];

        $pdf = Pdf::loadView('scentre.boncommande.boncommande', $data);
        return $pdf->download('boncommande-'.$boncommande->n_bc.'.pdf');
    }
}
