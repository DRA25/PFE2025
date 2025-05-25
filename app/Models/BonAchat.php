<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BonAchat extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'n_ba';
    public $incrementing = false;
    protected $keyType = 'int';

    protected $fillable = [
        'n_ba',
        'date_ba',
        'id_fourn',
        'n_dra',
    ];

    public function pieces()
    {
        return $this->belongsToMany(Piece::class, 'quantite_b_a_s', 'n_ba', 'id_piece')
            ->withPivot('qte_ba');
    }

    /**
     * Calculate the total amount of the bon d'achat.
     */
    public function getMontantAttribute(): float
    {
        return $this->pieces->sum(function($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_ba;
            return $subtotal * (1 + ($piece->tva / 100));
        });
    }

    public function dra()
    {
        return $this->belongsTo(Dra::class, 'n_dra', 'n_dra');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class, 'id_fourn', 'id_fourn');
    }
}
