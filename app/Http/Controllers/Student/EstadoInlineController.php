<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class EstadoInlineController extends Controller
{
    // Devuelve el mismo JSON del endpoint API pero para el usuario logueado
    public function yo()
    {
        $estu = auth()->user()->estudiante ?? null;
        if (!$estu) return response()->json(['ok'=>false,'message'=>'No hay estudiante asociado'], 404);

        // Reutilizamos la lógica del controlador API
        $ctrl = app(\App\Http\Controllers\Api\EstadoReinscripcionController::class);
        return $ctrl->show(request(), $estu->matricula);
    }
}
