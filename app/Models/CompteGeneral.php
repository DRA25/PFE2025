<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;



class CompteGeneral extends Model
{
    protected $table = 'comptes_generaux';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = ['code', 'libelle'];

    public function pieces()
    {
        return $this->hasMany(Piece::class, 'compte_general_code', 'code');
    }

    public function prestations()
    {
        return $this->hasMany(Prestation::class, 'compte_general_code', 'code');
    }
}
