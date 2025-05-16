<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Magasin extends Model
{
    use HasFactory;

    protected $table = 'magasins';
    protected $primaryKey = 'id_magasin';
    public $timestamps = false;

    protected $fillable = [
        'id_magasin',
        'adresse_magasin',
    ];

    public function demandePieces()
    {
        return $this->hasMany(DemandePiece::class, 'id_magasin', 'id_magasin');
    }
}
