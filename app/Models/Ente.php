<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ente extends Model
{
    protected $table = 'enti';
    protected $guarded = [];

    public function campi()
    {
        return $this->hasMany(Campo::class);
    }
}
