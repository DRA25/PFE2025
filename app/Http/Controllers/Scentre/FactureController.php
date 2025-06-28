<?php
namespace App\Http\Controllers\Scentre;

use App\Http\Controllers\Controller;
use App\Models\Dra;
use App\Models\Facture;
use App\Models\Fournisseur;
use App\Models\Piece;
use App\Models\Prestation;
use App\Models\Charge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FactureController extends Controller
{
public function index(Dra $dra)
{
$factures = $dra->factures()
->with([
'fournisseur:id_fourn,nom_fourn',
'pieces:id_piece,nom_piece,tva',
'prestations:id_prest,nom_prest,tva',
'charges:id_charge,nom_charge,tva'
])
->get()
->map(function ($facture) {
$facture->montant = $this->calculateMontant($facture);
return $facture;
});

return Inertia::render('Facture/Index', [
'dra' => $dra,
'factures' => $factures,
]);
}

public function show(Dra $dra)
{
$factures = $dra->factures()
->with([
'fournisseur:id_fourn,nom_fourn',
'pieces:id_piece,nom_piece,tva',
'prestations:id_prest,nom_prest,tva',
'charges:id_charge,nom_charge,tva'
])
->get()
->map(function ($facture) {
$facture->montant = $this->calculateMontant($facture);
return $facture;
});

return Inertia::render('Facture/Show', [
'dra' => $dra,
'factures' => $factures,
]);
}

public function create(Dra $dra)
{
$fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);
$pieces = Piece::all(['id_piece', 'nom_piece', 'tva']);
$prestations = Prestation::all(['id_prest', 'nom_prest', 'tva']);
$charges = Charge::all(['id_charge', 'nom_charge', 'tva']);

return inertia('Facture/Create', [
'dra' => $dra,
'fournisseurs' => $fournisseurs,
'pieces' => $pieces,
'prestations' => $prestations,
'charges' => $charges,
]);
}

public function store(Request $request, Dra $dra)
{
$request->validate([
'n_facture' => 'required|unique:factures,n_facture',
'date_facture' => 'required|date',
'id_fourn' => 'required|exists:fournisseurs,id_fourn',
'droit_timbre' => 'nullable|numeric',
'pieces' => 'nullable|array',
'pieces.*.id_piece' => 'required_with:pieces|exists:pieces,id_piece',
'pieces.*.qte_f' => 'required_with:pieces|integer|min:1',
'pieces.*.prix_piece' => 'required_with:pieces|numeric|min:0',
'prestations' => 'nullable|array',
'prestations.*.id_prest' => 'required_with:prestations|exists:prestations,id_prest',
'prestations.*.qte_fpr' => 'required_with:prestations|integer|min:1',
'prestations.*.prix_prest' => 'required_with:prestations|numeric|min:0', // Added price for prestation
'charges' => 'nullable|array',
'charges.*.id_charge' => 'required_with:charges|exists:charges,id_charge',
'charges.*.qte_fc' => 'required_with:charges|integer|min:1',
]);

if (empty($request->pieces) && empty($request->prestations) && empty($request->charges)) {
return back()->withErrors(['items' => 'Vous devez sélectionner au moins un article (pièce, prestation ou charge).']);
}

DB::beginTransaction();

try {
$facture = new Facture([
'n_facture' => $request->n_facture,
'date_facture' => $request->date_facture,
'id_fourn' => $request->id_fourn,
'n_dra' => $dra->n_dra,
'droit_timbre' => $request->droit_timbre ?? 0,
]);
$facture->save();

// Attach pieces with price
if (!empty($request->pieces)) {
$pieceAttachments = [];
foreach ($request->pieces as $piece) {
$pieceAttachments[$piece['id_piece']] = [
'qte_f' => $piece['qte_f'],
'prix_piece' => $piece['prix_piece']
];
}
$facture->pieces()->attach($pieceAttachments);
}

// Attach prestations with price
if (!empty($request->prestations)) {
$prestationAttachments = [];
foreach ($request->prestations as $prestation) {
$prestationAttachments[$prestation['id_prest']] = [
'qte_fpr' => $prestation['qte_fpr'],
'prix_prest' => $prestation['prix_prest'] // Store price in pivot
];
}
$facture->prestations()->attach($prestationAttachments);
}

// Attach charges (no price change)
if (!empty($request->charges)) {
$chargeAttachments = [];
foreach ($request->charges as $charge) {
$chargeAttachments[$charge['id_charge']] = ['qte_fc' => $charge['qte_fc']];
}
$facture->charges()->attach($chargeAttachments);
}

$totalFacture = $this->calculateMontant($facture);

if ($totalFacture > 20000) {
DB::rollBack();
return back()->withErrors(['facture_total' => 'Le montant total de la facture ne peut pas dépasser 20 000 DA.']);
}

$dra->load('bonAchats.pieces', 'factures.pieces', 'factures.prestations', 'factures.charges');
$totalDra = '0';

foreach ($dra->bonAchats as $ba) {
$totalDra = bcadd($totalDra, (string)$this->calculateBonAchatMontant($ba), 2);
}
foreach ($dra->factures as $f) {
$totalDra = bcadd($totalDra, (string)$this->calculateMontant($f), 2);
}

if ($dra->centre->montant_disponible < $totalFacture) {
DB::rollBack();
return back()->withErrors(['total_dra' => 'Le montant disponible est insuffisant, il faut un remboursement.']);
}

$dra->update([
'total_dra' => round($totalDra, 2),
]);

$dra->centre->update([
'montant_disponible' => $dra->centre->montant_disponible - $totalFacture
]);

DB::commit();

return redirect()->route('scentre.dras.factures.index', $dra->n_dra)
->with('success', 'Facture créée avec succès.');
} catch (\Exception $e) {
DB::rollBack();
return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()]);
}
}

