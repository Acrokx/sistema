<?php

namespace App\Jobs;

use App\Models\Equipo;
use App\Models\Lectura;
use App\Models\Alerta;
use App\Services\IAService;
use App\Events\AlertaGenerada;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalizarTodosLosEquipos implements ShouldQueue
{
    use Queueable;

    protected $tipoAnalisis;
    protected $umbralMinimoLecturas;
    protected $generarAlertas;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $tipoAnalisis = 'completo',
        int $umbralMinimoLecturas = 3,
        bool $generarAlertas = true
    ) {
        $this->tipoAnalisis = $tipoAnalisis;
        $this->umbralMinimoLecturas = $umbralMinimoLecturas;
        $this->generarAlertas = $generarAlertas;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $iaService = new IAService();

        // Verificar que el servicio de IA esté disponible
        if (!$iaService->verificarDisponibilidad()) {
            Log::error('Servicio de IA no disponible para análisis de equipos');
            return;
        }

        $equipos = Equipo::with(['sensores.lecturas' => function($query) {
            $query->latest()->take(10); // Últimas 10 lecturas por sensor
        }])->get();

        $resultados = [
            'total_equipos' => $equipos->count(),
            'equipos_analizados' => 0,
            'alertas_generadas' => 0,
            'errores' => 0,
            'detalles' => []
        ];

        Log::info("Iniciando análisis de {$resultados['total_equipos']} equipos", [
            'tipo_analisis' => $this->tipoAnalisis,
            'umbral_minimo' => $this->umbralMinimoLecturas
        ]);

        foreach ($equipos as $equipo) {
            try {
                $resultadoEquipo = $this->analizarEquipo($equipo, $iaService);
                $resultados['detalles'][] = $resultadoEquipo;

                if ($resultadoEquipo['analizado']) {
                    $resultados['equipos_analizados']++;
                }

                if ($resultadoEquipo['alerta_generada']) {
                    $resultados['alertas_generadas']++;
                }

            } catch (\Exception $e) {
                $resultados['errores']++;
                Log::error("Error analizando equipo {$equipo->id}", [
                    'equipo' => $equipo->nombre,
                    'error' => $e->getMessage()
                ]);

                $resultados['detalles'][] = [
                    'equipo_id' => $equipo->id,
                    'equipo_nombre' => $equipo->nombre,
                    'analizado' => false,
                    'alerta_generada' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Generar resumen del análisis
        $this->generarResumenAnalisis($resultados);

        Log::info("Análisis de equipos completado", [
            'equipos_analizados' => $resultados['equipos_analizados'],
            'alertas_generadas' => $resultados['alertas_generadas'],
            'errores' => $resultados['errores']
        ]);
    }

    /**
     * Analizar un equipo específico
     */
    private function analizarEquipo(Equipo $equipo, IAService $iaService): array
    {
        $resultado = [
            'equipo_id' => $equipo->id,
            'equipo_nombre' => $equipo->nombre,
            'analizado' => false,
            'alerta_generada' => false,
            'sensores_analizados' => 0,
            'prediccion' => null,
            'nivel_riesgo' => null
        ];

        // Recopilar datos de todos los sensores del equipo
        $datosSensores = $this->recopilarDatosSensores($equipo);

        if (empty($datosSensores) || count($datosSensores) < $this->umbralMinimoLecturas) {
            Log::warning("Equipo {$equipo->id} no tiene suficientes datos para análisis", [
                'sensores_con_datos' => count($datosSensores),
                'umbral_requerido' => $this->umbralMinimoLecturas
            ]);
            return $resultado;
        }

        // Realizar predicción con IA
        $prediccion = $iaService->predecirFallo(
            $datosSensores['temperatura'] ?? 40,
            $datosSensores['vibracion'] ?? 20,
            $datosSensores['presion'] ?? 2,
            $equipo->horas_operacion ?? 1000
        );

        if (isset($prediccion['error'])) {
            Log::warning("Error en predicción IA para equipo {$equipo->id}", [
                'error' => $prediccion['error']
            ]);
            return $resultado;
        }

        $resultado['analizado'] = true;
        $resultado['sensores_analizados'] = count($datosSensores);
        $resultado['prediccion'] = $prediccion;
        $resultado['nivel_riesgo'] = $prediccion['nivel_riesgo'] ?? 'bajo';

        // Generar alerta si es necesario y está habilitado
        if ($this->generarAlertas && $this->debeGenerarAlerta($prediccion)) {
            $alerta = $this->generarAlertaParaEquipo($equipo, $prediccion, $datosSensores);
            $resultado['alerta_generada'] = true;
            $resultado['alerta_id'] = $alerta->id;
        }

        return $resultado;
    }

    /**
     * Recopilar datos de sensores de un equipo
     */
    private function recopilarDatosSensores(Equipo $equipo): array
    {
        $datos = [];

        foreach ($equipo->sensores as $sensor) {
            $ultimaLectura = $sensor->lecturas->first(); // Ya ordenadas por latest()

            if ($ultimaLectura) {
                $datos[$sensor->tipo_sensor] = $ultimaLectura->valor;
            }
        }

        return $datos;
    }

    /**
     * Determinar si debe generar una alerta basada en la predicción
     */
    private function debeGenerarAlerta(array $prediccion): bool
    {
        $nivelRiesgo = $prediccion['nivel_riesgo'] ?? 'bajo';

        // Generar alerta para niveles medio, alto o crítico
        return in_array($nivelRiesgo, ['medio', 'alto', 'crítico']);
    }

    /**
     * Generar alerta para un equipo
     */
    private function generarAlertaParaEquipo(Equipo $equipo, array $prediccion, array $datosSensores): Alerta
    {
        // Encontrar la lectura más reciente para asociar la alerta
        $lecturaMasReciente = null;
        foreach ($equipo->sensores as $sensor) {
            $lectura = $sensor->lecturas->first();
            if ($lectura && (!$lecturaMasReciente || $lectura->created_at > $lecturaMasReciente->created_at)) {
                $lecturaMasReciente = $lectura;
            }
        }

        $alerta = Alerta::create([
            'lectura_id' => $lecturaMasReciente?->id,
            'nivel_criticidad' => $this->mapearNivelCriticidad($prediccion['nivel_riesgo']),
            'tipo_fallo' => 'Análisis Predictivo Completo - ' . $prediccion['nivel_riesgo'],
            'descripcion' => $this->generarDescripcionAlerta($equipo, $prediccion, $datosSensores)
        ]);

        // Disparar evento para broadcasting
        broadcast(new AlertaGenerada($alerta));

        Log::info("Alerta generada para equipo {$equipo->id}", [
            'alerta_id' => $alerta->id,
            'nivel_riesgo' => $prediccion['nivel_riesgo'],
            'recomendacion' => $prediccion['recomendacion'] ?? 'Sin recomendación'
        ]);

        return $alerta;
    }

    /**
     * Generar descripción detallada de la alerta
     */
    private function generarDescripcionAlerta(Equipo $equipo, array $prediccion, array $datosSensores): string
    {
        $descripcion = "Equipo: {$equipo->nombre}\n";
        $descripcion .= "Ubicación: {$equipo->ubicacion}\n";
        $descripcion .= "Nivel de riesgo: {$prediccion['nivel_riesgo']}\n";

        if (isset($prediccion['probabilidad_fallo'])) {
            $descripcion .= "Probabilidad de fallo: {$prediccion['probabilidad_fallo']}%\n";
        }

        $descripcion .= "Datos de sensores:\n";
        foreach ($datosSensores as $tipo => $valor) {
            $descripcion .= "- {$tipo}: {$valor}\n";
        }

        if (isset($prediccion['recomendacion'])) {
            $descripcion .= "\nRecomendación: {$prediccion['recomendacion']}";
        }

        return $descripcion;
    }

    /**
     * Mapear nivel de riesgo a criticidad
     */
    private function mapearNivelCriticidad(string $nivelRiesgo): string
    {
        return match($nivelRiesgo) {
            'bajo' => 'bajo',
            'medio' => 'medio',
            'moderado' => 'medio',
            'alto' => 'alto',
            'crítico' => 'alto',
            default => 'medio'
        };
    }

    /**
     * Generar resumen del análisis completo
     */
    private function generarResumenAnalisis(array $resultados): void
    {
        $resumen = [
            'timestamp' => now()->toISOString(),
            'tipo_analisis' => $this->tipoAnalisis,
            'estadisticas' => [
                'total_equipos' => $resultados['total_equipos'],
                'equipos_analizados' => $resultados['equipos_analizados'],
                'alertas_generadas' => $resultados['alertas_generadas'],
                'errores' => $resultados['errores'],
                'tasa_exito' => $resultados['total_equipos'] > 0
                    ? round(($resultados['equipos_analizados'] / $resultados['total_equipos']) * 100, 2)
                    : 0
            ]
        ];

        // Contar por nivel de riesgo
        $nivelesRiesgo = ['bajo' => 0, 'medio' => 0, 'alto' => 0, 'crítico' => 0];
        foreach ($resultados['detalles'] as $detalle) {
            if ($detalle['analizado'] && isset($detalle['nivel_riesgo'])) {
                $nivelesRiesgo[$detalle['nivel_riesgo']] = ($nivelesRiesgo[$detalle['nivel_riesgo']] ?? 0) + 1;
            }
        }

        $resumen['distribucion_riesgos'] = $nivelesRiesgo;

        Log::info("Resumen del análisis de equipos", $resumen);

        // Aquí se podría guardar el resumen en una tabla de reportes
        // o enviarlo por email a administradores
    }
}
