<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'persone';
    protected $guarded = [];

    protected $casts = [
        'data_nascita' => 'date',
        'ultimo_movimento_at' => 'datetime',
    ];

    public function posto()
    {
        return $this->belongsTo(Posto::class);
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaPersona::class, 'categoria_id');
    }

    public function nucleo()
    {
        return $this->belongsTo(Nucleo::class);
    }

    public function movimenti()
    {
        return $this->hasMany(Movimento::class);
    }
}