public function edit(Dra $dra, Facture $facture)
{
$fournisseurs = Fournisseur::all(['id_fourn', 'nom_fourn']);
$pieces = Piece::all(['id_piece', 'nom_piece', 'tva']);
$prestations = Prestation::all(['id_prest', 'nom_prest', 'tva']);
$charges = Charge::all(['id_charge', 'nom_charge', 'tva']);

$facture->load(['pieces' => function ($query) {
$query->withPivot('qte_f', 'prix_piece');
}, 'prestations' => function ($query) {
$query->withPivot('qte_fpr', 'prix_prest');
}, 'charges']);

return Inertia::render('Facture/Edit', [
'dra' => $dra,
'facture' => $facture,
'fournisseurs' => $fournisseurs,
'allPieces' => $pieces,
'allPrestations' => $prestations,
'allCharges' => $charges,
]);
}

public function update(Request $request, $n_dra, $n_facture)
{
$request->validate([
'n_facture' => 'required|unique:factures,n_facture,' . $n_facture . ',n_facture',
'date_facture' => 'required|date',
'id_fourn' => 'required|exists:fournisseurs,id_fourn',
'droit_timbre' => 'nullable|numeric',
'pieces' => 'nullable|array',
'pieces.*.id_piece' => 'required_with:pieces|exists:pieces,id_piece',
'pieces.*.qte_f' => 'required_with:pieces|integer|min:1',
'pieces.*.prix_piece' => 'required_with:pieces|numeric|min:0',
'prestations' => 'nullable|array',
'prestations.*.id_prest' => 'required_with:prestations|exists:prestations,id_prest',
'prestations.*.qte_fpr' => 'required_with:prestations|integer|min:1',
'prestations.*.prix_prest' => 'required_with:prestations|numeric|min:0', // Added price for prestation
'charges' => 'nullable|array',
'charges.*.id_charge' => 'required_with:charges|exists:charges,id_charge',
'charges.*.qte_fc' => 'required_with:charges|integer|min:1',
]);

if (empty($request->pieces) && empty($request->prestations) && empty($request->charges)) {
return back()->withErrors(['items' => 'Vous devez sélectionner au moins un article (pièce, prestation ou charge).']);
}

DB::beginTransaction();

try {
$dra = Dra::where('n_dra', $n_dra)->firstOrFail();
$facture = Facture::where('n_facture', $n_facture)->firstOrFail();
$centre = $dra->centre;

$oldMontant = $this->calculateMontant($facture);
$centre->montant_disponible += $oldMontant;
$centre->save();

$facture->update([
'n_facture' => $request->n_facture,
'date_facture' => $request->date_facture,
'id_fourn' => $request->id_fourn,
'droit_timbre' => $request->droit_timbre ?? 0,
]);

// Sync pieces with price
if (!empty($request->pieces)) {
$pieceAttachments = [];
foreach ($request->pieces as $piece) {
$pieceAttachments[$piece['id_piece']] = [
'qte_f' => $piece['qte_f'],
'prix_piece' => $piece['prix_piece']
];
}
$facture->pieces()->sync($pieceAttachments);
} else {
$facture->pieces()->detach();
}

// Sync prestations with price
if (!empty($request->prestations)) {
$prestationAttachments = [];
foreach ($request->prestations as $prestation) {
$prestationAttachments[$prestation['id_prest']] = [
'qte_fpr' => $prestation['qte_fpr'],
'prix_prest' => $prestation['prix_prest'] // Update price in pivot
];
}
$facture->prestations()->sync($prestationAttachments);
} else {
$facture->prestations()->detach();
}

// Sync charges (no price change)
if (!empty($request->charges)) {
$chargeAttachments = [];
foreach ($request->charges as $charge) {
$chargeAttachments[$charge['id_charge']] = ['qte_fc' => $charge['qte_fc']];
}
$facture->charges()->sync($chargeAttachments);
} else {
$facture->charges()->detach();
}

$facture->refresh();
$newMontant = $this->calculateMontant($facture);

if ($newMontant > 20000) {
DB::rollBack();
return back()->withErrors(['facture_total' => 'Le montant total de la facture ne peut pas dépasser 20 000 DA.']);
}

$totalDra = 0;
$dra->load(['bonAchats.pieces', 'factures.pieces', 'factures.prestations', 'factures.charges']);

foreach ($dra->bonAchats as $ba) {
$totalDra += $this->calculateBonAchatMontant($ba);
}
foreach ($dra->factures as $f) {
$totalDra += $this->calculateMontant($f);
}

if ($dra->centre->montant_disponible < $newMontant) {
DB::rollBack();
return back()->withErrors(['total_dra' => 'Le total du DRA dépasse le seuil autorisé du centre.']);
}

$dra->total_dra = $totalDra;
$dra->save();

$centre->montant_disponible -= $newMontant;
$centre->save();

DB::commit();

return redirect()->route('scentre.dras.factures.index', $dra->n_dra)
->with('success', 'Facture mise à jour avec succès.');
} catch (\Exception $e) {
DB::rollBack();
return back()->withErrors(['error' => 'Erreur lors de la mise à jour : ' . $e->getMessage()]);
}
}

