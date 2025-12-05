<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use App\Models\Sensor;
use App\Models\Alerta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'equipos' => Equipo::count(),
            'sensores' => Sensor::count(),
            'alertas_activas' => Alerta::where('nivel_criticidad', '!=', 'bajo')->count(),
            'ultimas_alertas' => Alerta::with('lectura.sensor.equipo')
                ->latest()
                ->take(5)
                ->get()
                ->map(function ($alerta) {
                    return [
                        'id' => $alerta->id,
                        'tipo_fallo' => $alerta->tipo_fallo,
                        'nivel_criticidad' => $alerta->nivel_criticidad,
                        'descripcion' => $alerta->descripcion,
                        'created_at' => $alerta->created_at,
                        'equipo' => $alerta->lectura?->sensor?->equipo?->nombre ?? 'N/A'
                    ];
                }),
            'chart_data' => $this->getChartData()
        ];

        return response()->json($stats);
    }

    private function getChartData()
    {
        // Datos para gráfico de alertas por nivel
        $alertasPorNivel = Alerta::selectRaw('nivel_criticidad, COUNT(*) as count')
            ->groupBy('nivel_criticidad')
            ->get()
            ->pluck('count', 'nivel_criticidad')
            ->toArray();

        return [
            'alertas_por_nivel' => [
                'labels' => ['Bajo', 'Medio', 'Alto'],
                'datasets' => [[
                    'label' => 'Alertas',
                    'data' => [
                        $alertasPorNivel['bajo'] ?? 0,
                        $alertasPorNivel['medio'] ?? 0,
                        $alertasPorNivel['alto'] ?? 0
                    ],
                    'backgroundColor' => [
                        'rgba(75, 192, 192, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(255, 99, 132, 0.6)'
                    ]
                ]]
            ]
        ];
    }
}
