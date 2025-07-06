<?php
namespace App\Http\Controllers\Magasin;

use App\Http\Controllers\Controller;
use App\Models\DemandePiece;
use App\Models\QuantiteStocke;
use App\Models\User;
use App\Notifications\DemandePieceStatusChanged;
use App\Notifications\NewDemandePieceNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class MagasinDemandePieceController extends Controller
{

    public function index()
    {
        $demandes = DemandePiece::with([
            'magasin.centre',
            'atelier.centre',
            'piece'
        ])
            ->where(function ($query) {
                $query->whereNotNull('id_atelier');
            })
            ->where('etat_dp', 'en attente')
            ->orderBy('date_dp', 'desc')
            ->get();


        $etatOptions = ['en attente', 'non disponible', 'livre', 'refuse'];

        return Inertia::render('Magasin/DemandeAtelier/Index', [
            'demandes' => $demandes,
            'etatOptions' => $etatOptions,
        ]);
    }


    public function show(DemandePiece $demande_piece)
    {

        $qte_stocke = 0;


        if ($demande_piece->id_piece) {

            $stock = QuantiteStocke::where('id_piece', $demande_piece->id_piece)

                ->first();

            $qte_stocke = $stock ? $stock->qte_stocke : 0;
        }


        // Ensure 'motif' is loaded with the demande_piece if it exists in the database
        $demande_piece->load(['piece', 'atelier.centre']);
        $demande_piece->qte_stocke = $qte_stocke;


        $etatOptions = ['en attente', 'non disponible', 'livre', 'refuse'];

        return Inertia::render('Magasin/DemandeAtelier/Show', [
            'demande' => $demande_piece,
            'etatOptions' => $etatOptions,
        ]);
    }


    public function update(Request $request, DemandePiece $demande_piece)
    {
        $validated = $request->validate([
            'etat_dp' => 'required|string|in:en attente,non disponible,livre,refuse',
            'motif' => 'nullable|string|max:500|required_if:etat_dp,refuse',
        ]);

        $oldStatus = $demande_piece->etat_dp;
        $demande_piece->update($validated);

        $magasinUser = auth()->user();

        Log::info('DemandePiece status changed', [
            'demande_id' => $demande_piece->id_dp,
            'old_status' => $oldStatus,
            'new_status' => $validated['etat_dp'],
            'user_id' => $magasinUser->id,
            'user_centre' => $magasinUser->id_centre,
        ]);

        if ($oldStatus !== $validated['etat_dp']) {
            try {
                if (!$magasinUser->id_centre) {
                    throw new \Exception('Magasin user has no center assigned');
                }

                // Notify atelier users in same center (for any status change)
                $atelierUsers = User::role('service atelier')
                    ->where('id_centre', $magasinUser->id_centre)
                    ->get();

                foreach ($atelierUsers as $atelierUser) {
                    $atelierUser->notify(new DemandePieceStatusChanged($demande_piece));
                    Log::info('Notification sent to atelier user', [
                        'to_user' => $atelierUser->id,
                        'centre' => $magasinUser->id_centre,
                        'demande' => $demande_piece->id_dp
                    ]);
                }

                // Additional notification for centre when status becomes 'non disponible'
                if ($validated['etat_dp'] === 'non disponible') {
                    $centreUsers = User::role('service centre')
                        ->where('id_centre', $magasinUser->id_centre)
                        ->get();

                    foreach ($centreUsers as $centreUser) {
                        $centreUser->notify(new NewDemandePieceNotification($demande_piece));
                        Log::info('Notification sent to centre user', [
                            'to_user' => $centreUser->id,
                            'centre' => $magasinUser->id_centre,
                            'demande' => $demande_piece->id_dp,
                            'status' => 'non disponible'
                        ]);
                    }
                }

            } catch (\Exception $e) {
                Log::error('Notification failed: '.$e->getMessage());
            }
        }

        return redirect()->route('magasin.mes-demandes.index')
            ->with('success', 'État mis à jour avec succès');
    }


    public function exportPdf(DemandePiece $demande_piece)
    {


        $demande_piece->load(['atelier.centre', 'piece']);
        $pdf = Pdf::loadView('magasin.demandespieces.pdf', ['demande' => $demande_piece]);
        return $pdf->download('demande_piece_' . $demande_piece->id_dp . '.pdf');
    }


    public function exportListPdf(Request $request)
    {
        // Start building the query
        $demandes = DemandePiece::with([
            'atelier.centre',
            'piece'
        ]);

        // Filter to only include demands where etat_dp is 'en attente'
        $demandes->where('etat_dp', 'en attente');

        $demandes->where(function ($query) {
            $query->whereNotNull('id_atelier');
        });

        $demandes->orderBy('date_dp', 'desc');

        $filteredDemandes = $demandes->get();


        $pdf = Pdf::loadView('magasin.demandespieces.pdf-export', [
            'demandes' => $filteredDemandes,
            'etat'     => 'en attente',
        ]);

        return $pdf->download('mes-demandes-list-en-attente-'.now()->format('Y-m-d').'.pdf');
    }

    public function livrerPiece(Request $request, $demande_piece_id)
    {
        DB::beginTransaction();
        try {
            // Get the authenticated magasin user
            $magasinUser = auth()->user();

            // Load the demande with necessary relationships
            $demande = DemandePiece::with(['piece'])
                ->findOrFail($demande_piece_id);

            // Check if already delivered
            if ($demande->etat_dp === 'livre') {
                return back()->with('error', 'Cette demande a déjà été livrée');
            }

            // Verify stock
            $stock = QuantiteStocke::where('id_piece', $demande->id_piece)
                ->first();

            if (!$stock) {
                return back()->with('error', 'Pièce non trouvée dans votre stock');
            }

            if ($stock->qte_stocke < $demande->qte_demandep) {
                return back()->with('error', 'Stock insuffisant');
            }

            // Update stock
            QuantiteStocke::where('id_piece', $demande->id_piece)
                ->decrement('qte_stocke', $demande->qte_demandep);

            // Update demande status
            $demande->etat_dp = 'livre';
            $demande->save();

            // Notify atelier users in the same center
            $atelierUsers = User::role('service atelier')
                ->where('id_centre', $magasinUser->id_centre)
                ->get();

            foreach ($atelierUsers as $atelierUser) {
                $atelierUser->notify(new DemandePieceStatusChanged($demande));
            }

            DB::commit();

            return redirect()
                ->route('magasin.mes-demandes.index')
                ->with('success', 'Pièce livrée avec succès');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Livraison échouée: ' . $e->getMessage(), [
                'demande_id' => $demande_piece_id,
                'user_id' => auth()->id()
            ]);
            return back()->with('error', 'Erreur lors de la livraison: ' . $e->getMessage());
        }
    }
}