public function destroy(Dra $dra, Facture $facture)
{
DB::beginTransaction();

try {
$montantToRestore = $this->calculateMontant($facture);

$facture->pieces()->detach();
$facture->prestations()->detach();
$facture->charges()->detach();
$facture->delete();

$dra->centre->update([
'montant_disponible' => $dra->centre->montant_disponible + $montantToRestore
]);

$dra->load('bonAchats.pieces', 'factures.pieces', 'factures.prestations', 'factures.charges');

$totalDra = '0';
foreach ($dra->bonAchats as $ba) {
$totalDra = bcadd($totalDra, (string)$this->calculateBonAchatMontant($ba), 2);
}
foreach ($dra->factures as $f) {
$totalDra = bcadd($totalDra, (string)$this->calculateMontant($f), 2);
}

$dra->update([
'total_dra' => (float)$totalDra,
]);

DB::commit();

return redirect()->route('scentre.dras.factures.index', $dra->n_dra)
->with('success', 'Facture supprimée avec succès.');
} catch (\Exception $e) {
DB::rollBack();
return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
}
}

protected function calculateMontant(Facture $facture): float
{
$piecesTotal = $facture->pieces->sum(function ($piece) {
$price = $piece->pivot->prix_piece;
$subtotal = $price * $piece->pivot->qte_f;
return $subtotal * (1 + ($piece->tva / 100));
});

$prestationsTotal = $facture->prestations->sum(function ($prestation) {
$price = $prestation->pivot->prix_prest; // Get price from pivot
$subtotal = $price * $prestation->pivot->qte_fpr;
return $subtotal * (1 + ($prestation->tva / 100));
});

$chargesTotal = $facture->charges->sum(function ($charge) {
$subtotal = $charge->prix_charge * $charge->pivot->qte_fc;
return $subtotal * (1 + ($charge->tva / 100));
});

return $piecesTotal + $prestationsTotal + $chargesTotal + ($facture->droit_timbre ?? 0);
}

protected function calculateBonAchatMontant($bonAchat): float
{
return $bonAchat->pieces->sum(function ($piece) {
$price = $piece->pivot->prix_piece;
$subtotal = $price * $piece->pivot->qte_ba;
return $subtotal * (1 + ($piece->tva / 100));
});
}
}
