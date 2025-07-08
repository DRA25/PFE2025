<?php

namespace App\Notifications;

use App\Models\DemandePiece;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewDemandePieceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $demandePiece;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\DemandePiece $demandePiece
     * @return void
     */
    public function __construct(DemandePiece $demandePiece)
    {
        $this->demandePiece = $demandePiece;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {

        return ['database'];
    }





    public function toArray($notifiable)
    {
        return [
            'demande_piece_id' => $this->demandePiece->id_dp,
            'piece_name' => $this->demandePiece->piece->nom_piece,
            'quantity' => $this->demandePiece->qte_demandep,
            'atelier_adresse' => $this->demandePiece->atelier->adresse_atelier,
            'message' => 'Nouvelle demande de pièce envoyée par ' .
                ($this->demandePiece->atelier ? 'Atelier ' . $this->demandePiece->atelier->adresse_atelier : 'Unknown Atelier'),

        ];
    }
}
