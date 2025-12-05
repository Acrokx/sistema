<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    use HasFactory;

    protected $table = 'alertas';

    protected $fillable = ['lectura_id', 'nivel_criticidad', 'tipo_fallo', 'descripcion'];

    // Relación: Una alerta pertenece a una lectura
    public function lectura()
    {
        return $this->belongsTo(Lectura::class);
    }

    // Relación polimórfica: Una alerta tiene muchos comentarios
    public function comentarios()
    {
        return $this->morphMany(Comentario::class, 'comentable');
    }
}
