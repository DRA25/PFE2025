<?php

namespace App\Http\Controllers;

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


class ExportController extends Controller
{
    public function exportAllDras()
    {
        $userCentreId = Auth::user()->id_centre;
        $allItems = collect();
        $centre = Centre::find($userCentreId);
        $centreType = $centre ? $centre->type_centre : 'Marine';
        $centreCode = $centre ? $centre->id_centre : 'N/A';
        $centreSeuil = $centre ? (float) $centre->seuil_centre : 0.00;

        $dras = Dra::with([
            'centre',
            'factures.pieces' => fn($query) => $query->withPivot('qte_f', 'prix_piece'),
            'factures.prestations' => fn($query) => $query->withPivot('qte_fpr', 'prix_prest'),
            'factures.charges' => fn($query) => $query->withPivot('qte_fc', 'prix_charge'),
            'factures.fournisseur',
            'bonAchats.pieces' => fn($query) => $query->withPivot('qte_ba', 'prix_piece'),
            'bonAchats.fournisseur',
            'remboursements.encaissements'
        ])
            ->where('id_centre', $userCentreId)
            ->orderBy('n_dra', 'asc')
            ->get();

        $firstDate = $dras->first() ? Carbon::parse($dras->first()->date_creation) : now();
        $lastDate = $dras->last() ? Carbon::parse($dras->last()->date_creation) : now();

        foreach ($dras as $dra) {
            $draItems = collect();
            $draTotalDecaissement = 0;
            $draTotalEncaissement = 0;

            foreach ($dra->factures as $facture) {
                $piecesTotal = $facture->pieces->sum(function ($piece) {
                    $quantity = $piece->pivot->qte_f ?? 1;
                    $price = $piece->pivot->prix_piece ?? 0;
                    return ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);
                });

                $prestationsTotal = $facture->prestations->sum(function ($prestation) {
                    $quantity = $prestation->pivot->qte_fpr ?? 1;
                    $price = $prestation->pivot->prix_prest ?? 0;
                    return ($price * $quantity) * (1 + ($prestation->tva ?? 0) / 100);
                });

                $chargesTotal = $facture->charges->sum(function ($charge) {
                    $quantity = $charge->pivot->qte_fc ?? 1;
                    $price = $charge->pivot->prix_charge ?? 0; // Corrected to get price from pivot
                    return ($price * $quantity) * (1 + ($charge->tva ?? 0) / 100);
                });

                $droitTimbre = $facture->droit_timbre ?? 0;
                $totalAmount = $piecesTotal + $prestationsTotal + $chargesTotal + $droitTimbre;

                $pieceNames = $facture->pieces->map(function ($piece) {
                    $quantity = $piece->pivot->qte_f ?? 1;
                    return $piece->nom_piece . ' (x' . $quantity.')';
                })->implode(', ');

                $prestationNames = $facture->prestations->map(function ($prestation) {
                    $quantity = $prestation->pivot->qte_fpr ?? 1;
                    return $prestation->nom_prest . ' (x' . $quantity . ')';
                })->implode(', ');

                $chargeNames = $facture->charges->map(function ($charge) {
                    $quantity = $charge->pivot->qte_fc ?? 1;
                    return $charge->nom_charge . ' (x' . $quantity . ')';
                })->implode(', ');

                $libelle = implode(', ', array_filter([$pieceNames, $prestationNames, $chargeNames]));
                $fournisseurName = $facture->fournisseur ? $facture->fournisseur->nom_fourn : 'Non spécifié';

                $draItems->push([
                    'n_dra' => $dra->n_dra,
                    'n_bon' => 'Facture ' . $facture->n_facture,
                    'date_bon' => Carbon::parse($facture->date_facture)->format('d/m/Y'),
                    'libelle' => $libelle,
                    'fournisseur' => $fournisseurName,
                    'encaissement' => '',
                    'decaissement' => number_format($totalAmount, 2, ',', ' '),
                    'obs' => '',
                    'is_total' => false
                ]);

                $draTotalDecaissement += $totalAmount;
            }

            foreach ($dra->bonAchats as $bonAchat) {
                $baPiecesTotal = $bonAchat->pieces->sum(function ($piece) {
                    $quantity = $piece->pivot->qte_ba ?? 1;
                    $price = $piece->pivot->prix_piece ?? 0;
                    return ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);
                });

                $totalAmount = $baPiecesTotal;
                $baPieceNames = $bonAchat->pieces->map(function ($piece) {
                    $quantity = $piece->pivot->qte_ba ?? 1;
                    return $piece->nom_piece . ' (x' . $quantity . ')';
                })->implode(', ');

                $libelle = implode(', ', array_filter([$baPieceNames]));
                $fournisseurName = $bonAchat->fournisseur ? $bonAchat->fournisseur->nom_fourn : 'Non spécifié';

                $draItems->push([
                    'n_dra' => $dra->n_dra,
                    'n_bon' => 'Bon Achat ' . $bonAchat->n_ba,
                    'date_bon' => Carbon::parse($bonAchat->date_ba)->format('d/m/Y'),
                    'libelle' => $libelle,
                    'fournisseur' => $fournisseurName,
                    'encaissement' => '',
                    'decaissement' => number_format($totalAmount, 2, ',', ' '),
                    'obs' => '',
                    'is_total' => false
                ]);

                $draTotalDecaissement += $totalAmount;
            }

