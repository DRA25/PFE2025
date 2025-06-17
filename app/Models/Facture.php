<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
use HasFactory;

public $timestamps = false;
protected $primaryKey = 'n_facture';
public $incrementing = false;
protected $keyType = 'int';

protected $fillable = [
'n_facture',
'date_facture',
'id_fourn',
'n_dra',
    'droit_timbre',
];


    public function pieces()
    {
        return $this->belongsToMany(Piece::class, 'quantite__f_s', 'n_facture', 'id_piece')
            ->withPivot('qte_f');
    }

    // New: Relationship for Prestations
    public function prestations()
    {
        return $this->belongsToMany(Prestation::class, 'facture_prestation', 'n_facture', 'id_prest')
            ->withPivot('qte_fpr'); // Use the correct pivot column name here
    }

    // New: Relationship for Charges
    public function charges()
    {
        return $this->belongsToMany(Charge::class, 'facture_charge', 'n_facture', 'id_charge')
            ->withPivot('qte_fc'); // Use the correct pivot column name here
    }

    /**
     * Calculate the total amount of the facture.
     */
    public function getMontantAttribute(): float
    {
        return $this->pieces->sum(function($piece) {
            $subtotal = $piece->prix_piece * $piece->pivot->qte_f;
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
