<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuantiteStocke extends Model
{
protected $table = 'quantite__stockes';

public $timestamps = false;

protected $primaryKey = ['id_magasin', 'id_piece'];
public $incrementing = false;

protected $fillable = ['id_magasin', 'id_piece', 'qte_stocke'];

public function magasin()
{
return $this->belongsTo(Magasin::class, 'id_magasin');
}

public function piece()
{
return $this->belongsTo(Piece::class, 'id_piece');
}
}
