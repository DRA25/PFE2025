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
        // You can choose multiple channels here. 'database' is good for in-app notifications.
        // You might also consider 'mail' for email notifications.
        return ['database']; // For in-app notifications
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->line('A new demand for a piece has been created.')
            ->action('View Demande', url('/magasin/demandes-pieces/' . $this->demandePiece->id_dp))
            ->line('Piece: ' . $this->demandePiece->piece->nom_piece)
            ->line('Quantity: ' . $this->demandePiece->qte_demandep)
            ->line('From Atelier: ' . $this->demandePiece->atelier->adresse_atelier)
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'demande_piece_id' => $this->demandePiece->id_dp,
            'piece_name' => $this->demandePiece->piece->nom_piece,
            'quantity' => $this->demandePiece->qte_demandep,
            'atelier_adresse' => $this->demandePiece->atelier->adresse_atelier,
            'message' => 'New piece demand created by ' . ($this->demandePiece->atelier ? 'Atelier ' . $this->demandePiece->atelier->adresse_atelier : 'Unknown Atelier'),
            'link' => url('/magasin/demandes-pieces/' . $this->demandePiece->id_dp),
        ];
    }
}
