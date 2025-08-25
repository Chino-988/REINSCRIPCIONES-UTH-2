<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Uso: ->middleware('role:ADMIN') ó 'role:ADMIN,CAJA'
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        $userRole = strtoupper((string)($user->role ?? ''));

        if (empty($roles)) {
            return $next($request);
        }

        $roles = array_map(fn($r) => strtoupper(trim($r)), $roles);

        if (in_array($userRole, $roles, true)) {
            return $next($request);
        }

        abort(403, 'No tienes permisos para acceder a esta sección.');
    }
}
