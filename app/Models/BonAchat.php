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
        'montant_ba',
        'date_ba',
        'id_fourn',
        'n_dra',
    ];

    public function dra()
    {
        return $this->belongsTo(Dra::class, 'n_dra', 'n_dra');
    }
}