            foreach ($dra->remboursements as $remboursement) {
                $draTotalEncaissement += $remboursement->encaissements->sum('montant_enc');
            }

            if ($draTotalEncaissement > 0) {
                $draItems->push([
                    'n_dra' => $dra->n_dra,
                    'n_bon' => '',
                    'date_bon' => $dra->date_creation->format('d/m/Y'),
                    'libelle' => 'Encaissement remboursement',
                    'fournisseur' => '',
                    'encaissement' => number_format($draTotalEncaissement, 2, ',', ' '),
                    'decaissement' => '',
                    'obs' => '',
                    'is_total' => false
                ]);
            }

            if ($draItems->isNotEmpty()) {
                $draItems->push([
                    'n_dra' => '',
                    'n_bon' => '',
                    'date_bon' => '',
                    'libelle' => 'Total DRA',
                    'fournisseur' => '',
                    'encaissement' => number_format($draTotalEncaissement, 2, ',', ' '),
                    'decaissement' => number_format($draTotalDecaissement, 2, ',', ' '),
                    'obs' => '',
                    'is_total' => true
                ]);
            }

            $allItems = $allItems->merge($draItems);
        }

        $rawTotalEncaissement = $allItems->where('is_total', false)->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['encaissement']);
        });

        $rawTotalDecaissement = $allItems->where('is_total', false)->sum(function ($item) {
            return (float) str_replace([' ', ','], ['', '.'], $item['decaissement']);
        });

        $rawBalancePeriod = $rawTotalEncaissement - $rawTotalDecaissement;
        $calculatedValue = $centreSeuil + $rawBalancePeriod;

        $pdf = PDF::loadView('scentre.dra.export_brouillard', [
            'items' => $allItems,
            'totalEncaissement' => number_format($rawTotalEncaissement, 2, ',', ' '),
            'totalDecaissement' => number_format($rawTotalDecaissement, 2, ',', ' '),
            'balancePeriod' => number_format($rawBalancePeriod, 2, ',', ' '),
            'centreseuil' => number_format($centreSeuil, 2, ',', ' '),
            'calculatedResult' => number_format($calculatedValue, 2, ',', ' '),
            'id_centre' => $userCentreId,
            'centre_type' => $centreType,
            'centre_code' => $centreCode,
            'periode_debut' => $firstDate->format('d/m/Y'),
            'periode_fin' => $lastDate->format('d/m/Y'),
            'exercice' => $firstDate->format('Y'),
        ]);

        return $pdf->download('brouillard_caisse_regie.pdf');
    }


    public function exportEtatTrimestriel()
    {
        $user = Auth::user();
        if (!$user->id_centre) {
            abort(403, 'User is not associated with any centre');
        }

        $centreCode = $user->id_centre;
        $formattedCentreCode = '1' . $centreCode;
        $centre = Centre::find($centreCode);
        $centreType = $centre ? $centre->type_centre : 'Inconnu';
        $startDate = Carbon::now()->startOfQuarter();
        $endDate = Carbon::now()->endOfQuarter();
        $items = collect();

        $processDocument = function ($document, $isFacture = true) use (&$items, $formattedCentreCode) {
            $date = $isFacture
                ? Carbon::parse($document->date_facture)->format('d/m/Y')
                : Carbon::parse($document->date_ba)->format('d/m/Y');

            $addedTimbre = false; // Flag to ensure droit_timbre is added only once per facture

            // Process pieces
            if ($document->relationLoaded('pieces')) {
                foreach ($document->pieces as $piece) {
                    if (!$piece) {
                        continue;
                    }

                    $quantity = $isFacture ? ($piece->pivot->qte_f ?? 1) : ($piece->pivot->qte_ba ?? 1);
                    $price = $piece->pivot->prix_piece ?? 0;
                    $montant = ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);

                    // Add droit_timbre to the first item processed for a Facture
                    if ($isFacture && !$addedTimbre) {
                        $montant += $document->droit_timbre ?? 0;
                        $addedTimbre = true;
                    }

                    $items->push([
                        'item' => $items->count() + 1,
                        'libelle' => $piece->nom_piece ?? 'Pièce sans nom',
                        'compte_charge' => $piece->compteGeneral->code ?? 'N/A',
                        'date' => $date,
                        'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                        'cds' => $formattedCentreCode,
                        'fourniture_consommable' => number_format($montant, 2, ',', ' '),
                        'travaux_prestations' => '',
                        'autres' => ''
                    ]);
                }
            }

            // Process prestations
            if ($isFacture && $document->relationLoaded('prestations')) {
                foreach ($document->prestations as $prestation) {
                    if (!$prestation) {
                        continue;
                    }

                    $quantity = $prestation->pivot->qte_fpr ?? 1;
                    $price = $prestation->pivot->prix_prest ?? 0;
                    $montant = ($price * $quantity) * (1 + ($prestation->tva ?? 0) / 100);

                    // Add droit_timbre if it hasn't been added yet (i.e., no pieces were processed)
                    if ($isFacture && !$addedTimbre) {
                        $montant += $document->droit_timbre ?? 0;
                        $addedTimbre = true;
                    }

                    $items->push([
                        'item' => $items->count() + 1,
                        'libelle' => $prestation->nom_prest ?? 'Prestation sans nom',
                        'compte_charge' => $prestation->compteGeneral->code ?? 'N/A',
                        'date' => $date,
                        'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                        'cds' => $formattedCentreCode,
                        'fourniture_consommable' => '',
                        'travaux_prestations' => number_format($montant, 2, ',', ' '),
                        'autres' => ''
                    ]);
                }
            }

            // Process charges
            if ($isFacture && $document->relationLoaded('charges')) {
                foreach ($document->charges as $charge) {
                    if (!$charge) {
                        continue;
                    }

                    $quantity = $charge->pivot->qte_fc ?? 1;
                    $price = $charge->pivot->prix_charge ?? 0; // Corrected to use pivot price
                    $montant = ($price * $quantity) * (1 + ($charge->tva ?? 0) / 100);

                    // Add droit_timbre if it hasn't been added yet (i.e., no pieces or prestations were processed)
                    if ($isFacture && !$addedTimbre) {
                        $montant += $document->droit_timbre ?? 0;
                        $addedTimbre = true;
                    }

                    $items->push([
                        'item' => $items->count() + 1,
                        'libelle' => $charge->nom_charge ?? 'Charge sans nom',
                        'compte_charge' => $charge->compteGeneral->code ?? 'N/A',
                        'date' => $date,
                        'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                        'cds' => $formattedCentreCode,
                        'fourniture_consommable' => '',
                        'travaux_prestations' => '',
                        'autres' => number_format($montant, 2, ',', ' ')
                    ]);
                }
            }
            // The logic to add Droit de Timbre as a separate line item was removed in a previous iteration
            // and is not being re-added here as per your request.
        };

        $factures = Facture::with([
            'pieces' => function ($query) { $query->withPivot('qte_f', 'prix_piece')->with('compteGeneral:code,libelle'); },
            'prestations' => function ($query) { $query->withPivot('qte_fpr', 'prix_prest')->with('compteGeneral:code,libelle'); },
            'charges' => function ($query) { $query->withPivot('qte_fc', 'prix_charge')->with('compteGeneral:code,libelle'); },
            'fournisseur',
            'dra'
        ])
            ->whereHas('dra', fn($q) => $q->where('id_centre', $centreCode))
            ->whereBetween('date_facture', [$startDate, $endDate])
            ->get();

        foreach ($factures as $facture) {
            $processDocument($facture, true);
        }

        $bonAchats = BonAchat::with([
            'pieces' => function ($query) { $query->withPivot('qte_ba', 'prix_piece')->with('compteGeneral:code,libelle'); },
            'fournisseur',
            'dra'
        ])
            ->whereHas('dra', fn($q) => $q->where('id_centre', $centreCode))
            ->whereBetween('date_ba', [$startDate, $endDate])
            ->get();

        foreach ($bonAchats as $bonAchat) {
            $processDocument($bonAchat, false);
        }

        $calculateTotal = fn($field) =>
        $items->sum(fn($item) =>
        (float) str_replace([' ', ','], ['', '.'], $item[$field] ?? '0'));

        $totalFourniture = $calculateTotal('fourniture_consommable');
        $totalTravaux = $calculateTotal('travaux_prestations');
        $totalAutres = $calculateTotal('autres');
        $grandTotal = $totalFourniture + $totalTravaux + $totalAutres;

        $pdf = PDF::loadView('exports.etat_trimestriel', [
            'items' => $items,
            'centreCode' => $formattedCentreCode,
            'centreType' => $centreType,
            'trimestre' => 'Du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y'),
            'totalFourniture' => number_format($totalFourniture, 2, ',', ' '),
            'totalTravaux' => number_format($totalTravaux, 2, ',', ' '),
            'totalAutres' => number_format($totalAutres, 2, ',', ' '),
            'grandTotal' => number_format($grandTotal, 2, ',', ' '),
            'currentDate' => Carbon::now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('etat_trimestriel_' . $formattedCentreCode . '_' . $startDate->format('Y-m') . '.pdf');
    }


    public function exportEtatTrimestrielAllCentres()
    {
        $centres = Centre::all();
        $startDate = Carbon::now()->startOfQuarter();
        $endDate = Carbon::now()->endOfQuarter();
        $allCentreData = [];
        $globalTotals = [
            'totalFourniture' => 0,
            'totalTravaux' => 0,
            'totalAutres' => 0,
            'grandTotal' => 0
        ];

        foreach ($centres as $centre) {
            $centreCode = $centre->id_centre;
            $formattedCentreCode = '1' . $centreCode;
            $items = collect();

            $processDocuments = function ($documents, $isFacture = true) use (&$items, $formattedCentreCode) {
                foreach ($documents as $document) {
                    if (!$document) {
                        Log::warning('Invalid document encountered in exportEtatTrimestrielAllCentres');
                        continue;
                    }

                    try {
                        $date = $isFacture
                            ? ($document->date_facture ? Carbon::parse($document->date_facture)->format('d/m/Y') : 'Date invalide')
                            : ($document->date_ba ? Carbon::parse($document->date_ba)->format('d/m/Y') : 'Date invalide');

                        $addedTimbre = false; // Flag for each document to ensure droit_timbre is added only once

                        // Process pieces
                        if ($document->relationLoaded('pieces')) {
                            foreach ($document->pieces as $piece) {
                                if (!$piece) {
                                    continue;
                                }

                                $quantity = $isFacture
                                    ? ($piece->pivot->qte_f ?? 1)
                                    : ($piece->pivot->qte_ba ?? 1);
                                $price = $piece->pivot->prix_piece ?? 0;
                                $montant = ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);

                                // Add droit_timbre to the first item processed for a Facture
                                if ($isFacture && !$addedTimbre) {
                                    $montant += $document->droit_timbre ?? 0;
                                    $addedTimbre = true;
                                }

                                $items->push([
                                    'item' => $items->count() + 1,
                                    'libelle' => $piece->nom_piece ?? 'Pièce sans nom',
                                    'compte_charge' => $piece->compteGeneral->code ?? 'N/A',
                                    'date' => $date,
                                    'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                                    'cds' => $formattedCentreCode,
                                    'fourniture_consommable' => number_format($montant, 2, ',', ' '),
                                    'travaux_prestations' => '',
                                    'autres' => ''
                                ]);
                            }
                        }

                        // Process prestations
                        if ($isFacture && $document->relationLoaded('prestations')) {
                            foreach ($document->prestations as $prestation) {
                                if (!$prestation) {
                                    continue;
                                }

                                $quantity = $prestation->pivot->qte_fpr ?? 1;
                                $price = $prestation->pivot->prix_prest ?? 0;
                                $montant = ($price * $quantity) * (1 + ($prestation->tva ?? 0) / 100);

                                // Add droit_timbre if it hasn't been added yet
                                if ($isFacture && !$addedTimbre) {
                                    $montant += $document->droit_timbre ?? 0;
                                    $addedTimbre = true;
                                }

                                $items->push([
                                    'item' => $items->count() + 1,
                                    'libelle' => $prestation->nom_prest ?? 'Prestation sans nom',
                                    'compte_charge' => $prestation->compteGeneral->code ?? 'N/A',
                                    'date' => $date,
                                    'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                                    'cds' => $formattedCentreCode,
                                    'fourniture_consommable' => '',
                                    'travaux_prestations' => number_format($montant, 2, ',', ' '),
                                    'autres' => ''
                                ]);
                            }
                        }

                        // Process charges
                        if ($isFacture && $document->relationLoaded('charges')) {
                            foreach ($document->charges as $charge) {
                                if (!$charge) {
                                    continue;
                                }

                                $quantity = $charge->pivot->qte_fc ?? 1;
                                $price = $charge->pivot->prix_charge ?? 0; // Corrected to use pivot price
                                $montant = ($price * $quantity) * (1 + ($charge->tva ?? 0) / 100);

                                // Add droit_timbre if it hasn't been added yet
                                if ($isFacture && !$addedTimbre) {
                                    $montant += $document->droit_timbre ?? 0;
                                    $addedTimbre = true;
                                }

                                $items->push([
                                    'item' => $items->count() + 1,
                                    'libelle' => $charge->nom_charge ?? 'Charge sans nom',
                                    'compte_charge' => $charge->compteGeneral->code ?? 'N/A',
                                    'date' => $date,
                                    'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                                    'cds' => $formattedCentreCode,
                                    'fourniture_consommable' => '',
                                    'travaux_prestations' => '',
                                    'autres' => number_format($montant, 2, ',', ' ')
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Error processing document in exportEtatTrimestrielAllCentres', [
                            'document_id' => $document->id ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                        continue;
                    }
                }
            };

            try {
                $factures = Facture::with([
                    'pieces' => function ($query) { $query->withPivot('qte_f', 'prix_piece')->with('compteGeneral:code,libelle'); },
                    'prestations' => function ($query) { $query->withPivot('qte_fpr', 'prix_prest')->with('compteGeneral:code,libelle'); },
                    'charges' => function ($query) { $query->withPivot('qte_fc', 'prix_charge')->with('compteGeneral:code,libelle'); },
                    'fournisseur',
                    'dra'
                ])
                    ->whereHas('dra', fn($q) => $q->where('id_centre', $centreCode))
                    ->whereBetween('date_facture', [$startDate, $endDate])
                    ->get();

                $bonAchats = BonAchat::with([
                    'pieces' => function ($query) { $query->withPivot('qte_ba', 'prix_piece')->with('compteGeneral:code,libelle'); },
                    'fournisseur',
                    'dra'
                ])
                    ->whereHas('dra', fn($q) => $q->where('id_centre', $centreCode))
                    ->whereBetween('date_ba', [$startDate, $endDate])
                    ->get();

                // Process documents for the current centre
                $processDocuments($factures, true);
                $processDocuments($bonAchats, false);

                $calculateTotalCentre = fn($field) =>
                $items->sum(fn($item) =>
                (float) str_replace([' ', ','], ['', '.'], $item[$field] ?? '0'));

                $totalFournitureCentre = $calculateTotalCentre('fourniture_consommable');
                $totalTravauxCentre = $calculateTotalCentre('travaux_prestations');
                $totalAutresCentre = $calculateTotalCentre('autres');
                $grandTotalCentre = $totalFournitureCentre + $totalTravauxCentre + $totalAutresCentre;

                $allCentreData[] = [
                    'centre' => $centre,
                    'centreType' => $centre->type_centre ?? 'N/A',
                    'centreCode' => $centre->code_centre ?? $centre->id_centre,
                    'items' => $items,
                    'totals' => [
                        'totalFourniture' => number_format($totalFournitureCentre, 2, ',', ' '),
                        'totalTravaux' => number_format($totalTravauxCentre, 2, ',', ' '),
                        'totalAutres' => number_format($totalAutresCentre, 2, ',', ' '),
                        'grandTotal' => number_format($grandTotalCentre, 2, ',', ' '),
                    ],
                ];

                $globalTotals['totalFourniture'] += $totalFournitureCentre;
                $globalTotals['totalTravaux'] += $totalTravauxCentre;
                $globalTotals['totalAutres'] += $totalAutresCentre;
                $globalTotals['grandTotal'] += $grandTotalCentre;

            } catch (\Exception $e) {
                Log::error('Error processing centre in exportEtatTrimestrielAllCentres', [
                    'centre_id' => $centreCode,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        $pdf = PDF::loadView('exports.etat_trimestriel_all_centres', [
            'allCentreData' => $allCentreData,
            'globalTotals' => array_map(fn($total) => number_format($total, 2, ',', ' '), $globalTotals),
            'trimestre' => 'Du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y'),
            'currentDate' => Carbon::now()->format('d/m/Y H:i'),
        ]);

        return $pdf->download('etat_trimestriel_all_centres_' . $startDate->format('Y-m') . '.pdf');
    }

    public function exportDemandeDerogation($draNumber)
    {
        // Authenticate and validate user
        $user = Auth::user();
        if (!$user->id_centre) {
            abort(403, 'User is not associated with any centre');
        }

        // Get centre information
        $centreCode = $user->id_centre;
        $formattedCentreCode = '1' . $centreCode;
        $centre = Centre::find($centreCode);
        $centreType = $centre ? $centre->type_centre : 'Inconnu';

        // Find the specific DRA by its number
        $dra = DB::table('dras')
            ->where('id_centre', $centreCode)
            ->where('n_dra', $draNumber)
            ->first();

        if (!$dra) {
            abort(404, 'DRA not found');
        }

        // Create reference number (n_dra/year)
        $reference = $dra->n_dra . '/' . Carbon::now()->format('Y');

        $items = collect();

        // Document processing function
        $processDocument = function ($document, $isFacture = true) use (&$items, $formattedCentreCode) {
            $date = $isFacture
                ? Carbon::parse($document->date_facture)->format('d/m/Y')
                : Carbon::parse($document->date_ba)->format('d/m/Y');

            $addedTimbre = false;

            // Process pieces (consumables)
            if ($document->relationLoaded('pieces')) {
                foreach ($document->pieces as $piece) {
                    if (!$piece) continue;

                    $quantity = $isFacture ? ($piece->pivot->qte_f ?? 1) : ($piece->pivot->qte_ba ?? 1);
                    $price = $piece->pivot->prix_piece ?? 0;
                    $montant = ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);

                    if ($isFacture && !$addedTimbre) {
                        $montant += $document->droit_timbre ?? 0;
                        $addedTimbre = true;
                    }

                    $items->push([
                        'item' => $items->count() + 1,
                        'libelle' => $piece->nom_piece ?? 'Pièce sans nom',
                        'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                        'cds' => $formattedCentreCode,
                        'fourniture_consommable' => number_format($montant, 2, ',', ' '),
                        'travaux_prestations' => '',
                        'autres' => ''
                    ]);
                }
            }

            // Process services
            if ($isFacture && $document->relationLoaded('prestations')) {
                foreach ($document->prestations as $prestation) {
                    if (!$prestation) continue;

                    $quantity = $prestation->pivot->qte_fpr ?? 1;
                    $price = $prestation->pivot->prix_prest ?? 0;
                    $montant = ($price * $quantity) * (1 + ($prestation->tva ?? 0) / 100);

                    if ($isFacture && !$addedTimbre) {
                        $montant += $document->droit_timbre ?? 0;
                        $addedTimbre = true;
                    }

                    $items->push([
                        'item' => $items->count() + 1,
                        'libelle' => $prestation->nom_prest ?? 'Prestation sans nom',
                        'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                        'cds' => $formattedCentreCode,
                        'fourniture_consommable' => '',
                        'travaux_prestations' => number_format($montant, 2, ',', ' '),
                        'autres' => ''
                    ]);
                }
            }

            // Process other expenses
            if ($isFacture && $document->relationLoaded('charges')) {
                foreach ($document->charges as $charge) {
                    if (!$charge) continue;

                    $quantity = $charge->pivot->qte_fc ?? 1;
                    $price = $charge->pivot->prix_charge ?? 0;
                    $montant = ($price * $quantity) * (1 + ($charge->tva ?? 0) / 100);

                    if ($isFacture && !$addedTimbre) {
                        $montant += $document->droit_timbre ?? 0;
                        $addedTimbre = true;
                    }

                    $items->push([
                        'item' => $items->count() + 1,
                        'libelle' => $charge->nom_charge ?? 'Charge sans nom',
                        'fournisseur' => $document->fournisseur->nom_fourn ?? 'Non spécifié',
                        'cds' => $formattedCentreCode,
                        'fourniture_consommable' => '',
                        'travaux_prestations' => '',
                        'autres' => number_format($montant, 2, ',', ' ')
                    ]);
                }
            }
        };

        // Get invoices for this DRA
        $factures = Facture::with([
            'pieces' => function ($query) { $query->withPivot('qte_f', 'prix_piece'); },
            'prestations' => function ($query) { $query->withPivot('qte_fpr', 'prix_prest'); },
            'charges' => function ($query) { $query->withPivot('qte_fc', 'prix_charge'); },
            'fournisseur',
            'dra'
        ])
            ->whereHas('dra', fn($q) => $q->where('n_dra', $draNumber))
            ->get();

        foreach ($factures as $facture) {
            $processDocument($facture, true);
        }

        // Get purchase orders for this DRA
        $bonAchats = BonAchat::with([
            'pieces' => function ($query) { $query->withPivot('qte_ba', 'prix_piece'); },
            'fournisseur',
            'dra'
        ])
            ->whereHas('dra', fn($q) => $q->where('n_dra', $draNumber))
            ->get();

        foreach ($bonAchats as $bonAchat) {
            $processDocument($bonAchat, false);
        }

        // Calculate totals
        $calculateTotal = function ($field) use ($items) {
            return $items->sum(function ($item) use ($field) {
                $value = $item[$field] ?? '0';
                return (float) str_replace([' ', ','], ['', '.'], $value);
            });
        };

        $totalFourniture = $calculateTotal('fourniture_consommable');
        $totalTravaux = $calculateTotal('travaux_prestations');
        $totalAutres = $calculateTotal('autres');
        $grandTotal = $totalFourniture + $totalTravaux + $totalAutres;

        // Generate PDF
        $pdf = PDF::loadView('exports.demande_derogation', [
            'items' => $items,
            'centreCode' => $formattedCentreCode,
            'centreType' => $centreType,
            'reference' => $reference,
            'totalFourniture' => number_format($totalFourniture, 2, ',', ' '),
            'totalTravaux' => number_format($totalTravaux, 2, ',', ' '),
            'totalAutres' => number_format($totalAutres, 2, ',', ' '),
            'grandTotal' => number_format($grandTotal, 2, ',', ' '),
            'currentDate' => Carbon::now()->format('d/m/Y'),
        ]);

        return $pdf->download('demande_derogation_' . $draNumber . '_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }
}
