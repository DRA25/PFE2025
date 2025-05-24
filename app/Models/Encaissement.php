<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Encaissement extends Model
{
    protected $fillable = ['id_centre', 'n_remb', 'montant_enc', 'date_enc'];

    public function centre()
    {
        return $this->belongsTo(Centre::class, 'id_centre');
    }

    public function remboursement()
    {
        return $this->belongsTo(Remboursement::class, 'n_remb');
    }


}
