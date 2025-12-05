<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    protected $table = 'sensores';

    protected $fillable = ['equipo_id', 'tipo_sensor', 'rango_min', 'rango_max', 'limite_alerta_bajo', 'limite_alerta_alto'];

    // Relación: Un sensor pertenece a un equipo
    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    // Relación: Un sensor tiene muchas lecturas
    public function lecturas()
    {
        return $this->hasMany(Lectura::class);
    }

    // Relación polimórfica: Un sensor tiene muchos comentarios
    public function comentarios()
    {
        return $this->morphMany(Comentario::class, 'comentable');
    }
}
