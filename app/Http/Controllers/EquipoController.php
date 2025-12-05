<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Equipo $equipo)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Equipo $equipo)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Equipo $equipo)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Equipo $equipo)
    {
        //
    }

    /**
     * Mostrar equipos asignados al usuario actual
     */
    public function misEquipos()
    {
        $user = auth()->user();

        // Obtener equipos asignados al usuario (personalizar según la lógica de propiedad)
        $equipos = Equipo::where('user_id', $user->id) // Asumiendo que hay una columna user_id
            ->with(['sensores.lecturas.alertas' => function($query) {
                $query->latest()->take(5); // Últimas 5 alertas por sensor
            }])
            ->get();

        return view('equipos.mis-equipos', compact('equipos'));
    }
}
