<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $equipos = Equipo::get();
        return response()->json($equipos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $equipo = Equipo::create($request->all());
        return response()->json($equipo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $equipo = Equipo::with('sensores.lecturas.alertas')->find($id);

        if (!$equipo) {
            return response()->json(['error' => 'Equipo no encontrado'], 404);
        }

        return response()->json($equipo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
