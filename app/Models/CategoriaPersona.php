<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoriaPersona extends Model
{
    protected $table = 'categorie_persona';
    protected $guarded = [];

    protected $casts = [
        'attiva' => 'boolean',
    ];

    // solo le categorie utilizzabili (le altre sono disattivate, non cancellate)
    public function scopeAttive($query)
    {
        return $query->where('attiva', true);
    }

    public function persone()
    {
        return $this->hasMany(Persona::class, 'categoria_id');
    }

    public function accessi()
    {
        return $this->hasMany(Accesso::class, 'categoria_id');
    }
}
