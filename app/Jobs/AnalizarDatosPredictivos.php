<?php

namespace App\Jobs;

use App\Models\Equipo;
use App\Models\Alerta;
use App\Models\Lectura;
use App\Services\IAService;
use App\Events\AlertaGenerada;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalizarDatosPredictivos implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $iaService = new IAService();

        // Verificar que el servicio de IA esté disponible
        if (!$iaService->verificarDisponibilidad()) {
            return;
        }

        $equipos = Equipo::with('sensores')->get();

        foreach ($equipos as $equipo) {
            // Obtener últimas lecturas de sensores
            $ultimasLecturas = $this->obtenerUltimasLecturas($equipo);

            if (count($ultimasLecturas) >= 4) {
                // Hacer predicción con IA
                $prediccion = $iaService->predecirFallo(
                    $ultimasLecturas['temperatura'],
                    $ultimasLecturas['vibracion'],
                    $ultimasLecturas['presion'],
                    $equipo->horas_operacion ?? 1000
                );

                // Si el riesgo es alto, crear alerta
                if (isset($prediccion['probabilidad_fallo']) &&
                    $prediccion['probabilidad_fallo'] > 70) {
                    $alerta = Alerta::create([
                        'lectura_id' => $this->obtenerUltimaLecturaId($equipo),
                        'nivel_criticidad' => $this->mapearNivelCriticidad($prediccion['nivel_riesgo'] ?? 'medio'),
                        'tipo_fallo' => 'Análisis Predictivo IA',
                        'descripcion' => "Riesgo de fallo del {$prediccion['probabilidad_fallo']}% - {$prediccion['recomendacion']}"
                    ]);

                    // Emitir evento en tiempo real
                    broadcast(new AlertaGenerada($alerta));
                }
            }
        }
    }

    private function obtenerUltimasLecturas($equipo)
    {
        $lecturas = [];
        foreach ($equipo->sensores as $sensor) {
            $ultimaLectura = Lectura::where('sensor_id', $sensor->id)
                ->latest()
                ->first();
            if ($ultimaLectura) {
                $lecturas[$sensor->tipo_sensor] = $ultimaLectura->valor;
            }
        }
        return $lecturas;
    }

    private function obtenerUltimaLecturaId($equipo)
    {
        // Obtener la lectura más reciente de cualquier sensor del equipo
        foreach ($equipo->sensores as $sensor) {
            $lectura = Lectura::where('sensor_id', $sensor->id)
                ->latest()
                ->first();
            if ($lectura) {
                return $lectura->id;
            }
        }
        return null;
    }

    private function mapearNivelCriticidad($nivelRiesgo)
    {
        $mapeo = [
            'bajo' => 'bajo',
            'moderado' => 'medio',
            'alto' => 'alto',
            'crítico' => 'alto'
        ];
        return $mapeo[$nivelRiesgo] ?? 'medio';
    }
}
