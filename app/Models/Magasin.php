<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Magasin extends Model
{
    use HasFactory;

    protected $table = 'magasins';
    protected $primaryKey = 'id_magasin';
    public $timestamps = false;

    protected $fillable = [
        'id_magasin',
        'adresse_magasin',
        'id_centre'
    ];

    public function demandePieces()
    {
        return $this->hasMany(DemandePiece::class, 'id_magasin', 'id_magasin');
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class, 'id_centre', 'id_centre');
    }

    public function pieces()
    {
        return $this->belongsToMany(Piece::class, 'quantite__stockes', 'id_magasin', 'id_piece')
            ->withPivot('qte_stocke');
    }

}
