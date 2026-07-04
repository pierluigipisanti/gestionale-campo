<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Automezzo extends Model
{
    protected $table = 'automezzi';
    protected $guarded = [];

    protected $casts = [
        'ultimo_movimento_at' => 'datetime',
    ];

    public function scopeDentro($query)
    {
        return $query->where('stato', 'dentro');
    }

    public function transiti()
    {
        return $this->hasMany(TransitoAutomezzo::class);
    }
}
