<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DRA extends Model
{
    protected $table = 'd_r_a_s';
    protected $primaryKey = 'n_dra';
    public $incrementing = false;
    protected $keyType = 'string';

    // Disable automatic handling of timestamps
    public $timestamps = false;

    protected $fillable = [
        'n_dra',
        'periode',
        'etat',
        'cmp_gen',
        'cmp_ana',
        'debit',
        'libelle_dra',
        'date_dra',
        'fourn_dra',
        'destinataire_dra'
    ];
}
