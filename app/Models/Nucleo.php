<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nucleo extends Model
{
    protected $table = 'nuclei';
    protected $guarded = [];

    public function persone()
    {
        return $this->hasMany(Persona::class);
    }
}
