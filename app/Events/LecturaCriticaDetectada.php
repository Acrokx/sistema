<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LecturaCriticaDetectada implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lectura;
    public $sensor;
    public $equipo;
    public $nivelCriticidad;

    public function __construct($lectura, $sensor, $equipo)
    {
        $this->lectura = $lectura;
        $this->sensor = $sensor;
        $this->equipo = $equipo;
        $this->nivelCriticidad = $this->calcularNivelCriticidad();
    }

    private function calcularNivelCriticidad()
    {
        $valor = $this->lectura->valor;
        $limiteNormal = $this->sensor->limite_alerta_bajo ?? 50; // fallback
        $limiteCritico = $this->sensor->limite_alerta_alto ?? 80; // fallback

        if ($valor >= $limiteCritico) {
            return 'CRITICO';
        } elseif ($valor >= $limiteNormal) {
            return 'ADVERTENCIA';
        }

        return 'NORMAL';
    }

    public function broadcastOn()
    {
        return new Channel('alertas.' . $this->equipo->id);
    }

    public function broadcastAs()
    {
        return 'lectura-critica';
    }

    public function broadcastWith()
    {
        return [
            'equipo_id' => $this->equipo->id,
            'equipo_nombre' => $this->equipo->nombre,
            'sensor_tipo' => $this->sensor->tipo_sensor,
            'valor_actual' => $this->lectura->valor,
            'nivel_criticidad' => $this->nivelCriticidad,
            'timestamp' => now()->toISOString(),
            'mensaje' => "Lectura {$this->nivelCriticidad} detectada en {$this->equipo->nombre}"
        ];
    }
}
