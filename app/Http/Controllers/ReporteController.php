<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Sensor;
use App\Models\Alerta;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function index()
    {
        // Generar reportes estadísticos
        $reportes = [
            'equipos_por_estado' => Equipo::selectRaw('estado, COUNT(*) as total')
                ->groupBy('estado')
                ->get(),

            'alertas_por_nivel' => Alerta::selectRaw('nivel_criticidad, COUNT(*) as total')
                ->groupBy('nivel_criticidad')
                ->get(),

            'sensores_por_tipo' => Sensor::selectRaw('tipo_sensor, COUNT(*) as total')
                ->groupBy('tipo_sensor')
                ->get(),

            'alertas_recientes' => Alerta::with('lectura.sensor.equipo')
                ->latest()
                ->take(10)
                ->get(),

            'estadisticas_generales' => [
                'total_equipos' => Equipo::count(),
                'total_sensores' => Sensor::count(),
                'total_alertas' => Alerta::count(),
                'alertas_criticas' => Alerta::where('nivel_criticidad', 'alto')->count(),
                'equipos_activos' => Equipo::where('estado', 'activo')->count(),
            ]
        ];

        return view('admin.reportes.index', compact('reportes'));
    }
}
