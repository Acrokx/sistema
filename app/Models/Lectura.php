<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lectura extends Model
{
    use HasFactory;

    protected $table = 'lecturas';

    protected $fillable = ['sensor_id', 'valor', 'timestamp_lectura', 'estado'];

    // Relación: Una lectura pertenece a un sensor
    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }

    // Relación: Una lectura tiene muchas alertas
    public function alertas()
    {
        return $this->hasMany(Alerta::class);
    }
}
