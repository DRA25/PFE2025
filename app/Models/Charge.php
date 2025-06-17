<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Charge extends Model
{
    use HasFactory;

    protected $table = 'charges';
    protected $primaryKey = 'id_charge';
    public $timestamps = false;

    protected $fillable = [
        'id_charge',
        'nom_charge',
        'desc_change',
        'type_change',
        'prix_charge',
        'tva',
        'compte_general_code',
        'compte_analytique_code',
    ];

    public function compteGeneral()
    {
        return $this->belongsTo(CompteGeneral::class, 'compte_general_code', 'code');
    }

    public function compteAnalytique()
    {
        return $this->belongsTo(CompteAnalytique::class, 'compte_analytique_code', 'code');
    }

    public function boncommandes()
    {
        return $this->belongsToMany(BonDeCommande::class, 'commande_charges', 'id_charge', 'n_bc')
            ->withPivot('qte_commandec');
    }

    public function factures()
    {
        return $this->belongsToMany(Facture::class, 'facture_charge', 'id_charge', 'n_facture')
            ->withPivot('qte_fc'); // Use the correct pivot column name here
    }
}
