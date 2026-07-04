<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accesso extends Model
{
    protected $table = 'accessi';
    protected $guarded = [];

    protected $casts = [
        'entrata_at' => 'datetime',
        'uscita_at' => 'datetime',
    ];

    // "chi è dentro il campo ora" — la domanda più frequente al varco.
    public function scopeDentro($query)
    {
        return $query->whereNull('uscita_at');
    }

    public function categoria()
    {
        return $this->belongsTo(CategoriaPersona::class, 'categoria_id');
    }
}
