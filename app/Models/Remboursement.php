<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Remboursement extends Model
{
    protected $primaryKey = 'n_remb';

    public $timestamps = false;
    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'date_remb',
        'method_remb',
        'n_dra',
    ];

    public function dra()
    {
        return $this->belongsTo(Dra::class, 'n_dra', 'n_dra');
    }
}

