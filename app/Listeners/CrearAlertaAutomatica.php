<?php

namespace App\Listeners;

use App\Models\Alerta;
use App\Events\LecturaCriticaDetectada;

class CrearAlertaAutomatica
{
    public function handle(LecturaCriticaDetectada $event)
    {
        // Verificar si ya existe una alerta activa reciente
        $alertaExistente = Alerta::where('equipo_id', $event->equipo->id)
            ->where('sensor_id', $event->sensor->id)
            ->where('estado', 'activa')
            ->where('created_at', '>=', now()->subHours(1))
            ->first();

        if ($alertaExistente) {
            // Actualizar alerta existente
            $alertaExistente->update([
                'contador_ocurrencias' => $alertaExistente->contador_ocurrencias + 1,
                'ultimo_valor' => $event->lectura->valor,
                'updated_at' => now()
            ]);
        } else {
            // Crear nueva alerta
            Alerta::create([
                'equipo_id' => $event->equipo->id,
                'sensor_id' => $event->sensor->id,
                'tipo' => 'lectura_critica',
                'nivel' => strtolower($event->nivelCriticidad),
                'titulo' => "Lectura {$event->nivelCriticidad} en {$event->equipo->nombre}",
                'descripcion' => "El sensor {$event->sensor->tipo} registró un valor de {$event->lectura->valor}",
                'valor_detectado' => $event->lectura->valor,
                'contador_ocurrencias' => 1,
                'estado' => 'activa',
                'prioridad' => $this->calcularPrioridad($event->nivelCriticidad),
            ]);
        }
    }

    private function calcularPrioridad($nivel)
    {
        return match ($nivel) {
            'CRITICO' => 'alta',
            'ADVERTENCIA' => 'media',
            default => 'baja',
        };
    }
}
