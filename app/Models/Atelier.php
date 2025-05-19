<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Atelier extends Model
{
    use HasFactory;

    protected $table = 'ateliers';
    protected $primaryKey = 'id_atelier';
    public $timestamps = false;

    protected $fillable = [
        'id_atelier',
        'adresse_atelier',
        'id_centre'
    ];

    public function demandePieces()
    {
        return $this->hasMany(DemandePiece::class, 'id_atelier', 'id_atelier');
    }

    public function centre()
    {
        return $this->belongsTo(Centre::class, 'id_centre', 'id_centre');
    }

}
