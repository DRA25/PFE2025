<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prestation extends Model
{
    protected $primaryKey = 'id_prest';
    public $incrementing = true;

    public $timestamps = false;
    protected $keyType = 'int';

    protected $fillable = [
        'id_prest',
        'nom_prest',
        'desc_prest',
        'prix_prest',
        'date_prest',
        'tva',
        'compte_general_code',
        'compte_analytique_code'
    ];

    protected $casts = [
        'date_prest' => 'date',
        'tva' => 'float',
    ];

    public function compteGeneral(): BelongsTo
    {
        return $this->belongsTo(CompteGeneral::class, 'compte_general_code', 'code');
    }

    public function compteAnalytique(): BelongsTo
    {
        return $this->belongsTo(CompteAnalytique::class, 'compte_analytique_code', 'code');
    }

    public function boncommandes()
    {
        return $this->belongsToMany(BonDeCommande::class, 'commande_prests', 'id_prest', 'n_bc')
            ->withPivot('qte_commandepr');
    }

    public function factures()
    {
        return $this->belongsToMany(Facture::class, 'facture_prestation', 'id_prest', 'n_facture')
            ->withPivot('qte_fpr'); // Use the correct pivot column name here
    }
}
