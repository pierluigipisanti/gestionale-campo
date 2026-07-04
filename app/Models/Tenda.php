<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenda extends Model
{
    protected $table = 'tende';
    protected $guarded = [];

    public function posti()
    {
        return $this->hasMany(Posto::class);
    }
}
