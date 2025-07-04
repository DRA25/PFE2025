<?php

namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\BonAchat;
use App\Models\Centre;
use App\Models\Dra;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;
use Inertia\Inertia;


class DraController extends Controller
{

    public function index(Request $request) // Inject Request
    {
        $userCentreId = Auth::user()->id_centre;

        // Get trimestre and year from the request, with default to current trimester/year
        $selectedTrimestre = $request->input('trimestre');
        $selectedYear = $request->input('year');

        $drasQuery = Dra::with('centre')
            ->where('id_centre', $userCentreId);

        // Apply trimester and year filter if both are provided
        if ($selectedTrimestre && $selectedYear) {
            $startDate = null;
            $endDate = null;

            switch ($selectedTrimestre) {
                case '1': // January, February, March
                    $startDate = "$selectedYear-01-01";
                    $endDate = "$selectedYear-03-31";
                    break;
                case '2': // April, May, June
                    $startDate = "$selectedYear-04-01";
                    $endDate = "$selectedYear-06-30";
                    break;
                case '3': // July, August, September
                    $startDate = "$selectedYear-07-01";
                    $endDate = "$selectedYear-09-30";
                    break;
                case '4': // October, November, December
                    $startDate = "$selectedYear-10-01";
                    $endDate = "$selectedYear-12-31";
                    break;
                // 'Trimestre actuel' case is handled by not applying the filter
            }

            if ($startDate && $endDate) {
                $drasQuery->whereBetween('date_creation', [$startDate, $endDate]);
            }
        } else {
            // Default to current trimester if no specific trimester/year is selected
            // This mirrors the frontend's 'Trimestre actuel' logic
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $currentTrimestre = null;
            if ($currentMonth >= 1 && $currentMonth <= 3) {
                $currentTrimestre = '1';
            } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
                $currentTrimestre = '2';
            } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
                $currentTrimestre = '3';
            } elseif ($currentMonth >= 10 && $currentMonth <= 12) {
                $currentTrimestre = '4';
            }

