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


    protected $dates = ['date_creation', 'created_at', 'updated_at']; // If using Laravel <8
    protected $casts = [
        'date_creation' => 'datetime', // For Laravel 8+
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];


    protected $fillable = [
        'n_dra',
        'date_creation',
        'etat',
        'seuil_dra',
        'total_dra',
        'created_at'
    ];

    public function factures()
    {
        return $this->hasMany(Facture::class, 'n_dra', 'n_dra');
    }

    public function bonachats()
    {
        return $this->hasMany(BonAchat::class, 'n_dra', 'n_dra');
    }
}
