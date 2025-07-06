<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Dra;
use App\Models\User;

class DraStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $dra;
    public $user;
    public $status;

    public function __construct(Dra $dra, User $user, string $status)
    {
        $this->dra = $dra;
        $this->user = $user;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $message = sprintf(
            'Le DRA %s a été %s par %s',
            $this->dra->n_dra,
            $this->status,
            $this->user->name
        );

        if ($this->status === 'refusé' && $this->dra->motif) {
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
