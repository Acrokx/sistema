<?php

namespace App\Http\Middleware;

use App\Models\Equipo;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OwnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $resource): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        // Para equipos, verificar propiedad
        if ($resource === 'equipo') {
            $equipoId = $request->route('id') ?? $request->route('equipo');

            if ($equipoId) {
                $equipo = Equipo::find($equipoId);

                if (!$equipo) {
                    return response()->json(['error' => 'Equipo no encontrado'], 404);
                }

                // Lógica de propiedad: admins pueden ver todo, otros solo sus equipos asignados
                $userRole = $user->role ?? 'usuario';

                if ($userRole !== 'admin') {
                    // Aquí iría la lógica para verificar si el usuario es propietario del equipo
                    // Por ahora, permitir acceso (personalizar según necesidades)
                    // Ejemplo: verificar si el equipo está asignado al usuario
                    // if (!$equipo->users()->where('user_id', $user->id)->exists()) {
                    //     return response()->json(['error' => 'No tienes acceso a este equipo'], 403);
                    // }
                }
            }
        }

        return $next($request);
    }
}
