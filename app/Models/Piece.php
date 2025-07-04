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
        'tva',
        'marque_piece',
        'ref_piece',
        'id_centre',
        'compte_general_code',
        'compte_analytique_code',

    ];

    public function demandePieces()
    {
        return $this->hasMany(DemandePiece::class, 'id_piece', 'id_piece');
    }


    public function factures()
    {
        return $this->belongsToMany(Facture::class, 'quantite__f_s', 'id_piece', 'n_facture')
            ->withPivot('qte_f');
    }

    public function bonachats()
    {
        return $this->belongsToMany(Facture::class, 'quantite_b_a_s', 'id_piece', 'n_ba')
            ->withPivot('qte_ba');
    }

    public function boncommandes()
    {
        return $this->belongsToMany(BonDeCommande::class, 'commande_pieces', 'id_piece', 'n_bc')
            ->withPivot('qte_commandep');
    }
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'id_centre');
    }

    public function compteGeneral()
    {
        return $this->belongsTo(CompteGeneral::class, 'compte_general_code', 'code');
    }

    public function compteAnalytique()
    {
        return $this->belongsTo(CompteAnalytique::class, 'compte_analytique_code', 'code');
    }



}

