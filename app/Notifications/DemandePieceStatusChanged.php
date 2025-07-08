<?php

namespace App\Notifications;

use App\Models\DemandePiece;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandePieceStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public $demandePiece;

    // app/Notifications/DemandePieceStatusChanged.php

    public function __construct(DemandePiece $demandePiece)
    {
        // Only load what's absolutely necessary
        $this->demandePiece = $demandePiece->loadMissing([
            'piece' => fn($q) => $q->withDefault(['nom_piece' => 'Inconnue'])
        ]);
    }

    public function toArray($notifiable)
    {
        return [
            'message' => 'Statut mis à jour: ' . $this->demandePiece->etat_dp,
            'demande_id' => $this->demandePiece->id_dp,
            'piece' => $this->demandePiece->piece->nom_piece,
            'new_status' => $this->demandePiece->etat_dp,
            'motif' => $this->demandePiece->motif ?? 'Aucun motif',

        ];
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // Now supports both channels
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Mise à jour du statut de votre demande de pièce')
            ->line('Le statut de votre demande de pièce a été modifié:')
            ->line('Nouveau statut: ' . $this->demandePiece->etat_dp)
            ->line('Pièce: ' . $this->demandePiece->piece->nom_piece)
            ->line('Quantité: ' . $this->demandePiece->qte_demandep)
            ->when($this->demandePiece->motif, function ($mail) {
                $mail->line('Motif: ' . $this->demandePiece->motif);
            })
            ->action('Voir la demande', url('/demandes-pieces/' . $this->demandePiece->id_dp))
            ->line('Merci d\'utiliser notre application!');
    }



    // Alias for toArray (some Laravel versions use toDatabase)
    public function toDatabase($notifiable)
    {
        return $this->toArray($notifiable);
    }
}
