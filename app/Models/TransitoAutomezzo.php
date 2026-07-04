<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransitoAutomezzo extends Model
{
    protected $table = 'transiti_automezzo';
    protected $guarded = [];

    protected $casts = [
        'entrata_at' => 'datetime',
        'uscita_at' => 'datetime',
    ];

    public function automezzo()
    {
        return $this->belongsTo(Automezzo::class);
    }
}
