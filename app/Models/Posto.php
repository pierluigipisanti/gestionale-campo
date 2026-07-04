<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Posto extends Model
{
    protected $table = 'posti';
    protected $guarded = [];

    public function tenda()
    {
        return $this->belongsTo(Tenda::class);
    }
}
