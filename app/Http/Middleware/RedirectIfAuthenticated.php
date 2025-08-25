<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if (auth()->check()) {
            // Si ya hay sesión, envía a la ruta 'dashboard'
            return redirect()->route('dashboard');
        }
        return $next($request);
    }
}
