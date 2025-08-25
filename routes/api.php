<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReferenciaController;
use App\Http\Controllers\Api\EstadoReinscripcionController;
use App\Http\Middleware\ApiKeyMiddleware;

/*
|--------------------------------------------------------------------------
| Rutas públicas (ya salen con /api por defecto)
|--------------------------------------------------------------------------
*/
Route::get('/ping', fn () => response()->json([
    'ok'   => true,
    'pong' => now()->toIso8601String(),
]));

Route::get('/diag', function () {
    $diag = [
        'env'              => config('app.env'),
        'api_key_cfg'      => (config('app.api_key_caja', env('API_KEY_CAJA', '')) !== '') ? 'SET' : 'EMPTY',
        'db_conn'          => config('database.default'),
        'db_ok'            => true,
        'db_error'         => null,
        'has_pagos'        => null,
        'has_estudiantes'  => null,
    ];

    try {
        $diag['has_pagos']       = \App\Models\Pago::query()->exists();
        $diag['has_estudiantes'] = \App\Models\Estudiante::query()->exists();
    } catch (\Throwable $e) {
        $diag['db_ok']    = false;
        $diag['db_error'] = $e->getMessage();
    }

    return response()->json(['ok' => true, 'diag' => $diag]);
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas /api/v1/... (middleware API KEY)
|--------------------------------------------------------------------------
*/
Route::prefix('v1')
    ->middleware([ApiKeyMiddleware::class])
    ->group(function () {
        // --- Referencias (existentes) ---
        Route::get ('/referencias/pendientes',   [ReferenciaController::class, 'pendientes']);
        Route::post('/referencias/consulta',     [ReferenciaController::class, 'consulta']);
        Route::post('/referencias/sincronizar',  [ReferenciaController::class, 'sincronizar']);
        Route::post('/referencias/evidencia',    [ReferenciaController::class, 'evidencia']);
        Route::post('/referencias/cargar-csv',   [ReferenciaController::class, 'cargarCsv']);

        // --- NUEVO: Estado de Reinscripción por matrícula ---
        // Soporta path param y query param (?matricula=...)
        Route::get('/reinscripciones/estado/{matricula?}', [EstadoReinscripcionController::class, 'show']);
    });
