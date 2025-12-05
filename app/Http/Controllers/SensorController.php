<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use App\Models\Lectura;
use App\Events\LecturaCriticaDetectada;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function almacenarLectura(Request $request, $sensorId)
    {
        $request->validate([
            'valor' => 'required|numeric',
        ]);

        $sensor = Sensor::with('equipo')->findOrFail($sensorId);

        // Crear la lectura
        $lectura = Lectura::create([
            'sensor_id' => $sensor->id,
            'valor' => $request->valor,
            'timestamp_lectura' => now(),
            'estado' => $this->determinarEstado($request->valor, $sensor),
        ]);

        // Verificar si es crítica y disparar evento
        if ($this->esLecturaCritica($lectura, $sensor)) {
            event(new LecturaCriticaDetectada(
                $lectura,
                $sensor,
                $sensor->equipo
            ));
        }

        return response()->json([
            'success' => true,
            'lectura_id' => $lectura->id,
            'es_critica' => $this->esLecturaCritica($lectura, $sensor),
            'estado' => $lectura->estado,
            'mensaje' => $this->esLecturaCritica($lectura, $sensor)
                ? 'Lectura crítica detectada y notificación enviada'
                : 'Lectura almacenada correctamente'
        ]);
    }

    private function esLecturaCritica($lectura, $sensor)
    {
        // Considera crítica si supera el límite normal (advertencia)
        return $lectura->valor >= ($sensor->limite_alerta_bajo ?? 50);
    }

    private function determinarEstado($valor, $sensor)
    {
        if ($valor >= ($sensor->limite_alerta_alto ?? 80)) {
            return 'critico';
        } elseif ($valor >= ($sensor->limite_alerta_bajo ?? 50)) {
            return 'alerta';
        }
        return 'normal';
    }
}
