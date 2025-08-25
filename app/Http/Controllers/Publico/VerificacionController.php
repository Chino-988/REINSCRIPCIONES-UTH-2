<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\Reinscripcion;

class VerificacionController extends Controller
{
    public function show(string $token)
    {
        $rein = Reinscripcion::with(['estudiante.user'])->where('token_verificacion', $token)->firstOrFail();

        // Estado esperado APROBADA; de lo contrario se muestra como no vigente
        $vigente = $rein->estatus_final === 'APROBADA';

        return view('publico.verificacion', compact('rein','vigente'));
    }
}
