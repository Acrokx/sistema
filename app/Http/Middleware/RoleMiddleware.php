<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!$request->user()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $userRole = $request->user()->role ?? 'usuario';

        // Si se especifican roles, verificar que el usuario tenga uno de ellos
        if (!empty($roles)) {
            $allowedRoles = explode(',', $roles[0]); // Permite múltiples roles separados por coma

            if (!in_array($userRole, $allowedRoles)) {
                return response()->json([
                    'error' => 'Acceso denegado',
                    'required_roles' => $allowedRoles,
                    'user_role' => $userRole
                ], 403);
            }
        }

        return $next($request);
    }
}
