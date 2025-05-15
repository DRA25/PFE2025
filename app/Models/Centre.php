<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Centre extends Model
{
    protected $table = 'centres';

    protected $primaryKey = 'id_centre';

    public $timestamps = false;
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_centre',
        'adresse_centre',
        'seuil_centre',
        'type_centre',
    ];

    public function dras()
    {
        return $this->hasMany(Dra::class, 'id_centre', 'id_centre');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'id_centre', 'id_centre');
    }

}
