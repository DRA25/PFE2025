<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonDeCommande extends Model
{

    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'n_bc';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'n_bc',
        'date_bc',
    ];


    public function pieces()
    {
        return $this->belongsToMany(Piece::class, 'commande_pieces', 'n_bc', 'id_piece')
            ->withPivot('qte_commandep');
    }

    public function prestations()
    {
        return $this->belongsToMany(Piece::class, 'commande_prests', 'n_bc', 'id_prest')
            ->withPivot('qte_commandepr');
    }
}
