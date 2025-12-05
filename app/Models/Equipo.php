<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'tipo', 'ubicacion', 'descripcion', 'fecha_instalacion', 'estado'];

    // Relación: Un equipo tiene muchos sensores
    public function sensores()
    {
        return $this->hasMany(Sensor::class);
    }

    // Relación polimórfica: Un equipo tiene muchos comentarios
    public function comentarios()
    {
        return $this->morphMany(Comentario::class, 'comentable');
    }

    // Relación con técnicos (usuarios asignados al equipo)
    public function tecnicos()
    {
        return $this->belongsToMany(\App\Models\User::class, 'equipo_user', 'equipo_id', 'user_id');
    }

}
