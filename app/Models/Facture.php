<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
use HasFactory;

public $timestamps = false;
protected $primaryKey = 'n_facture';
public $incrementing = false;
protected $keyType = 'int';

protected $fillable = [
'n_facture',
'montant_facture',
'date_facture',
'id_fourn',
'n_dra',
];



public function dra()
{
return $this->belongsTo(Dra::class, 'n_dra', 'n_dra');
}

public function fournisseur()
{
return $this->belongsTo(Fournisseur::class, 'id_fourn', 'id_fourn');
}
}
