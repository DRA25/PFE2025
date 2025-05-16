<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandePiece extends Model
{
    use HasFactory;

    protected $table = 'demande_pieces';
    protected $primaryKey = 'id_dp';
    public $timestamps = false;

    protected $fillable = [
        'date_dp',
        'etat_dp',
        'id_piece',
        'qte_demandep',
        'id_magasin',
        'id_atelier',
    ];

    public function atelier()
    {
        return $this->belongsTo(Atelier::class, 'id_atelier', 'id_atelier');
    }

    public function magasin()
    {
        return $this->belongsTo(Magasin::class, 'id_magasin', 'id_magasin');
    }

    public function piece()
    {
        return $this->belongsTo(Piece::class, 'id_piece', 'id_piece');
    }


}
