<?php

namespace App\Http\Controllers;

use App\Models\BonAchat;
use App\Models\Centre;
use App\Models\Dra;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ExportController extends Controller
{
    public function exportAllDras(Request $request)
    {
        $userCentreId = Auth::user()->id_centre;

        // Get trimestre and year from request (default to current quarter if not provided)
        $trimestre = $request->input('trimestre', 'current');
        $year = $request->input('year', date('Y'));

        // Calculate dates based on selected trimestre
        switch ($trimestre) {
            case '1':
                $startDate = Carbon::create($year, 1, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 3, 31)->endOfQuarter();
                break;
            case '2':
                $startDate = Carbon::create($year, 4, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 6, 30)->endOfQuarter();
                break;
            case '3':
                $startDate = Carbon::create($year, 7, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 9, 30)->endOfQuarter();
                break;
            case '4':
                $startDate = Carbon::create($year, 10, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 12, 31)->endOfQuarter();
                break;
            default: // current quarter
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                $trimestre = ceil(Carbon::now()->month / 3);
                $year = Carbon::now()->year;
                break;
        }

        $allItems = collect();
        $centre = Centre::find($userCentreId);
        $centreType = $centre ? $centre->type_centre : 'Marine';
        $centreCode = $centre ? $centre->id_centre : 'N/A';
        $centreSeuil = $centre ? (float) $centre->seuil_centre : 0.00;

        $dras = Dra::with([
            'centre',
            'factures' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('date_facture', [$startDate, $endDate])
                    ->with(['pieces' => function($query) {
                        $query->withPivot('qte_f', 'prix_piece');
                    }])
                    ->with(['prestations' => function($query) {
                        $query->withPivot('qte_fpr', 'prix_prest');
                    }])
                    ->with(['charges' => function($query) {
                        $query->withPivot('qte_fc', 'prix_charge');
                    }])
                    ->with('fournisseur');
            },
            'bonAchats' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('date_ba', [$startDate, $endDate])
                    ->with(['pieces' => function($query) {
                        $query->withPivot('qte_ba', 'prix_piece');
                    }])
                    ->with('fournisseur');
            },
            'remboursements.encaissements' => function($query) use ($startDate, $endDate) {
                $query->whereBetween('date_enc', [$startDate, $endDate]);
            }
        ])
            ->where('id_centre', $userCentreId)
            ->where(function($query) use ($startDate, $endDate) {
                $query->whereHas('factures', function($q) use ($startDate, $endDate) {
                    $q->whereBetween('date_facture', [$startDate, $endDate]);
                })
                    ->orWhereHas('bonAchats', function($q) use ($startDate, $endDate) {
                        $q->whereBetween('date_ba', [$startDate, $endDate]);
                    });
            })
            ->orderBy('n_dra', 'asc')
            ->get();

        foreach ($dras as $dra) {
            $draItems = collect();
            $draTotalDecaissement = 0;
            $draTotalEncaissement = 0;

            // Process factures
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
                    $price = $charge->pivot->prix_charge ?? 0;
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

            // Process bon achats
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

            // Process remboursements
            foreach ($dra->remboursements as $remboursement) {
                $encaisTotal = $remboursement->encaissements->sum('montant_enc');
                $draTotalEncaissement += $encaisTotal;

                if ($encaisTotal > 0) {
                    $draItems->push([
                        'n_dra' => $dra->n_dra,
                        'n_bon' => 'Remb. ' . $remboursement->n_remb,
                        'date_bon' => $remboursement->date_remb ? Carbon::parse($remboursement->date_remb)->format('d/m/Y') : '',
                        'libelle' => 'Encaissement remboursement',
                        'fournisseur' => '',
                        'encaissement' => number_format($encaisTotal, 2, ',', ' '),
                        'decaissement' => '',
                        'obs' => '',
                        'is_total' => false
                    ]);
                }
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

        // Generate QR code content
        $qrContent = "BROUILLARD CAISSE REGIE\n";
        $qrContent .= "Centre: $centreCode ($centreType)\n";
        $qrContent .= "Trimestre: T$trimestre $year\n";
        $qrContent .= "Période: " . $startDate->format('d/m/Y') . " - " . $endDate->format('d/m/Y') . "\n";
        $qrContent .= "Total Encaissements: " . number_format($rawTotalEncaissement, 2, ',', ' ') . " DZD\n";
        $qrContent .= "Total Décaissements: " . number_format($rawTotalDecaissement, 2, ',', ' ') . " DZD\n";
        $qrContent .= "Solde: " . number_format($rawBalancePeriod, 2, ',', ' ') . " DZD\n";
        $qrContent .= "Généré le: " . Carbon::now()->format('d/m/Y H:i');

        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(150)
            ->margin(10)
            ->build();

        // In your exportAllDras function, modify the PDF loadView part:
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
            'periode_debut' => $startDate->format('d/m/Y'),
            'periode_fin' => $endDate->format('d/m/Y'),
            'exercice' => $year,
            'trimestre' => 'Trimestre ' . $trimestre . ' ' . $year,
            'qrCode' => base64_encode($qrCode->getString()),
            'showQrCode' => false // Default to false, we'll handle it in the view
        ]);

        return $pdf->setOption('defaultFont', 'dejavu sans')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('brouillard_caisse_regie_T' . $trimestre . '_' . $year . '.pdf');
    }


    public function exportEtatTrimestriel(Request $request)
    {
        $user = Auth::user();
        if (!$user->id_centre) {
            abort(403, 'User is not associated with any centre');
        }

        $trimestre = $request->input('trimestre', 'current');
        $year = $request->input('year', date('Y'));

        // Calculate dates based on selected trimestre
        switch ($trimestre) {
            case '1':
                $startDate = Carbon::create($year, 1, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 3, 31)->endOfQuarter();
                break;
            case '2':
                $startDate = Carbon::create($year, 4, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 6, 30)->endOfQuarter();
                break;
            case '3':
                $startDate = Carbon::create($year, 7, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 9, 30)->endOfQuarter();
                break;
            case '4':
                $startDate = Carbon::create($year, 10, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 12, 31)->endOfQuarter();
                break;
            default: // current quarter
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                $trimestre = ceil(Carbon::now()->month / 3);
                $year = Carbon::now()->year;
                break;
        }

        $centreCode = $user->id_centre;
        $formattedCentreCode = '1' . $centreCode;
        $centre = Centre::find($centreCode);
        $centreType = $centre ? $centre->type_centre : 'Inconnu';
        $items = collect();

        // Document processing function (THIS WAS MISSING)
        $processDocument = function ($document, $isFacture = true) use (&$items, $formattedCentreCode) {
            $date = $isFacture
                ? Carbon::parse($document->date_facture)->format('d/m/Y')
                : Carbon::parse($document->date_ba)->format('d/m/Y');

            $addedTimbre = false;

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
        };

        // [Rest of your existing code for fetching factures and bonAchats]

        $factures = Facture::with([
            'pieces' => function ($query) {
                $query->withPivot('qte_f', 'prix_piece')->with('compteGeneral:code,libelle');
            },
            'prestations' => function ($query) {
                $query->withPivot('qte_fpr', 'prix_prest')->with('compteGeneral:code,libelle');
            },
            'charges' => function ($query) {
                $query->withPivot('qte_fc', 'prix_charge')->with('compteGeneral:code,libelle');
            },
            'fournisseur',
            'dra'
        ])
            ->whereHas('dra', function ($q) use ($centreCode) {
                $q->where('id_centre', $centreCode);
            })
            ->whereBetween('date_facture', [$startDate, $endDate])
            ->get();

        foreach ($factures as $facture) {
            $processDocument($facture, true);
        }

        $bonAchats = BonAchat::with([
            'pieces' => function ($query) {
                $query->withPivot('qte_ba', 'prix_piece')->with('compteGeneral:code,libelle');
            },
            'fournisseur',
            'dra'
        ])
            ->whereHas('dra', function ($q) use ($centreCode) {
                $q->where('id_centre', $centreCode);
            })
            ->whereBetween('date_ba', [$startDate, $endDate])
            ->get();

        foreach ($bonAchats as $bonAchat) {
            $processDocument($bonAchat, false);
        }

        $calculateTotal = function ($field) use ($items) {
            return $items->sum(function ($item) use ($field) {
                return (float)str_replace([' ', ','], ['', '.'], $item[$field] ?? '0');
            });
        };

        $totalFourniture = $calculateTotal('fourniture_consommable');
        $totalTravaux = $calculateTotal('travaux_prestations');
        $totalAutres = $calculateTotal('autres');
        $grandTotal = $totalFourniture + $totalTravaux + $totalAutres;

        // Generate QR code with quarter information
        $qrContent = "ETAT TRIMESTRIEL\n";
        $qrContent .= "Centre: $formattedCentreCode\n";
        $qrContent .= "Trimestre: T$trimestre $year\n";
        $qrContent .= "Période: " . $startDate->format('d/m/Y') . " - " . $endDate->format('d/m/Y') . "\n";
        $qrContent .= "Total: " . number_format($grandTotal, 2, ',', ' ') . " DZD\n";
        $qrContent .= "Généré le: " . Carbon::now()->format('d/m/Y H:i');

        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(150)
            ->margin(10)
            ->build();

        // Generate PDF with QR code
        $pdf = PDF::loadView('exports.etat_trimestriel', [
            'items' => $items,
            'centreCode' => $formattedCentreCode,
            'centreType' => $centreType,
            'trimestre' => 'Trimestre ' . $trimestre . ' ' . $year . ' (Du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y') . ')',
            'totalFourniture' => number_format($totalFourniture, 2, ',', ' '),
            'totalTravaux' => number_format($totalTravaux, 2, ',', ' '),
            'totalAutres' => number_format($totalAutres, 2, ',', ' '),
            'grandTotal' => number_format($grandTotal, 2, ',', ' '),
            'currentDate' => Carbon::now()->format('d/m/Y H:i'),
            'qrCode' => base64_encode($qrCode->getString()),
        ]);

        return $pdf->setOption('defaultFont', 'dejavu sans')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('etat_trimestriel_' . $formattedCentreCode . '_T' . $trimestre . '_' . $year . '.pdf');
    }


    public function exportEtatTrimestrielAllCentres(Request $request)
    {
        $trimestre = $request->input('trimestre', 'current');
        $year = $request->input('year', date('Y'));

        // Calculate dates based on selected trimestre
        switch ($trimestre) {
            case '1':
                $startDate = Carbon::create($year, 1, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 3, 31)->endOfQuarter();
                break;
            case '2':
                $startDate = Carbon::create($year, 4, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 6, 30)->endOfQuarter();
                break;
            case '3':
                $startDate = Carbon::create($year, 7, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 9, 30)->endOfQuarter();
                break;
            case '4':
                $startDate = Carbon::create($year, 10, 1)->startOfQuarter();
                $endDate = Carbon::create($year, 12, 31)->endOfQuarter();
                break;
            default: // current quarter
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                $trimestre = ceil(Carbon::now()->month / 3);
                $year = Carbon::now()->year;
                break;
        }

        $centres = Centre::all();
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

                        $addedTimbre = false;

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
                                $price = $charge->pivot->prix_charge ?? 0;
                                $montant = ($price * $quantity) * (1 + ($charge->tva ?? 0) / 100);

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
                    ->whereHas('dra', function($q) use ($centreCode) {
                        $q->where('id_centre', $centreCode);
                    })
                    ->whereBetween('date_facture', [$startDate, $endDate])
                    ->get();

                $bonAchats = BonAchat::with([
                    'pieces' => function ($query) { $query->withPivot('qte_ba', 'prix_piece')->with('compteGeneral:code,libelle'); },
                    'fournisseur',
                    'dra'
                ])
                    ->whereHas('dra', function($q) use ($centreCode) {
                        $q->where('id_centre', $centreCode);
                    })
                    ->whereBetween('date_ba', [$startDate, $endDate])
                    ->get();

                // Process documents for the current centre
                $processDocuments($factures, true);
                $processDocuments($bonAchats, false);

                $calculateTotalCentre = function($field) use ($items) {
                    return $items->sum(function($item) use ($field) {
                        return (float) str_replace([' ', ','], ['', '.'], $item[$field] ?? '0');
                    });
                };

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

        // Generate QR code with consolidated information
        $qrContent = "ÉTAT TRIMESTRIEL TOUS CENTRES\n";
        $qrContent .= "Trimestre: T$trimestre $year\n";
        $qrContent .= "Période: " . $startDate->format('d/m/Y') . " - " . $endDate->format('d/m/Y') . "\n";
        $qrContent .= "Centres inclus: " . count($centres) . "\n";
        $qrContent .= "Total global: " . number_format($globalTotals['grandTotal'], 2, ',', ' ') . " DZD\n";
        $qrContent .= "Généré le: " . Carbon::now()->format('d/m/Y H:i');

        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(150)
            ->margin(10)
            ->build();

        $pdf = PDF::loadView('exports.etat_trimestriel_all_centres', [
            'allCentreData' => $allCentreData,
            'globalTotals' => array_map(function($total) {
                return number_format($total, 2, ',', ' ');
            }, $globalTotals),
            'trimestre' => 'Trimestre ' . $trimestre . ' ' . $year . ' (Du ' . $startDate->format('d/m/Y') . ' au ' . $endDate->format('d/m/Y') . ')',
            'currentDate' => Carbon::now()->format('d/m/Y H:i'),
            'qrCode' => base64_encode($qrCode->getString()),
        ]);

        return $pdf->setOption('defaultFont', 'dejavu sans')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setPaper('a4', 'landscape')
            ->download('etat_trimestriel_all_centres_T' . $trimestre . '_' . $year . '.pdf');
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


        // Generate QR code content
        $qrContent = "NAFTAL DRA DOCUMENT\n";
        $qrContent .= "Référence: {$reference}\n";
        $qrContent .= "Centre: {$formattedCentreCode}\n";
        $qrContent .= "Date: " . Carbon::now()->format('d/m/Y') . "\n";
        $qrContent .= "Total: " . number_format($grandTotal, 2, ',', ' ') . " DZD";

        // Generate QR code
        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(200)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        // Get QR code as base64 string
        $qrCodeBase64 = base64_encode($qrCode->getString());

        // Generate PDF with QR code
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
            'qrCode' => $qrCodeBase64, // Pass the QR code to view
        ]);

        // Ensure proper PDF rendering
        return $pdf->setOption('defaultFont', 'dejavu sans')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->download('demande_derogation_' . $draNumber . '_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }


    public function exportBordereauOperations($draNumber)
    {
        // Authenticate and validate user
        $user = Auth::user();
        if (!$user->id_centre) {
            abort(403, 'User is not associated with any centre');
        }

        // Get centre info
        $centreCode = $user->id_centre;
        $formattedCentreCode = str_pad($centreCode, 4, '0', STR_PAD_LEFT);
        $centre = Centre::find($centreCode);
        $seuilcentre = $centre?->seuil_centre;
        $centreType = $centre?->type_centre ?? 'Inconnu';

        // Fetch DRA
        $dra = DB::table('dras')
            ->where('id_centre', $centreCode)
            ->where('n_dra', $draNumber)
            ->first();

        if (!$dra) {
            abort(404, 'DRA not found');
        }

        // Prepare
        $operations = collect();
        $lineNumber = 1;
        $currentPage = 1;

        // FACTURES
        $factures = DB::table('factures')
            ->where('n_dra', $dra->n_dra)
            ->get();

        foreach ($factures as $facture) {
            $firstLine = true;

            // PIECES
            $pieces = DB::table('quantite__f_s')
                ->where('n_facture', $facture->n_facture)
                ->join('pieces', 'quantite__f_s.id_piece', '=', 'pieces.id_piece')
                ->get();

            foreach ($pieces as $piece) {
                $quantity = $piece->qte_f ?? 1;
                $price = $piece->prix_piece ?? 0;
                $montant = ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);

                // Add droit_timbre to first line only
                if ($firstLine && $facture->droit_timbre > 0) {
                    $montant += $facture->droit_timbre;
                    $firstLine = false;
                }

                $operations->push([
                    'page' => $currentPage,
                    'ligne' => $lineNumber++,
                    'n_enregistrement' => '',
                    'reference_document' => '',
                    'compte_general' => $piece->compte_general_code ?? '/',
                    'compte_analytique' => $piece->compte_analytique_code ?? '/',
                    'debit' => $montant,
                    'credit' => '',
                    'devise' => '',
                    'libelle' => $piece->nom_piece ?? 'Pièce sans nom'
                ]);
            }

            // PRESTATIONS
            $prestations = DB::table('facture_prestation')
                ->where('n_facture', $facture->n_facture)
                ->join('prestations', 'facture_prestation.id_prest', '=', 'prestations.id_prest')
                ->get();

            foreach ($prestations as $prestation) {
                $quantity = $prestation->qte_fpr ?? 1;
                $price = $prestation->prix_prest ?? 0;
                $montant = ($price * $quantity) * (1 + ($prestation->tva ?? 0) / 100);

                // Add droit_timbre to first prestation if not yet added
                if ($firstLine && $facture->droit_timbre > 0) {
                    $montant += $facture->droit_timbre;
                    $firstLine = false;
                }

                $operations->push([
                    'page' => $currentPage,
                    'ligne' => $lineNumber++,
                    'n_enregistrement' => '',
                    'reference_document' => '',
                    'compte_general' => $prestation->compte_general_code ?? '/',
                    'compte_analytique' => $prestation->compte_analytique_code ?? '/',
                    'debit' => $montant,
                    'credit' => '',
                    'devise' => '',
                    'libelle' => $prestation->nom_prest ?? 'Prestation sans nom'
                ]);
            }

            // CHARGES
            try {
                $factureCharges = DB::table('facture_charge')
                    ->where('n_facture', $facture->n_facture)
                    ->join('charges', 'facture_charge.id_charge', '=', 'charges.id_charge')
                    ->get();

                foreach ($factureCharges as $charge) {
                    $quantity = $charge->qte_charge ?? 1;
                    $price = $charge->prix_charge ?? $charge->montant_charge ?? 0;
                    $montant = ($price * $quantity) * (1 + ($charge->tva ?? 0) / 100);

                    // Add droit_timbre to first charge if not yet added
                    if ($firstLine && $facture->droit_timbre > 0) {
                        $montant += $facture->droit_timbre;
                        $firstLine = false;
                    }

                    $operations->push([
                        'page' => $currentPage,
                        'ligne' => $lineNumber++,
                        'n_enregistrement' => '',
                        'reference_document' => '',
                        'compte_general' => $charge->compte_general_code ?? '/',
                        'compte_analytique' => $charge->compte_analytique_code ?? '/',
                        'debit' => $montant,
                        'credit' => '',
                        'devise' => '',
                        'libelle' => $charge->nom_charge ?? 'Charge sans nom'
                    ]);
                }
            } catch (\Exception $e) {
                // skip
            }
        }

        // BON ACHATS
        $bonAchats = DB::table('bon_achats')
            ->where('n_dra', $dra->n_dra)
            ->get();

        foreach ($bonAchats as $bonAchat) {
            $pieces = DB::table('quantite_b_a_s')
                ->where('n_ba', $bonAchat->n_ba)
                ->join('pieces', 'quantite_b_a_s.id_piece', '=', 'pieces.id_piece')
                ->get();

            foreach ($pieces as $piece) {
                $quantity = $piece->qte_ba ?? 1;
                $price = $piece->prix_piece ?? 0;
                $montant = ($price * $quantity) * (1 + ($piece->tva ?? 0) / 100);

                $operations->push([
                    'page' => $currentPage,
                    'ligne' => $lineNumber++,
                    'n_enregistrement' => '',
                    'reference_document' => '',
                    'compte_general' => $piece->compte_general_code ?? '/',
                    'compte_analytique' => $piece->compte_analytique_code ?? '/',
                    'debit' => $montant,
                    'credit' => '',
                    'devise' => '',
                    'libelle' => $piece->nom_piece ?? 'Pièce sans nom'
                ]);
            }
        }

        // DIRECT CHARGES
        try {
            $directCharges = DB::table('charges')
                ->where('n_dra', $dra->n_dra)
                ->get();

            foreach ($directCharges as $charge) {
                $montant = $charge->montant_charge ?? 0;

                $operations->push([
                    'page' => $currentPage,
                    'ligne' => $lineNumber++,
                    'n_enregistrement' => '',
                    'reference_document' => '',
                    'compte_general' => $charge->compte_general_code ?? '/',
                    'compte_analytique' => $charge->compte_analytique_code ?? '/',
                    'debit' => $montant,
                    'credit' => '',
                    'devise' => '',
                    'libelle' => $charge->nom_charge ?? 'Charge sans nom'
                ]);
            }
        } catch (\Exception $e) {
            // skip if direct charge relation fails
        }

        // Totals
        $totalDebits = $operations->sum('debit');
        $totalCredits = $totalDebits;

        $operations->push([
            'page' => '',
            'ligne' => '',
            'n_enregistrement' => '',
            'reference_document' => '',
            'compte_general' => '',
            'compte_analytique' => 'TOTAUX',
            'debit' => $totalDebits,
            'credit' => $totalCredits,
            'devise' => '',
            'libelle' => "Remboursement DRA N°:{$draNumber}"
        ]);

        // Summary values
        $montantRegie = $seuilcentre ?? 0;
        $enCaisseDebut = $seuilcentre ?? 0;
        $encaissementRemb = $dra->montant_rembourse ?? 0;
        $depensesPeriode = $totalDebits;
        $enCaisseFin = $centre?->montant_disponible ?? ($enCaisseDebut + $encaissementRemb - $depensesPeriode);

        $dateDebut = $dra->date_creation ? Carbon::parse($dra->date_creation)->format('d/m/y') : '01/01/25';
        $dateFin = Carbon::now()->format('d/m/y');
        $periodeCompte = $dra->trimestre ?? $this->calculateTrimestre($dra->date_creation);

        // Generate QR code content
        $qrContent = "NAFTAL BORDEREAU OPERATIONS\n";
        $qrContent .= "DRA Number: {$draNumber}\n";
        $qrContent .= "Centre: {$formattedCentreCode}\n";
        $qrContent .= "Date: " . Carbon::now()->format('d/m/Y') . "\n";
        $qrContent .= "Total Debits: " . number_format($totalDebits, 2, ',', ' ') . " DZD\n";
        $qrContent .= "Total Credits: " . number_format($totalCredits, 2, ',', ' ') . " DZD";

        // Generate QR code
        $qrCode = Builder::create()
            ->writer(new PngWriter())
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(200)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        // Get QR code as base64 string
        $qrCodeBase64 = base64_encode($qrCode->getString());

        // Generate PDF with QR code
        $pdf = PDF::loadView('exports.bordereau_operations', [
            'dra' => $dra,
            'operations' => $operations,
            'centreCode' => $formattedCentreCode,
            'centreType' => $centreType,
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'montantRegie' => $montantRegie,
            'enCaisseDebut' => $enCaisseDebut,
            'encaissementRemb' => $encaissementRemb,
            'depensesPeriode' => $depensesPeriode,
            'enCaisseFin' => $enCaisseFin,
            'dateDebut' => $dateDebut,
            'dateFin' => $dateFin,
            'periodeCompte' => $periodeCompte,
            'currentDate' => Carbon::now()->format('d/m/Y'),
            'numeroRemboursement' => $dra->numero_remboursement ?? '3761002',
            'qrCode' => $qrCodeBase64, // Pass the QR code to view
        ]);

        return $pdf->setOption('defaultFont', 'dejavu sans')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('enable_php', true)
            ->setOption('dpi', 96)
            ->setPaper('A4', 'landscape')
            ->download('bordereau_operations_' . $draNumber . '_' . Carbon::now()->format('Y-m-d') . '.pdf');
    }


    /**
     * Calculate trimestre based on date
     */
    private function calculateTrimestre($date)
    {
        if (!$date) {
            return 'T1'; // Default to first trimestre
        }

        $month = Carbon::parse($date)->month;

        if ($month >= 1 && $month <= 3) {
            return 'T1';
        } elseif ($month >= 4 && $month <= 6) {
            return 'T2';
        } elseif ($month >= 7 && $month <= 9) {
            return 'T3';
        } else {
            return 'T4';
        }
    }





}
