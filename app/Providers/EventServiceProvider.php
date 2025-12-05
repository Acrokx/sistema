<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

// Eventos
use App\Events\LecturaCriticaDetectada;
use App\Events\MantenimientoCompletado;
use App\Events\UsuarioRegistrado;

// Listeners
use App\Listeners\NotificarLecturaCritica;
use App\Listeners\CrearAlertaAutomatica;
use App\Listeners\ActualizarEstadisticasEquipo;
use App\Listeners\EnviarNotificacionSlack;

use App\Listeners\ActualizarHistorialEquipo;
use App\Listeners\NotificarComplecionMantenimiento;
use App\Listeners\ProgramarProximoMantenimiento;

use App\Listeners\EnviarEmailBienvenida;
use App\Listeners\CrearPerfilTecnico;
use App\Listeners\AsignarRolPorDefecto;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [

        // Eventos de lecturas críticas
        LecturaCriticaDetectada::class => [
            NotificarLecturaCritica::class,
            CrearAlertaAutomatica::class,
            ActualizarEstadisticasEquipo::class,
            EnviarNotificacionSlack::class,
        ],

        // Eventos de mantenimiento
        MantenimientoCompletado::class => [
            ActualizarHistorialEquipo::class,
            NotificarComplecionMantenimiento::class,
            ProgramarProximoMantenimiento::class,
        ],

        // Eventos de usuario
        UsuarioRegistrado::class => [
            EnviarEmailBienvenida::class,
            CrearPerfilTecnico::class,
            AsignarRolPorDefecto::class,
        ],
    ];
}
