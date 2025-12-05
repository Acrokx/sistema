<?php

namespace App\Jobs;

use App\Models\Equipo;
use App\Models\Alerta;
use App\Models\User;
use App\Mail\ReporteMantenimientoMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EnviarReporteMantenimiento implements ShouldQueue
{
    use Queueable;

    protected $tipoReporte;
    protected $destinatarios;
    protected $periodo;

    /**
     * Create a new job instance.
     */
    public function __construct(string $tipoReporte = 'diario', array $destinatarios = null, string $periodo = null)
    {
        $this->tipoReporte = $tipoReporte;
        $this->destinatarios = $destinatarios;
        $this->periodo = $periodo ?? now()->format('Y-m-d');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Generar datos del reporte
            $datosReporte = $this->generarDatosReporte();

            // Determinar destinatarios
            $destinatarios = $this->obtenerDestinatarios();

            // Enviar reporte a cada destinatario
            foreach ($destinatarios as $destinatario) {
                Mail::to($destinatario->email)->send(
                    new ReporteMantenimientoMail($datosReporte, $this->tipoReporte, $destinatario)
                );

                Log::info("Reporte de mantenimiento enviado", [
                    'tipo' => $this->tipoReporte,
                    'destinatario' => $destinatario->email,
                    'periodo' => $this->periodo
                ]);
            }

            Log::info("Job EnviarReporteMantenimiento completado exitosamente", [
                'tipo_reporte' => $this->tipoReporte,
                'destinatarios' => count($destinatarios),
                'periodo' => $this->periodo
            ]);

        } catch (\Exception $e) {
            Log::error("Error en EnviarReporteMantenimiento", [
                'error' => $e->getMessage(),
                'tipo_reporte' => $this->tipoReporte,
                'periodo' => $this->periodo
            ]);

            throw $e; // Re-lanzar para que el job sea marcado como fallido
        }
    }

    /**
     * Generar los datos del reporte según el tipo
     */
    private function generarDatosReporte(): array
    {
        $fechaInicio = $this->obtenerFechaInicio();
        $fechaFin = $this->obtenerFechaFin();

        return [
            'periodo' => [
                'inicio' => $fechaInicio->format('Y-m-d H:i:s'),
                'fin' => $fechaFin->format('Y-m-d H:i:s'),
                'tipo' => $this->tipoReporte
            ],
            'estadisticas_generales' => $this->obtenerEstadisticasGenerales($fechaInicio, $fechaFin),
            'alertas_por_equipo' => $this->obtenerAlertasPorEquipo($fechaInicio, $fechaFin),
            'equipos_criticos' => $this->obtenerEquiposCriticos(),
            'tendencias' => $this->obtenerTendencias($fechaInicio, $fechaFin),
            'recomendaciones' => $this->generarRecomendaciones()
        ];
    }

    /**
     * Obtener fecha de inicio según el tipo de reporte
     */
    private function obtenerFechaInicio(): Carbon
    {
        return match($this->tipoReporte) {
            'diario' => now()->startOfDay(),
            'semanal' => now()->startOfWeek(),
            'mensual' => now()->startOfMonth(),
            default => now()->startOfDay()
        };
    }

    /**
     * Obtener fecha de fin según el tipo de reporte
     */
    private function obtenerFechaFin(): Carbon
    {
        return match($this->tipoReporte) {
            'diario' => now()->endOfDay(),
            'semanal' => now()->endOfWeek(),
            'mensual' => now()->endOfMonth(),
            default => now()->endOfDay()
        };
    }

    /**
     * Obtener estadísticas generales del período
     */
    private function obtenerEstadisticasGenerales(Carbon $inicio, Carbon $fin): array
    {
        return [
            'total_equipos' => Equipo::count(),
            'equipos_activos' => Equipo::where('estado', 'activo')->count(),
            'total_alertas' => Alerta::whereBetween('created_at', [$inicio, $fin])->count(),
            'alertas_criticas' => Alerta::where('nivel_criticidad', 'alto')
                ->whereBetween('created_at', [$inicio, $fin])->count(),
            'alertas_moderadas' => Alerta::where('nivel_criticidad', 'medio')
                ->whereBetween('created_at', [$inicio, $fin])->count(),
            'alertas_bajas' => Alerta::where('nivel_criticidad', 'bajo')
                ->whereBetween('created_at', [$inicio, $fin])->count(),
        ];
    }

    /**
     * Obtener alertas agrupadas por equipo
     */
    private function obtenerAlertasPorEquipo(Carbon $inicio, Carbon $fin): array
    {
        return Alerta::with(['lectura.sensor.equipo'])
            ->whereBetween('created_at', [$inicio, $fin])
            ->get()
            ->groupBy(function($alerta) {
                return $alerta->lectura->sensor->equipo->nombre ?? 'Sin asignar';
            })
            ->map(function($alertas, $equipoNombre) {
                return [
                    'equipo' => $equipoNombre,
                    'total_alertas' => $alertas->count(),
                    'criticas' => $alertas->where('nivel_criticidad', 'alto')->count(),
                    'moderadas' => $alertas->where('nivel_criticidad', 'medio')->count(),
                    'ultima_alerta' => $alertas->max('created_at')?->format('Y-m-d H:i:s')
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Obtener equipos que requieren atención inmediata
     */
    private function obtenerEquiposCriticos(): array
    {
        return Equipo::with(['sensores.lecturas' => function($query) {
                $query->latest()->take(5);
            }])
            ->get()
            ->filter(function($equipo) {
                // Filtrar equipos con alertas críticas recientes
                return $equipo->sensores->pluck('lecturas')->flatten()
                    ->where('estado', 'critico')
                    ->where('created_at', '>=', now()->subHours(24))
                    ->isNotEmpty();
            })
            ->map(function($equipo) {
                return [
                    'id' => $equipo->id,
                    'nombre' => $equipo->nombre,
                    'ubicacion' => $equipo->ubicacion,
                    'alertas_recientes' => $equipo->sensores->pluck('lecturas')->flatten()
                        ->where('estado', 'critico')
                        ->where('created_at', '>=', now()->subHours(24))
                        ->count()
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Obtener tendencias del período
     */
    private function obtenerTendencias(Carbon $inicio, Carbon $fin): array
    {
        // Comparar con el período anterior
        $duracion = $inicio->diffInDays($fin) + 1;
        $periodoAnteriorInicio = $inicio->copy()->subDays($duracion);
        $periodoAnteriorFin = $inicio->copy()->subDay();

        $alertasActual = Alerta::whereBetween('created_at', [$inicio, $fin])->count();
        $alertasAnterior = Alerta::whereBetween('created_at', [$periodoAnteriorInicio, $periodoAnteriorFin])->count();

        $diferencia = $alertasActual - $alertasAnterior;
        $porcentaje = $alertasAnterior > 0 ? (($diferencia / $alertasAnterior) * 100) : 0;

        return [
            'comparacion_periodos' => [
                'actual' => $alertasActual,
                'anterior' => $alertasAnterior,
                'diferencia' => $diferencia,
                'porcentaje_cambio' => round($porcentaje, 2)
            ],
            'tendencia' => $diferencia > 0 ? 'aumento' : ($diferencia < 0 ? 'disminucion' : 'estable')
        ];
    }

    /**
     * Generar recomendaciones basadas en los datos
     */
    private function generarRecomendaciones(): array
    {
        $recomendaciones = [];

        // Recomendaciones basadas en equipos críticos
        $equiposCriticos = $this->obtenerEquiposCriticos();
        if (count($equiposCriticos) > 0) {
            $recomendaciones[] = [
                'tipo' => 'critico',
                'mensaje' => "Atención inmediata requerida para " . count($equiposCriticos) . " equipos con alertas críticas",
                'accion' => 'Revisar equipos críticos inmediatamente'
            ];
        }

        // Recomendaciones basadas en tendencias
        $tendencias = $this->obtenerTendencias($this->obtenerFechaInicio(), $this->obtenerFechaFin());
        if ($tendencias['tendencia'] === 'aumento') {
            $recomendaciones[] = [
                'tipo' => 'advertencia',
                'mensaje' => "Aumento del " . abs($tendencias['comparacion_periodos']['porcentaje_cambio']) . "% en alertas",
                'accion' => 'Investigar causas del aumento de alertas'
            ];
        }

        return $recomendaciones;
    }

    /**
     * Obtener lista de destinatarios
     */
    private function obtenerDestinatarios()
    {
        if ($this->destinatarios) {
            return collect($this->destinatarios)->map(function($email) {
                return (object)['email' => $email, 'name' => 'Usuario'];
            });
        }

        // Por defecto, enviar a administradores y supervisores
        return User::whereIn('role', ['admin', 'supervisor'])
            ->where('activo', true)
            ->get();
    }
}
