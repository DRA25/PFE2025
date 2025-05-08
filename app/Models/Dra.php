<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dra extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'n_dra';
    public $incrementing = false;
    protected $keyType = 'string';



    protected $fillable = [
        'n_dra',
        'date_creation',
        'etat',
        'seuil_dra',
        'total_dra',
    ];

    public function factures()
    {
        return $this->hasMany(Facture::class, 'n_dra', 'n_dra');
    }
}
