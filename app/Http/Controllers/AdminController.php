<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use App\Models\Sensor;
use App\Models\Alerta;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_equipos' => Equipo::count(),
            'total_sensores' => Sensor::count(),
            'total_alertas' => Alerta::count(),
            'total_usuarios' => User::count(),
            'alertas_criticas' => Alerta::where('nivel_criticidad', 'alto')->count(),
            'equipos_activos' => Equipo::where('estado', 'activo')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
