<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piece extends Model

{
    protected $table = 'pieces';
    protected $primaryKey = 'id_piece';
    public $timestamps = false;

    protected $fillable = [
        'id_piece',
        'nom_piece',
        'prix_piece',
        'marque_piece',
        'ref_piece',

    ];

    public function demandePieces()
    {
        return $this->hasMany(DemandePiece::class, 'id_piece', 'id_piece');
    }


}

