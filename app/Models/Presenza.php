<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presenza extends Model
{
    protected $table = 'presenze';
    protected $guarded = [];

    protected $casts = [
        'data' => 'date',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
}
