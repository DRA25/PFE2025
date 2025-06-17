<?php
namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use App\Models\Dra;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DraController extends Controller
{
    public function index()
    {
        $userCentreId = Auth::user()->id_centre;

        $dras = Dra::with('centre') // eager load to prevent N+1
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
                        'seuil_centre' => $dra->centre->seuil_centre,
                        'montant_disponible' => $dra->centre->montant_disponible,
                    ]
                ];
            }),
            'id_centre' => $userCentreId
        ]);
    }


    public function show($n_dra)
    {
        $userCentreId = Auth::user()->id_centre;

        $dra = Dra::with('centre')->where('n_dra', $n_dra)->firstOrFail();

        if ($dra->id_centre !== $userCentreId) {
            abort(403, 'Unauthorized action.');
        }

        // Load factures with related data
        $factures = $dra->factures()
            ->with(['fournisseur:id_fourn,nom_fourn', 'pieces:id_piece,nom_piece,prix_piece,tva'])
            ->get()
            ->map(function ($facture) {
                $facture->montant = $this->calculateMontant($facture);
                return $facture;
            });

        // Load bonAchats with related data
        $bonAchats = $dra->bonAchats()
            ->with(['fournisseur:id_fourn,nom_fourn', 'pieces:id_piece,nom_piece,prix_piece,tva'])
            ->get()
            ->map(function ($bonAchat) {
                $bonAchat->montant = $this->calculateMontant($bonAchat);
                return $bonAchat;
            });

        return Inertia::render('Dra/Show', [
            'dra' => [
                'n_dra' => $dra->n_dra,
                'id_centre' => $dra->id_centre,
                'date_creation' => $dra->date_creation->format('Y-m-d'),
                'etat' => $dra->etat,
                'total_dra' => $dra->total_dra,
                'created_at' => $dra->created_at?->toISOString(),
                'centre' => [
                    'seuil_centre' => $dra->centre->seuil_centre,
                    'montant_disponible' => $dra->centre->montant_disponible,
                ]
            ],
            'factures' => $factures,
            'bonAchats' => $bonAchats,
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

        return redirect()->route('scentre.dras.index');
    }


    public function store(Request $request)
    {
        $centreId = Auth::user()->id_centre;

        $centre = Centre::findOrFail($centreId);

        $count = Dra::where('id_centre', $centreId)->count();
        $n_dra = $centreId . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        // Default value; could be changed to allow setting total_dra via request
        $totalDra = 0;

        // Optional: If you want to prevent creating DRA with insufficient funds
        if ($centre->montant_disponible < $totalDra) {
            return back()->withErrors(['montant_disponible' => 'Fonds insuffisants pour créer une nouvelle DRA.']);
        }

        $dra = new Dra();
        $dra->n_dra = $n_dra;
        $dra->id_centre = $centreId;
        $dra->date_creation = now()->toDateString();
        $dra->etat = 'actif';
        $dra->total_dra = $totalDra;
        $dra->save();

        // Update montant_disponible if needed
        if ($totalDra > 0) {
            $centre->decrement('montant_disponible', $totalDra);
        }

        return redirect()->route('scentre.dras.index')->with('success', 'DRA créé avec succès.');
    }


    public function edit(Dra $dra)
{

return Inertia::render('Dra/Edit', [
'dra' => $dra,
]);
}

    public function update(Request $request, Dra $dra)
    {


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
            $dra = Dra::with(['bonAchats.pieces', 'factures.pieces', 'centre'])->where('n_dra', $n_dra)->firstOrFail();

            // Authorization: Ensure the DRA belongs to the user's center
            if ($dra->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }

            if ($dra->etat !== 'actif') {
                return back()->withErrors(['error' => 'Seuls les DRAs actifs peuvent être supprimés']);
            }

            // Calculate the total montant to restore
            $montantToRestore = '0';

            foreach ($dra->bonAchats as $bonAchat) {
                $montantToRestore = bcadd($montantToRestore, (string)$this->calculateMontant($bonAchat), 2);
            }

            foreach ($dra->factures as $facture) {
                $montantToRestore = bcadd($montantToRestore, (string)$this->calculateMontant($facture), 2);
            }

            // Delete related factures and bonAchats
            $dra->factures()->delete();
            $dra->bonAchats()->delete();

            // Delete the DRA itself
            $dra->delete();

            // Update the centre's montant_disponible
            $dra->centre->increment('montant_disponible', $montantToRestore);

            DB::commit();

            return redirect()->route('scentre.dras.index')
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

        return redirect()->route('scentre.dras.index')
            ->with('success', 'DRA clôturé avec succès');
    }

    protected function calculateMontant($model)
    {
        // Sum of pieces (HT + TVA)
        $total = $model->pieces->sum(function ($piece) {
            $ht = $piece->prix_piece;
            $tva = $piece->tva ?? 0;
            return $ht * (1 + $tva / 100);
        });

        // If it's a Facture, add droit_timbre
        if ($model instanceof \App\Models\Facture) {
            $total += $model->droit_timbre ?? 0;
        }

        return $total;
    }

    public function generateEtatSortie($n_dra)
    {
        $userCentreId = Auth::user()->id_centre;
        $dra = Dra::with('centre')->where('n_dra', $n_dra)->firstOrFail();

        if ($dra->id_centre !== $userCentreId) {
            abort(403, 'Unauthorized action.');
        }

        $draDateCreation = $dra->date_creation->format('d/m/Y');

        // Load factures with related data
        $factures = $dra->factures()
            ->with(['fournisseur:id_fourn,nom_fourn', 'pieces:id_piece,nom_piece,prix_piece,tva'])
            ->get()
            ->map(function ($facture) use ($draDateCreation) {
                $totalQuantity = 0;

                $montantHT = $facture->pieces->sum(function ($piece) use ($facture, &$totalQuantity) {
                    $quantity = $facture->pieces->find($piece->id_piece)->pivot->qte_f ?? 1;
                    $totalQuantity += $quantity;
                    return $piece->prix_piece * $quantity;
                });

                $tva = $facture->pieces->sum(function ($piece) use ($facture) {
                    $quantity = $facture->pieces->find($piece->id_piece)->pivot->qte_f ?? 1;
                    return ($piece->prix_piece * $quantity) * ($piece->tva ?? 0) / 100;
                });

                $droitTimbre = $facture->droit_timbre ?? 0;
                $total = $montantHT + $tva + $droitTimbre;

                // Combine all piece names with quantities for the libelle
                $pieceNames = $facture->pieces->map(function ($piece) use ($facture) {
                    $quantity = $facture->pieces->find($piece->id_piece)->pivot->qte_f ?? 1;
                    return $piece->nom_piece . ' (x' . $quantity . ')';
                })->implode(', ');

                return [
                    'n_dra' => $facture->n_dra,
                    'date_creation' => $draDateCreation,
                    'libelle' => $pieceNames,
                    'montant' => number_format($montantHT, 2, ',', ' '),
                    'tva' => number_format($tva, 2, ',', ' '),
                    'droit_timbre' => number_format($droitTimbre, 2, ',', ' '),
                    'nombre_piece' => $totalQuantity, // Total physical pieces (sum of quantities)
                    'total' => number_format($total, 2, ',', ' '),
                ];
            });

        // Load bonAchats with related data
        $bonAchats = $dra->bonAchats()
            ->with(['fournisseur:id_fourn,nom_fourn', 'pieces:id_piece,nom_piece,prix_piece,tva'])
            ->get()
            ->map(function ($bonAchat) use ($draDateCreation) {
                $totalQuantity = 0;

                $montantHT = $bonAchat->pieces->sum(function ($piece) use ($bonAchat, &$totalQuantity) {
                    $quantity = $bonAchat->pieces->find($piece->id_piece)->pivot->qte_b ?? 1;
                    $totalQuantity += $quantity;
                    return $piece->prix_piece * $quantity;
                });

                $tva = $bonAchat->pieces->sum(function ($piece) use ($bonAchat) {
                    $quantity = $bonAchat->pieces->find($piece->id_piece)->pivot->qte_b ?? 1;
                    return ($piece->prix_piece * $quantity) * ($piece->tva ?? 0) / 100;
                });

                $total = $montantHT + $tva;

                // Combine all piece names with quantities for the libelle
                $pieceNames = $bonAchat->pieces->map(function ($piece) use ($bonAchat) {
                    $quantity = $bonAchat->pieces->find($piece->id_piece)->pivot->qte_b ?? 1;
                    return $piece->nom_piece . ' (x' . $quantity . ')';
                })->implode(', ');

                return [
                    'n_dra' => $bonAchat->n_dra,
                    'date_creation' => $draDateCreation,
                    'libelle' => $pieceNames,
                    'montant' => number_format($montantHT, 2, ',', ' '),
                    'tva' => number_format($tva, 2, ',', ' '),
                    'droit_timbre' => '0,00',
                    'nombre_piece' => $totalQuantity, // Total physical pieces (sum of quantities)
                    'total' => number_format($total, 2, ',', ' '),
                ];
            });

        // Combine both collections
        $items = $factures->merge($bonAchats);

        // Calculate remboursement totals
        $totalMontant = $items->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['montant']);
        });

        $totalTVA = $items->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['tva']);
        });

        $totalDroitTimbre = $items->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['droit_timbre']);
        });

        $totalGeneral = $items->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['total']);
        });

        // Generate PDF
        $pdf = PDF::loadView('scentre.dra.etat_sortie', [
            'items' => $items,
            'dra' => $dra,
            'totalMontant' => number_format($totalMontant, 2, ',', ' '),
            'totalTVA' => number_format($totalTVA, 2, ',', ' '),
            'totalDroitTimbre' => number_format($totalDroitTimbre, 2, ',', ' '),
            'totalGeneral' => number_format($totalGeneral, 2, ',', ' '),
            'dateDebut' => '01/01/25',
            'dateFin' => '31/03/25',
        ]);

        return $pdf->download('etat_sortie_' . $n_dra . '.pdf');
    }

    public function exportAllDras()
    {

        $userCentreId = Auth::user()->id_centre;
        $allItems = collect();
        $centre = Centre::find($userCentreId);
        $centreType = $centre ? $centre->type_centre : 'Marine';
        $dras = Dra::with(['centre', 'factures.pieces', 'bonAchats.pieces'])
            ->where('id_centre', $userCentreId)
            ->orderBy('date_creation', 'desc')
            ->get();

        foreach ($dras as $dra) {
            $draItems = collect();
            $draTotalMontant = 0;
            $draTotalTVA = 0;
            $draTotalDroitTimbre = 0;
            $draTotalGeneral = 0;
            $draTotalPieces = 0;

            // Process factures
            foreach ($dra->factures as $facture) {
                $totalQuantity = 0;
                $montantHT = $facture->pieces->sum(function ($piece) use ($facture, &$totalQuantity) {
                    $quantity = $facture->pieces->find($piece->id_piece)->pivot->qte_f ?? 1;
                    $totalQuantity += $quantity;
                    return $piece->prix_piece * $quantity;
                });

                $tva = $facture->pieces->sum(function ($piece) use ($facture) {
                    $quantity = $facture->pieces->find($piece->id_piece)->pivot->qte_f ?? 1;
                    return ($piece->prix_piece * $quantity) * ($piece->tva ?? 0) / 100;
                });

                $droitTimbre = $facture->droit_timbre ?? 0;
                $total = $montantHT + $tva + $droitTimbre;

                $pieceNames = $facture->pieces->map(function ($piece) use ($facture) {
                    $quantity = $facture->pieces->find($piece->id_piece)->pivot->qte_f ?? 1;
                    return $piece->nom_piece . ' (x' . $quantity . ')';
                })->implode(', ');

                $draItems->push([
                    'n_dra' => $dra->n_dra,
                    'date_creation' => $dra->date_creation->format('d/m/Y'),
                    'libelle' => $pieceNames,
                    'montant' => number_format($montantHT, 2, ',', ' '),
                    'tva' => number_format($tva, 2, ',', ' '),
                    'droit_timbre' => number_format($droitTimbre, 2, ',', ' '),
                    'nombre_piece' => $totalQuantity,
                    'total' => number_format($total, 2, ',', ' '),
                    'etat' => $dra->etat,
                    'is_total' => false
                ]);

                // Accumulate DRA totals
                $draTotalMontant += $montantHT;
                $draTotalTVA += $tva;
                $draTotalDroitTimbre += $droitTimbre;
                $draTotalGeneral += $total;
                $draTotalPieces += $totalQuantity;
            }

            // Process bonAchats
            foreach ($dra->bonAchats as $bonAchat) {
                $totalQuantity = 0;
                $montantHT = $bonAchat->pieces->sum(function ($piece) use ($bonAchat, &$totalQuantity) {
                    $quantity = $bonAchat->pieces->find($piece->id_piece)->pivot->qte_ba ?? 1;
                    $totalQuantity += $quantity;
                    return $piece->prix_piece * $quantity;
                });

                $tva = $bonAchat->pieces->sum(function ($piece) use ($bonAchat) {
                    $quantity = $bonAchat->pieces->find($piece->id_piece)->pivot->qte_ba ?? 1;
                    return ($piece->prix_piece * $quantity) * ($piece->tva ?? 0) / 100;
                });

                $total = $montantHT + $tva;

                $pieceNames = $bonAchat->pieces->map(function ($piece) use ($bonAchat) {
                    $quantity = $bonAchat->pieces->find($piece->id_piece)->pivot->qte_ba ?? 1;
                    return $piece->nom_piece . ' (x' . $quantity . ')';
                })->implode(', ');

                $draItems->push([
                    'n_dra' => $dra->n_dra,
                    'date_creation' => $dra->date_creation->format('d/m/Y'),
                    'libelle' => $pieceNames,
                    'montant' => number_format($montantHT, 2, ',', ' '),
                    'tva' => number_format($tva, 2, ',', ' '),
                    'droit_timbre' => '0,00',
                    'nombre_piece' => $totalQuantity,
                    'total' => number_format($total, 2, ',', ' '),
                    'etat' => $dra->etat,
                    'is_total' => false
                ]);

                // Accumulate DRA totals
                $draTotalMontant += $montantHT;
                $draTotalTVA += $tva;
                $draTotalGeneral += $total;
                $draTotalPieces += $totalQuantity;
            }

            // Add DRA total row if there are items
            if ($draItems->isNotEmpty()) {
                $draItems->push([
                    'n_dra' => '',
                    'date_creation' => '',
                    'libelle' => 'TOTAL DRA ' . $dra->n_dra,
                    'montant' => number_format($draTotalMontant, 2, ',', ' '),
                    'tva' => number_format($draTotalTVA, 2, ',', ' '),
                    'droit_timbre' => number_format($draTotalDroitTimbre, 2, ',', ' '),
                    'nombre_piece' => $draTotalPieces,
                    'total' => number_format($draTotalGeneral, 2, ',', ' '),
                    'etat' => '',
                    'is_total' => true
                ]);
            }

            $allItems = $allItems->merge($draItems);
        }

        // Calculate grand totals
        $totalMontant = $allItems->where('is_total', false)->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['montant']);
        });

        $totalTVA = $allItems->where('is_total', false)->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['tva']);
        });

        $totalDroitTimbre = $allItems->where('is_total', false)->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['droit_timbre']);
        });

        $totalGeneral = $allItems->where('is_total', false)->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['total']);
        });

        $pdf = PDF::loadView('scentre.dra.etat_sortie_all', [
            'items' => $allItems,
            'totalMontant' => number_format($totalMontant, 2, ',', ' '),
            'totalTVA' => number_format($totalTVA, 2, ',', ' '),
            'totalDroitTimbre' => number_format($totalDroitTimbre, 2, ',', ' '),
            'totalGeneral' => number_format($totalGeneral, 2, ',', ' '),
            'id_centre' => $userCentreId,
             'centre_type' => $centreType,

        ]);

        return $pdf->download('etat_sortie_all_dras.pdf');
    }
}
