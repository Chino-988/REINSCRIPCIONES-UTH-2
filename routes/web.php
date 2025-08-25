<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\{
    DashboardController,
    Admin\AdminController,
    Caja\CajaController,
    ProfileController
};
use App\Http\Controllers\Student\{
    PerfilController,
    PagoController,
    ConstanciaController,
    EstadoInlineController
};
use App\Http\Controllers\Publico\VerificacionController; // verificación pública por token

// Portada (welcome presentable)
Route::view('/', 'welcome')->name('home');

// Verificación pública por token (QR en constancia) - SIN auth
Route::get('/verificacion/{token}', [VerificacionController::class, 'show'])
    ->name('verificacion.mostrar');

// Recibir token por formulario y redirigir al GET
Route::post('/verificacion', function (Request $request) {
    $token = trim((string)$request->input('token'));
    if ($token === '') {
        return back()->with('err', 'Ingresa un token para verificar.')->withInput();
    }
    return redirect()->route('verificacion.mostrar', $token);
})->name('verificacion.redirect');

// ‘/dashboard’ redirige al panel según rol (si no hay sesión => login)
Route::get('/dashboard', function () {
    $u = auth()->user();
    if (!$u) return redirect()->route('login');
    return match ($u->role) {
        'ADMIN' => redirect()->route('dash.admin'),
        'CAJA'  => redirect()->route('dash.caja'),
        default => redirect()->route('dash.estudiante'),
    };
})->middleware('auth')->name('dashboard');

// Auth de Breeze
require __DIR__.'/auth.php';

// ===== RUTAS DE PERFIL (Breeze) =====
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ===== ESTUDIANTE =====
Route::middleware(['auth','role:ESTUDIANTE'])->group(function () {
    Route::get('/estudiante', [DashboardController::class, 'estudiante'])->name('dash.estudiante');

    Route::get('/estudiante/perfil', [PerfilController::class, 'edit'])->name('est.perfil.edit');
    Route::post('/estudiante/perfil', [PerfilController::class, 'update'])->name('est.perfil.update');

    Route::get('/estudiante/pago', [PagoController::class, 'create'])->name('est.pago.create');
    Route::post('/estudiante/pago', [PagoController::class, 'store'])->name('est.pago.store');

    // Constancia (controlador Student correcto)
    Route::get('/estudiante/constancia', [ConstanciaController::class, 'descargar'])->name('est.constancia.descargar');

    // Notificaciones
    Route::get('/estudiante/notificaciones', [DashboardController::class, 'notificaciones'])->name('est.notificaciones');
    Route::post('/estudiante/notificaciones/{id}/leer', [DashboardController::class, 'marcarNotiLeida'])->name('est.noti.leer');

    // Proxy interno (sin exponer API key) para estado de reinscripción (usado por el dashboard JS)
    Route::get('/mi-estado-reinscripcion', [EstadoInlineController::class, 'yo'])->name('estudiante.estado');
});

// ===== ADMIN =====
Route::middleware(['auth','role:ADMIN'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard'])->name('dash.admin');

    Route::get('/admin/solicitudes', [AdminController::class, 'solicitudes'])->name('admin.solicitudes');
    Route::get('/admin/solicitudes/exportar', [AdminController::class, 'exportSolicitudes'])->name('admin.solicitudes.exportar');

    Route::get('/admin/solicitud/{pago}', [AdminController::class, 'verSolicitud'])->name('admin.ver');

    Route::post('/admin/solicitud/{pago}/finalizar', [AdminController::class, 'finalizar'])->name('admin.finalizar');
    Route::post('/admin/solicitud/{pago}/rechazar', [AdminController::class, 'rechazar'])->name('admin.rechazar');
});

// ===== CAJA =====
Route::middleware(['auth','role:CAJA'])->group(function () {
    Route::get('/caja', [CajaController::class, 'dashboard'])->name('dash.caja');

    // Métricas del dashboard (AJAX)
    Route::get('/caja/metrics', [CajaController::class, 'metrics'])->name('caja.metrics');

    Route::get('/caja/pendientes', [CajaController::class, 'pendientes'])->name('caja.pendientes');
    Route::get('/caja/pendientes/exportar', [CajaController::class, 'exportarPendientes'])->name('caja.pendientes.exportar');

    Route::post('/caja/pago/{pago}/validar', [CajaController::class, 'validar'])->name('caja.validar');
    Route::post('/caja/pago/{pago}/rechazar', [CajaController::class, 'rechazar'])->name('caja.rechazar');

    Route::post('/caja/exportar-csv', [CajaController::class, 'exportarCSV'])->name('caja.exportar.csv');

    // Análisis de referencias
    Route::get('/caja/analisis', [CajaController::class, 'analisis'])->name('caja.analisis');
    Route::post('/caja/analisis/procesar', [CajaController::class, 'procesarAnalisis'])->name('caja.analisis.procesar');
    Route::get('/caja/analisis/descargar', [CajaController::class, 'descargarAnalisis'])->name('caja.analisis.descargar');

    // Consulta de estado por referencia (AJAX interno)
    Route::post('/caja/referencias/estado', [CajaController::class, 'estadoAjax'])->name('caja.referencias.estado');
});
