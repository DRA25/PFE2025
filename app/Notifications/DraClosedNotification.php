<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Dra;
use App\Models\User;

class DraClosedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $dra;
    public $user;

    public function __construct(Dra $dra, User $user)
    {
        $this->dra = $dra;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $message = sprintf(
            'Le DRA %s a été clôturé par %s',
            $this->dra->n_dra,
            $this->user->name
        );

        if ($this->dra->motif) {
            $message .= '. Motif: ' . $this->dra->motif;
        }

        return [
            'dra_number' => $this->dra->n_dra,
            'user_name' => $this->user->name,
            'message' => $message,
            'link' => '/dras/' . $this->dra->n_dra,
        ];
    }
}
