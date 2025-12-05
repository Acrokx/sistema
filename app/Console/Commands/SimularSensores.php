<?php

namespace App\Console\Commands;

use App\Models\Lectura;
use App\Models\Sensor;
use App\Models\Alerta;
use App\Services\IAService;
use App\Events\AlertaGenerada;
use App\Events\LecturaCriticaDetectada;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SimularSensores extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sensores:simular';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula datos de sensores para el sistema de mantenimiento predictivo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $iaService = new IAService();

        // Verificar que el servicio de IA esté disponible
        if (!$iaService->verificarDisponibilidad()) {
            $this->error('El servicio de IA no está disponible. Asegúrate de que la API de Python esté ejecutándose.');
            return;
        }

        $sensores = Sensor::with('equipo')->get();

        foreach ($sensores as $sensor) {
            // Generar valor aleatorio según tipo de sensor
            $valor = $this->generarValor($sensor->tipo_sensor);

            // Obtener datos para predicción IA
            $datosIa = $this->obtenerDatosParaIA($sensor);

            // Hacer predicción con IA usando el servicio
            $prediccion = $iaService->predecirFallo(
                $datosIa['temperatura'],
                $datosIa['vibracion'],
                $datosIa['presion'],
                $datosIa['horas_operacion']
            );

            // Verificar si hay error en la predicción
            if (isset($prediccion['error'])) {
                $this->warn("Error al consultar IA para sensor {$sensor->id}: {$prediccion['error']}");
                $prediccion = ['nivel_riesgo' => 'bajo', 'recomendacion' => 'Sin predicción disponible'];
            }

            // Evaluar estado basado en límites y IA
            $estado = $this->evaluarEstado($valor, $sensor, $prediccion);

            // Crear nueva lectura
            $lectura = Lectura::create([
                'sensor_id' => $sensor->id,
                'valor' => $valor,
                'timestamp_lectura' => Carbon::now(),
                'estado' => $estado
            ]);

            // Broadcast lectura crítica si supera límites
            if ($valor >= $sensor->limite_alerta_alto) {
                event(new LecturaCriticaDetectada($lectura, $sensor, $sensor->equipo));
            }

            // Crear alerta si es necesario
            if ($prediccion['nivel_riesgo'] !== 'bajo') {
                $alerta = Alerta::create([
                    'lectura_id' => $lectura->id,
                    'nivel_criticidad' => $this->mapearNivelCriticidad($prediccion['nivel_riesgo']),
                    'tipo_fallo' => 'Predicción IA: ' . $prediccion['nivel_riesgo'],
                    'descripcion' => $prediccion['recomendacion']
                ]);

                // Disparar evento para WebSockets
                event(new AlertaGenerada($alerta));
            }
        }

        $this->info('Datos de sensores simulados y alertas generadas exitosamente');
    }

    private function generarValor($tipo)
    {
        switch ($tipo) {
            case 'temperatura':
                return rand(20, 80); // Grados Celsius
            case 'vibracion':
                return rand(0, 100); // mm/s
            case 'presion':
                return rand(1, 10); // Bar
            default:
                return rand(0, 100);
        }
    }

    private function obtenerDatosParaIA($sensor)
    {
        // Por simplicidad, usar valores simulados
        // En producción, calcular horas de operación reales
        return [
            'temperatura' => $sensor->tipo_sensor === 'temperatura' ? $this->generarValor('temperatura') : 40,
            'vibracion' => $sensor->tipo_sensor === 'vibracion' ? $this->generarValor('vibracion') : 20,
            'presion' => $sensor->tipo_sensor === 'presion' ? $this->generarValor('presion') : 2,
            'horas_operacion' => rand(100, 1500) // Simular horas de operación
        ];
    }


    private function evaluarEstado($valor, $sensor, $prediccion)
    {
        // Combinar evaluación de límites con predicción IA
        $estadoLimites = 'normal';
        if ($valor >= $sensor->limite_alerta_alto) {
            $estadoLimites = 'critico';
        } elseif ($valor >= $sensor->limite_alerta_bajo) {
            $estadoLimites = 'alerta';
        }

        // Si IA predice alto riesgo, usar ese estado
        if ($prediccion['nivel_riesgo'] === 'crítico') {
            return 'critico';
        } elseif ($prediccion['nivel_riesgo'] === 'alto' && $estadoLimites !== 'critico') {
            return 'alerta';
        }

        return $estadoLimites;
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
