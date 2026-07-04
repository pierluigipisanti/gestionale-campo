<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campo extends Model
{
    protected $table = 'campi';
    protected $guarded = [];

    protected $casts = [
        'attivo' => 'boolean',
    ];

    public function ente()
    {
        return $this->belongsTo(Ente::class);
    }
}
