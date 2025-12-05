<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    protected $fillable = ['contenido'];

    public function comentable()
    {
        return $this->morphTo();
    }
}
