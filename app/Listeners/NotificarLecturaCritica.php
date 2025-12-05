<?php

namespace App\Listeners;

use App\Events\LecturaCriticaDetectada;
use App\Mail\AlertaCriticaMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificarLecturaCritica implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the listener should be queued.
     */
    public function shouldQueue(LecturaCriticaDetectada $event): bool
    {
        // Evitar spam: no enviar más de una notificación por equipo en 5 minutos
        $cacheKey = "alerta_critica_equipo_{$event->equipo->id}";
        $ultimaNotificacion = cache($cacheKey);

        if ($ultimaNotificacion && now()->diffInMinutes($ultimaNotificacion) < 5) {
            Log::info('Notificación crítica omitida por límite de frecuencia', [
                'equipo_id' => $event->equipo->id,
                'ultima_notificacion' => $ultimaNotificacion,
                'minutos_transcurridos' => now()->diffInMinutes($ultimaNotificacion)
            ]);
            return false; // No encolar
        }

        // Actualizar timestamp de última notificación
        cache([$cacheKey => now()], now()->addMinutes(10));

        return true;
    }

    /**
     * Handle the event.
     */
    public function handle(LecturaCriticaDetectada $event): void
    {
        // Solo notificar si es crítico
        if ($event->nivelCriticidad !== 'CRITICO') {
            return;
        }

        // Obtener técnicos responsables del equipo
        // Nota: Asumiendo que hay una relación equipos-usuarios para asignación
        $tecnicos = $event->equipo->tecnicos()
            ->where('activo', true)
            ->get();

        // Si no hay técnicos asignados, buscar técnicos con rol 'tecnico'
        if ($tecnicos->isEmpty()) {
            $tecnicos = \App\Models\User::where('role', 'tecnico')
                ->orWhere('role', 'supervisor')
                ->get();
        }

        foreach ($tecnicos as $tecnico) {
            Mail::to($tecnico->email)->send(
                new AlertaCriticaMail($event)
            );
        }

        // Log para auditoría
        Log::critical('Lectura crítica detectada - Notificaciones enviadas', [
            'equipo_id' => $event->equipo->id,
            'equipo_nombre' => $event->equipo->nombre,
            'sensor_id' => $event->sensor->id,
            'sensor_tipo' => $event->sensor->tipo_sensor,
            'valor' => $event->lectura->valor,
            'nivel_criticidad' => $event->nivelCriticidad,
            'tecnicos_notificados' => $tecnicos->pluck('email')->toArray(),
            'cantidad_notificaciones' => $tecnicos->count()
        ]);
    }
}