            if ($currentTrimestre) {
                $startDate = null;
                $endDate = null;
                switch ($currentTrimestre) {
                    case '1':
                        $startDate = "$currentYear-01-01";
                        $endDate = "$currentYear-03-31";
                        break;
                    case '2':
                        $startDate = "$currentYear-04-01";
                        $endDate = "$currentYear-06-30";
                        break;
                    case '3':
                        $startDate = "$currentYear-07-01";
                        $endDate = "$currentYear-09-30";
                        break;
                    case '4':
                        $startDate = "$currentYear-10-01";
                        $endDate = "$currentYear-12-31";
                        break;
                }
                if ($startDate && $endDate) {
                    $drasQuery->whereBetween('date_creation', [$startDate, $endDate]);
                }
            }
        }


        $dras = $drasQuery->orderBy('created_at', 'desc')
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
            'id_centre' => $userCentreId,
            'selectedTrimestre' => $selectedTrimestre, // Pass back the selected trimester
            'selectedYear' => $selectedYear,       // Pass back the selected year
        ]);
    }


    public function show($n_dra)
    {
        $userCentreId = Auth::user()->id_centre;
        $dra = Dra::with('centre')->where('n_dra', $n_dra)->firstOrFail();

        // Ensure the user is authorized to view this DRA
        if ($dra->id_centre !== $userCentreId) {
            abort(403, 'Unauthorized action.');
        }

        // Load factures with related data and pivot prices.
        $factures = $dra->factures()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces' => function ($query) {
                    $query->withPivot('qte_f', 'prix_piece');
                },
                'prestations' => function ($query) {
                    $query->withPivot('qte_fpr', 'prix_prest');
                },
                'charges' => function ($query) {
                    $query->withPivot('qte_fc', 'prix_charge');
                }
            ])
            ->get()
            ->map(function ($facture) {
                $facture->montant = $this->calculateMontant($facture);
                return $facture;
            });

        // Load bonAchats with related data and pivot prices.
        $bonAchats = $dra->bonAchats()
            ->with([
                'fournisseur:id_fourn,nom_fourn',
                'pieces' => function ($query) {
                    $query->withPivot('qte_ba', 'prix_piece');
                },
            ])
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
                'motif' => $dra->motif,
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

        $count = Dra::where('id_centre', $centreId)->count() + 1;
        $n_dra = $centreId . str_pad($count, 6, '0', STR_PAD_LEFT);

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
        $totalDra = 0; // Initialize totalDra for new DRA

        // This condition is likely intended to check if the *initial* DRA creation exceeds threshold
        // but since totalDra is 0, it won't prevent creation. Logic might need adjustment if totalDra
        // is supposed to be calculated from initial items in a single form.
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

        if ($totalDra > 0) { // This will not run as totalDra is 0
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
        try {
            // Log the incoming request data for debugging
            Log::info('DRA Update Request Data (DraController):', $request->all());

            // Validate the request data
            $validatedData = $request->validate([
                'etat' => ['required', 'string', Rule::in(['cloture', 'refuse', 'accepte'])],
                'motif' => ['nullable', 'string', 'max:500', 'required_if:etat,refuse'], // Motif is required if etat is 'refuse'
            ]);

            // Log the validated data for debugging
            Log::info('DRA Update Validated Data (DraController):', $validatedData);

            // Update the DRA's etat and motif
            $dra->etat = $validatedData['etat'];
            $dra->motif = $validatedData['motif'] ?? null; // Set motif to null if not provided or not 'refuse'

            // Save the changes to the database
            $dra->save();

            // Log success
            Log::info('DRA ' . $dra->n_dra . ' updated successfully to etat: ' . $dra->etat . ' and motif: ' . ($dra->motif ?? 'N/A'));

            // Redirect to the index page with a success message
            return redirect()->route('scf.dras.index')->with('success', 'DRA mis à jour avec succès.');

        } catch (ValidationException $e) {
            // If validation fails, Inertia will automatically send errors to the frontend.
            // Log them here for server-side debugging.
            Log::error('Validation error updating DRA ' . $dra->n_dra . ': ' . json_encode($e->errors()));
            throw $e; // Re-throw to let Inertia handle the response
        } catch (Exception $e) {
            // Catch any other unexpected errors
            Log::error('Error updating DRA ' . $dra->n_dra . ': ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // You might want to return a more generic error response for unexpected issues
            return redirect()->back()->withErrors(['error' => 'Une erreur inattendue est survenue lors de la mise à jour du DRA.']);
        }
    
    }

    public function destroy($n_dra)
    {
        DB::beginTransaction();

        try {
            $userCentreId = Auth::user()->id_centre;
            $dra = Dra::with([
                'bonAchats.pieces' => fn($query) => $query->withPivot('qte_ba', 'prix_piece'), // Load pivot for BA pieces
                'factures.pieces' => fn($query) => $query->withPivot('qte_f', 'prix_piece'),   // Load pivot for Facture pieces
                'factures.prestations' => fn($query) => $query->withPivot('qte_fpr', 'prix_prest'), // Load pivot for Facture prestations
                'factures.charges' => fn($query) => $query->withPivot('qte_fc', 'prix_charge'),   // Load pivot for Facture charges
                'centre'
            ])->where('n_dra', $n_dra)->firstOrFail();

            if ($dra->id_centre !== $userCentreId) {
                abort(403, 'Unauthorized action.');
            }

            if ($dra->etat !== 'actif') {
                return back()->withErrors(['error' => 'Seuls les DRAs actifs peuvent être supprimés']);
            }

            $montantToRestore = '0';

            // Calculate total montant to restore from associated bonAchats
            foreach ($dra->bonAchats as $bonAchat) {
                $montantToRestore = bcadd($montantToRestore, (string)$this->calculateMontant($bonAchat), 2);
            }

            // Calculate total montant to restore from associated factures
            foreach ($dra->factures as $facture) {
                $montantToRestore = bcadd($montantToRestore, (string)$this->calculateMontant($facture), 2);
            }

            // Detach related items before deleting
            foreach ($dra->bonAchats as $bonAchat) {
                $bonAchat->pieces()->detach();
            }
            foreach ($dra->factures as $facture) {
                $facture->pieces()->detach();
                $facture->prestations()->detach();
                $facture->charges()->detach();
            }

            $dra->factures()->delete();
            $dra->bonAchats()->delete();
            $dra->delete();

            // Restore the total amount to the centre's available montant_disponible
            $dra->centre->increment('montant_disponible', (float)$montantToRestore);

            DB::commit();

            return redirect()->route('scentre.dras.index')
                ->with('success', 'DRA supprimé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting DRA: ' . $e->getMessage(), ['n_dra' => $n_dra, 'error' => $e]);
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }


    public function close(Request $request, Dra $dra) // Inject Request
    {
        $userCentreId = Auth::user()->id_centre;

        if ($dra->id_centre !== $userCentreId) {
            abort(403, 'Unauthorized action.');
        }

        $normalizedEtat = strtolower($dra->etat);

        if ($normalizedEtat !== 'refuse' && $normalizedEtat !== 'actif') {
            return back()->withErrors([
                'etat' => 'Seuls les DRAs actifs ou refusés peuvent être clôturés'
            ]);
        }

        // Get the motif from the request, defaulting to null
        // This ensures that if the frontend sends motif: null, it's captured.
        // If the motif is not sent, it will also default to null.
        $motif = $request->input('motif', null);

        $dra->update([
            'etat' => 'cloture',
            'motif' => $motif, // Set the motif based on the request (will be null from frontend)
        ]);

        return redirect()->route('scentre.dras.index')
            ->with('success', 'DRA clôturé avec succès');
    }


    protected function calculateMontant($model): float
    {
        $total = 0;

        // Calculate pieces total (HT + TVA) using pivot prices
        if ($model->relationLoaded('pieces')) {
            $total += $model->pieces->sum(function ($piece) use ($model) {
                $quantity = 0;
                $price = 0;

                if ($model instanceof \App\Models\BonAchat) {
                    $quantity = $piece->pivot->qte_ba ?? 0;
                    $price = $piece->pivot->prix_piece ?? 0;
                } elseif ($model instanceof \App\Models\Facture) {
                    $quantity = $piece->pivot->qte_f ?? 0;
                    $price = $piece->pivot->prix_piece ?? 0;
                }

                $tva = $piece->tva ?? 0;
                return ($price * $quantity) * (1 + $tva / 100);
            });
        }

        // Add prestations (HT + TVA) using pivot prices - Only for Facture
        if ($model instanceof \App\Models\Facture && $model->relationLoaded('prestations')) {
            $total += $model->prestations->sum(function ($prestation) {
                $quantity = $prestation->pivot->qte_fpr ?? 0;
                $price = $prestation->pivot->prix_prest ?? 0;
                $tva = $prestation->tva ?? 0;
                return ($price * $quantity) * (1 + $tva / 100);
            });
        }

        // Add charges (HT + TVA) using pivot prices - Only for Facture
        if ($model instanceof \App\Models\Facture && $model->relationLoaded('charges')) {
            $total += $model->charges->sum(function ($charge) {
                $quantity = $charge->pivot->qte_fc ?? 0;
                $price = $charge->pivot->prix_charge ?? 0; // Corrected to get price from pivot
                $tva = $charge->tva ?? 0;
                return ($price * $quantity) * (1 + $tva / 100);
            });
        }

        // If it's a Facture, add droit_timbre
        if ($model instanceof \App\Models\Facture) {
            $total += $model->droit_timbre ?? 0;
        }

        return (float) $total;
    }


}
