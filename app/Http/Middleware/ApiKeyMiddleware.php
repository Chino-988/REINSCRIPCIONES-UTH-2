<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    /**
     * Valida la API Key para los endpoints protegidos.
     * Header esperado: X-API-KEY
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Acepta mayúsculas/minúsculas
        $key = $request->header('X-API-KEY') ?? $request->header('x-api-key');

        // Lee de config/app.php (cae a ENV si no está en config)
        $expected = (string) config('app.api_key_caja', env('API_KEY_CAJA', ''));

        if ($expected === '' || !hash_equals($expected, (string) $key)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Unauthorized: invalid or missing API key',
            ], 401);
        }

        return $next($request);
    }
}
