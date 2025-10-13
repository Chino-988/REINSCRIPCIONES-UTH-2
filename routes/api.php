<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\Api\ReferenciaController;
use App\Http\Controllers\Api\EstadoReinscripcionController;
use App\Http\Middleware\ApiKeyMiddleware;

// API móvil (Sanctum)
use App\Http\Controllers\Mobile\MobileAuthController;
use App\Http\Controllers\Mobile\DeviceController;

// Servicio (para pruebas locales)
use App\Services\FcmService;

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
        // Referencias (existentes)
        Route::get ('/referencias/pendientes',   [ReferenciaController::class, 'pendientes']);
        Route::post('/referencias/consulta',     [ReferenciaController::class, 'consulta']);
        Route::post('/referencias/sincronizar',  [ReferenciaController::class, 'sincronizar']);
        Route::post('/referencias/evidencia',    [ReferenciaController::class, 'evidencia']);
        Route::post('/referencias/cargar-csv',   [ReferenciaController::class, 'cargarCsv']);

        // Estado de Reinscripción por matrícula (protegido por API-KEY)
        Route::get('/reinscripciones/estado/{matricula?}', [EstadoReinscripcionController::class, 'show']);
    });

/*
|--------------------------------------------------------------------------
| API móvil (Sanctum): login + registrar token FCM + mi estado + logout
|--------------------------------------------------------------------------
*/
Route::prefix('v1/mobile')->group(function () {
    Route::post('/login', [MobileAuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        // Registra/actualiza token del dispositivo
        Route::post('/devices/register', [DeviceController::class, 'register']);
        // Devuelve estado del alumno logueado
        Route::get('/me/estado',         [DeviceController::class, 'estado']);
        // Cerrar sesión
        Route::post('/logout',           [MobileAuthController::class, 'logout']);
    });
});

/*
|--------------------------------------------------------------------------
| DEV ONLY: push de prueba a un user_id (solo en entorno local)
|--------------------------------------------------------------------------
*/
if (app()->environment('local')) {
    Route::post('/v1/dev/push-test/{user}', function (Request $req, int $userId, FcmService $fcm) {
        $title = $req->input('title', 'Prueba');
        $body  = $req->input('body',  'Hola desde FCM v1');
        $data  = (array) $req->input('data', ['env' => 'local']);

        $ok = $fcm->sendToUser($userId, $title, $body, $data);
        return ['ok' => $ok];
    });
}
