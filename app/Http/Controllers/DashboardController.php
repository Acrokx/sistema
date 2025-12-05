<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Alerta;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function usuario()
    {
        $user = auth()->user();

        // Obtener estadísticas básicas para el usuario
        $stats = [
            'mis_equipos' => Equipo::where('user_id', $user->id)->count(), // Asumiendo que hay una relación
            'alertas_activas' => Alerta::whereHas('lectura.sensor.equipo', function($query) use ($user) {
                $query->where('user_id', $user->id); // Personalizar según la lógica de propiedad
            })->count(),
            'ultimas_alertas' => Alerta::with('lectura.sensor.equipo')
                ->whereHas('lectura.sensor.equipo', function($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('dashboard.usuario', compact('stats'));
    }
}
